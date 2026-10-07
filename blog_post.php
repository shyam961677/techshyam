<?php
require_once __DIR__ . '/admin/includes/auth.php';
trackVisitor();

$slug = trim($_GET['slug'] ?? '');
$blogs = readJson('blogs.json') ?? [];
$profile = readJson('profile.json') ?? [];
$settings = readJson('settings.json') ?? [];
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$blogUrl = $basePath . '/blog';

// Only published articles are public.
$postIndex = null;
foreach ($blogs as $index => $blog) {
    if (($blog['slug'] ?? '') === $slug && !empty($blog['published'])) {
        $postIndex = $index;
        break;
    }
}
if ($postIndex === null) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Article not found</title><link rel="icon" href="' . htmlspecialchars($basePath . '/assets/images/fav.png', ENT_QUOTES, 'UTF-8') . '"></head><body style="margin:0;background:#05050d;color:#f5f6ff;font:16px Arial,sans-serif;text-align:center;padding:15vh 20px"><h1>Article not found</h1><p><a style="color:#aaa3ff" href="' . htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') . '">Return to all articles</a></p></body></html>';
    exit;
}
$post = $blogs[$postIndex];

// Increment the view count.
$blogs[$postIndex]['views'] = (int)($post['views'] ?? 0) + 1;
$post['views'] = $blogs[$postIndex]['views'];
writeJson('blogs.json', $blogs);

// Related published posts in the same category.
$related = array_values(array_filter($blogs, fn($blog) =>
    !empty($blog['published']) &&
    ($blog['slug'] ?? '') !== $slug &&
    ($blog['category'] ?? '') === ($post['category'] ?? '')
));
$related = array_slice($related, 0, 3);

$allComments = readJson('comments.json') ?? [];
$postComments = array_values(array_filter($allComments, fn($comment) =>
    (int)($comment['post_id'] ?? 0) === (int)$post['id'] &&
    !empty($comment['approved'])
));

$commentError = '';
$commentSuccess = '';
if (!empty($settings['comments_enabled']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cName = trim($_POST['c_name'] ?? '');
    $cEmail = filter_var(trim($_POST['c_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $cMessage = trim($_POST['c_message'] ?? '');

    if ($cName === '' || $cMessage === '') {
        $commentError = 'Name and message are required.';
    } elseif ((function_exists('mb_strlen') ? mb_strlen($cName) : strlen($cName)) > 120 || (function_exists('mb_strlen') ? mb_strlen($cMessage) : strlen($cMessage)) > 5000) {
        $commentError = 'Name or comment is too long.';
    } else {
        $ids = array_map(fn($comment) => (int)($comment['id'] ?? 0), $allComments);
        $allComments[] = [
            'id' => empty($ids) ? 1 : max($ids) + 1,
            'post_id' => (int)$post['id'],
            'name' => $cName,
            'email' => $cEmail ?: '',
            'message' => $cMessage,
            'date' => date('Y-m-d H:i:s'),
            'approved' => false,
        ];
        writeJson('comments.json', $allComments);
        $commentSuccess = 'Comment submitted. It will appear after review.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05050d">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/assets/images/fav.png', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($basePath . '/assets/images/fav.png', ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($post['image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($basePath . '/' . ltrim($post['image'], '/'), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root{color-scheme:dark;--bg:#05050d;--panel:#0f0f1e;--panel2:#131326;--line:rgba(99,102,241,.18);--text:#f5f6ff;--muted:#929bb2;--purple:#8278ff;--cyan:#16c6d3;--rail:72px}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--text);font:400 16px/1.7 Inter,Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit}
        .site-rail{position:fixed;inset:0 auto 0 0;width:var(--rail);z-index:20;background:linear-gradient(180deg,rgba(12,12,27,.98),rgba(5,5,13,.97));border-right:1px solid rgba(145,155,255,.13);display:flex;flex-direction:column;align-items:center;justify-content:space-between;padding:22px 0}.rail-logo{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;text-decoration:none;font-weight:800;background:linear-gradient(135deg,#6366f1,#06b6d4);box-shadow:0 8px 24px #6366f144}.rail-links,.rail-social{display:flex;flex-direction:column;align-items:center;gap:8px}.rail-link,.rail-social a{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;color:#71809b;text-decoration:none;font-size:1.15rem;border:1px solid transparent;transition:.2s}.rail-link:hover,.rail-link.active,.rail-social a:hover{color:#bdc5ff;background:#6366f122;border-color:#919bff33}.rail-social a{height:34px;font-size:.95rem}.page{margin-left:var(--rail);min-height:100vh}.mobilebar{display:none}
        .wrap{width:min(1120px,calc(100% - 96px));margin-inline:auto}.article-hero{position:relative;overflow:hidden;padding:72px 0 46px;border-bottom:1px solid #ffffff0c;background:radial-gradient(ellipse at 65% -20%,#392e7650,transparent 55%),linear-gradient(180deg,#080812,#05050d)}.article-hero:after{content:"";position:absolute;right:8%;top:25px;width:230px;height:230px;border:1px solid #7772ff16;border-radius:50%;box-shadow:0 0 0 35px #7772ff08,0 0 0 70px #7772ff05;pointer-events:none}.crumb{display:flex;align-items:center;gap:9px;color:#858da4;font-size:.78rem;margin-bottom:36px}.crumb a{color:#b5b9ce;text-decoration:none}.crumb a:hover{color:#fff}.crumb i{font-size:.7rem}.cat{display:inline-flex;align-items:center;gap:8px;color:#b9b4ff;background:#7970ff16;border:1px solid #8178ff35;border-radius:8px;padding:7px 11px;font:500 .69rem 'JetBrains Mono',monospace;letter-spacing:.06em;text-transform:uppercase}.cat:before{content:"";width:6px;height:6px;border-radius:50%;background:var(--cyan);box-shadow:0 0 10px var(--cyan)}h1{position:relative;z-index:1;max-width:900px;font-size:clamp(2.7rem,6vw,5rem);letter-spacing:-.07em;line-height:1.02;margin:22px 0 18px;font-weight:800}.dek{max-width:710px;color:#a2abc1;font-size:1.04rem;line-height:1.8;margin:0}.byline{display:flex;align-items:center;gap:15px;flex-wrap:wrap;margin-top:27px;color:#929bb2;font-size:.77rem}.author-dot{width:35px;height:35px;border:1px solid #8178ff55;border-radius:50%;display:grid;place-items:center;color:#c8c4ff;background:#8178ff1a;font-weight:700}.byline .author-name{color:#e0e3f1;font-weight:600}.byline-item{display:flex;align-items:center;gap:6px}.byline-sep{height:4px;width:4px;border-radius:50%;background:#4c5265}
        .article-layout{display:grid;grid-template-columns:minmax(0,760px) minmax(220px,280px);gap:44px;align-items:start;padding:42px 0 76px}.article-main{min-width:0}.cover{height:270px;position:relative;overflow:hidden;border:1px solid var(--line);border-radius:18px;margin:0 0 34px;background:radial-gradient(ellipse at 50% 42%,#373568,#17182b 63%,#10111c)}.cover-art{position:absolute;inset:0;display:grid;place-items:center;background:radial-gradient(ellipse at center,#35316577,transparent 68%),linear-gradient(135deg,#15162a,#10111a)}.cover-art:before,.cover-art:after{content:"";position:absolute;border:1px solid #aaa4ff1c;border-radius:50%;width:220px;height:220px}.cover-art:after{width:310px;height:310px}.cover-icon{width:88px;height:88px;border:1px solid #9a92ff55;border-radius:25px;display:grid;place-items:center;color:#c2bdff;font-size:2.6rem;background:#7770ff17;box-shadow:0 18px 60px #0004;z-index:1}.cover img{position:relative;width:100%;height:100%;object-fit:cover;display:block}.cover-caption{position:absolute;left:20px;bottom:17px;color:#c4c8da;font:500 .68rem 'JetBrains Mono',monospace;letter-spacing:.12em;text-transform:uppercase;z-index:2}
        .article-body{color:#c1c6d7;font-size:1.04rem;line-height:1.92;overflow-wrap:anywhere}.article-body>*:first-child{margin-top:0}.article-body p{margin:0 0 22px}.article-body h2{margin:48px 0 15px;color:#f4f5ff;font-size:1.65rem;line-height:1.25;letter-spacing:-.045em}.article-body h3{margin:32px 0 12px;color:#f0f1fc;font-size:1.25rem;line-height:1.35}.article-body ul,.article-body ol{padding-left:1.35rem;margin:0 0 24px}.article-body li{padding-left:5px;margin:8px 0}.article-body li::marker{color:#8d83ff}.article-body a{color:#8fbcff;text-decoration:underline;text-decoration-color:#8fbcff55;text-underline-offset:3px}.article-body a:hover{color:#c4d9ff}.article-body strong{color:#edf0ff}.article-body blockquote{margin:28px 0;padding:5px 0 5px 22px;border-left:2px solid #8278ff;color:#aeb6cf}.article-body pre{overflow:auto;margin:25px 0;padding:20px 22px;border:1px solid #282c47;border-radius:13px;background:#0b0c15;color:#b7e4ff;font:400 .84rem/1.8 'JetBrains Mono',monospace;white-space:pre}.article-body pre code{padding:0;background:none;border:0;color:inherit;font:inherit}.article-body code{font:400 .86em 'JetBrains Mono',monospace;color:#b4adff;background:#7970ff16;border:1px solid #8178ff26;padding:2px 6px;border-radius:5px}.article-tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:22px 0 28px;border-bottom:1px solid var(--line);margin-top:32px}.tag-label{font:500 .67rem 'JetBrains Mono',monospace;color:#78819a;text-transform:uppercase;letter-spacing:.1em;margin-right:4px}.tag{padding:6px 10px;border:1px solid #ffffff12;border-radius:7px;background:#ffffff05;color:#aeb5ca;font:400 .7rem 'JetBrains Mono',monospace}
        .side-card{border:1px solid var(--line);border-radius:15px;background:linear-gradient(145deg,#111222,#0c0d16);padding:20px;margin-bottom:15px}.side-label{font:500 .65rem 'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:.12em;color:#848ca5;margin:0 0 15px}.author-card{position:sticky;top:24px}.author-avatar{width:54px;height:54px;border-radius:17px;object-fit:cover;margin:0 0 13px;border:1px solid #8278ff55}.author-initials{width:54px;height:54px;display:grid;place-items:center;border-radius:17px;background:linear-gradient(135deg,#6366f1,#06b6d4);font-weight:800;font-size:1.1rem;margin-bottom:13px}.author-name{font-size:.94rem;font-weight:700;color:#f1f2fc}.author-title{font-size:.75rem;color:#8790a8;margin-top:3px}.side-link{display:flex;align-items:center;gap:8px;text-decoration:none;color:#b4baff;font-size:.78rem;font-weight:600;margin-top:18px}.side-link:hover{color:#fff}.related{display:flex;gap:12px;padding:12px 0;text-decoration:none;border-top:1px solid #ffffff0c}.related:first-of-type{border-top:0;padding-top:0}.related-icon{width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:10px;background:#8178ff17;color:#aaa3ff}.related-title{font-size:.76rem;font-weight:600;line-height:1.45;color:#dce0f1}.related-date{margin-top:5px;color:#78829a;font-size:.67rem}
        .discussion{border-top:1px solid var(--line);margin-top:40px;padding-top:32px}.section-title{font-size:1.15rem;letter-spacing:-.03em;color:#f0f1ff;margin:0 0 18px}.muted{font-size:.85rem;color:#858ea6}.comment{padding:16px 0;border-bottom:1px solid #ffffff0d}.comment-head{display:flex;gap:10px;align-items:center}.comment-avatar{width:32px;height:32px;display:grid;place-items:center;border-radius:11px;background:#7770ff1c;color:#bbb6ff;font-size:.75rem;font-weight:700}.comment-name{font-size:.83rem;color:#e8eaf5;font-weight:600}.comment-date{font-size:.72rem;color:#737d94}.comment-message{font-size:.86rem;color:#aeb5c9;margin:8px 0 0;white-space:pre-wrap}.comment-form{border:1px solid var(--line);border-radius:15px;padding:20px;background:#0d0e17;margin-top:24px}.form-title{font-size:.96rem;font-weight:700;color:#eef0fc;margin:0 0 14px}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{width:100%;border:1px solid #282c43;background:#090a12;color:#f1f2fa;border-radius:9px;padding:12px 13px;font:400 .82rem Inter,Arial,sans-serif;outline:0;transition:border .2s,box-shadow .2s}.field::placeholder{color:#6e7890}.field:focus{border-color:#8178ff;box-shadow:0 0 0 3px #8178ff1b}.field-message{min-height:120px;resize:vertical;margin-top:12px}.submit{display:inline-flex;align-items:center;gap:8px;margin-top:12px;padding:11px 16px;border:1px solid #8b83ff;border-radius:9px;background:linear-gradient(120deg,#7167ee,#5553d9);color:white;font:600 .8rem Inter,Arial,sans-serif;cursor:pointer;box-shadow:0 8px 22px #6259da33}.submit:hover{filter:brightness(1.1)}.notice{padding:12px 14px;border-radius:9px;font-size:.82rem;margin-bottom:12px}.notice-ok{color:#93e6b2;background:#10271d;border:1px solid #22573c}.notice-error{color:#ffb0b0;background:#2a171a;border:1px solid #633236}
        .end-nav{border-top:1px solid #ffffff0e;padding:21px 0;color:#818aa1;font-size:.78rem}.end-nav-inner{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}.end-nav a{color:#b5b0ff;text-decoration:none}.end-nav a:hover{color:#fff}
        @media(max-width:950px){.article-layout{grid-template-columns:minmax(0,1fr) 245px;gap:26px}.wrap{width:calc(100% - 60px)}}
        @media(max-width:780px){:root{--rail:0px}.site-rail{display:none}.page{margin-left:0}.mobilebar{height:60px;padding:0 20px;display:flex;align-items:center;justify-content:space-between;background:#080811;border-bottom:1px solid #ffffff12}.wrap{width:calc(100% - 40px)}.article-layout{grid-template-columns:1fr}.author-card{position:static}.article-aside{display:grid;grid-template-columns:1fr 1fr;gap:12px}.side-card{margin:0}.article-hero{padding-top:46px}.article-hero:after{right:-130px}}
        @media(max-width:560px){.wrap{width:calc(100% - 32px)}.article-hero{padding:34px 0 32px}.crumb{margin-bottom:26px}h1{font-size:clamp(2.45rem,12vw,3.6rem)}.dek{font-size:.94rem}.article-layout{padding:26px 0 56px}.cover{height:200px;margin-bottom:25px}.article-body{font-size:.98rem;line-height:1.85}.article-body h2{font-size:1.42rem;margin-top:38px}.article-aside{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.byline{gap:10px}.byline-sep{display:none}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{transition:none!important}}
    </style>
</head>
<body>
<nav class="site-rail" aria-label="Main navigation">
    <a class="rail-logo" href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" aria-label="Home">SY</a>
    <div class="rail-links">
        <a class="rail-link" href="<?= htmlspecialchars($basePath . '/home', ENT_QUOTES, 'UTF-8') ?>" aria-label="Home" title="Home"><i class="bi bi-house-fill"></i></a>
        <a class="rail-link" href="<?= htmlspecialchars($basePath . '/about', ENT_QUOTES, 'UTF-8') ?>" aria-label="About" title="About"><i class="bi bi-person-fill"></i></a>
        <a class="rail-link" href="<?= htmlspecialchars($basePath . '/experience', ENT_QUOTES, 'UTF-8') ?>" aria-label="Experience" title="Experience"><i class="bi bi-briefcase-fill"></i></a>
        <a class="rail-link" href="<?= htmlspecialchars($basePath . '/portfolio', ENT_QUOTES, 'UTF-8') ?>" aria-label="Projects" title="Projects"><i class="bi bi-columns-gap"></i></a>
        <a class="rail-link active" href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="Blog" title="Blog"><i class="bi bi-journal-text"></i></a>
        <a class="rail-link" href="<?= htmlspecialchars($basePath . '/contact', ENT_QUOTES, 'UTF-8') ?>" aria-label="Contact" title="Contact"><i class="bi bi-envelope-fill"></i></a>
    </div>
    <div class="rail-social"><?php $socialIcons = ['linkedin'=>'bi-linkedin','github'=>'bi-github','twitter'=>'bi-twitter-x','instagram'=>'bi-instagram','facebook'=>'bi-facebook']; foreach (($profile['social'] ?? []) as $network => $url): if (!$url) continue; ?><a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars(ucfirst($network), ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= $socialIcons[$network] ?? 'bi-link-45deg' ?>"></i></a><?php endforeach; ?></div>
</nav>
<header class="mobilebar"><a class="brand" href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>"><span class="brand-mark">SY</span><?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a><a class="back" href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-arrow-left"></i> All articles</a></header>
<div class="page">
    <header class="article-hero">
        <div class="wrap">
            <div class="crumb"><a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">Home</a><i class="bi bi-chevron-right"></i><a href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>">Blog</a><i class="bi bi-chevron-right"></i><span><?= htmlspecialchars($post['category'] ?? 'Article', ENT_QUOTES, 'UTF-8') ?></span></div>
            <?php if (!empty($post['category'])): ?><span class="cat"><?= htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <h1><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if (!empty($post['excerpt'])): ?><p class="dek"><?= htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <div class="byline">
                <span class="author-dot"><?= htmlspecialchars(strtoupper(substr($post['author'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="author-name"><?= htmlspecialchars($post['author'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><span class="byline-sep"></span>
                <span class="byline-item"><i class="bi bi-calendar3"></i><?= htmlspecialchars($post['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><span class="byline-sep"></span>
                <span class="byline-item"><i class="bi bi-clock"></i><?= max(1, (int)ceil(str_word_count(strip_tags($post['content'] ?? '')) / 220)) ?> min read</span><span class="byline-sep"></span>
                <span class="byline-item"><i class="bi bi-eye"></i><?= (int)($post['views'] ?? 0) ?> views</span>
            </div>
        </div>
    </header>
    <main class="wrap article-layout">
        <article class="article-main">
            <figure class="cover">
                <div class="cover-art"><span class="cover-icon"><i class="bi bi-braces-asterisk"></i></span></div>
                <?php if (!empty($post['image'])): ?><img src="<?= htmlspecialchars($basePath . '/' . ltrim($post['image'], '/'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>" onerror="this.remove()"><?php endif; ?>
                <figcaption class="cover-caption"><?= htmlspecialchars($post['category'] ?? 'Developer notes', ENT_QUOTES, 'UTF-8') ?> / FIELD NOTES</figcaption>
            </figure>
            <div class="article-body">
                <?php $content = $post['content'] ?? ''; if (strip_tags($content) === $content) { echo '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</p>'; } else { echo $content; } ?>
            </div>
            <?php if (!empty($post['tags'])): ?><div class="article-tags"><span class="tag-label">Filed under</span><?php foreach ($post['tags'] as $tag): ?><span class="tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div><?php endif; ?>

            <section class="discussion" id="comments">
                <h2 class="section-title">Discussion <span style="color:#777f96;font-weight:500;font-size:.82em">(<?= count($postComments) ?>)</span></h2>
                <?php if (empty($postComments)): ?><p class="muted">No comments yet. Start a thoughtful conversation.</p><?php else: foreach ($postComments as $c): ?>
                    <div class="comment"><div class="comment-head"><span class="comment-avatar"><?= htmlspecialchars(strtoupper(substr($c['name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') ?></span><span class="comment-name"><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><span class="comment-date"><?= htmlspecialchars(substr($c['date'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></span></div><p class="comment-message"><?= htmlspecialchars($c['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></p></div>
                <?php endforeach; endif; ?>
                <?php if (!empty($settings['comments_enabled'])): ?>
                <form class="comment-form" method="POST" action="<?= htmlspecialchars($basePath . '/blog/' . rawurlencode($slug), ENT_QUOTES, 'UTF-8') ?>">
                    <h3 class="form-title">Add to the conversation</h3>
                    <?php if ($commentError): ?><div class="notice notice-error"><?= htmlspecialchars($commentError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <?php if ($commentSuccess): ?><div class="notice notice-ok"><?= htmlspecialchars($commentSuccess, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <div class="form-row"><input class="field" type="text" name="c_name" placeholder="Your name" autocomplete="name" required><input class="field" type="email" name="c_email" placeholder="Email (optional)" autocomplete="email"></div>
                    <textarea class="field field-message" name="c_message" placeholder="Write your comment..." required></textarea>
                    <button class="submit" type="submit"><i class="bi bi-send"></i> Submit for review</button>
                </form>
                <?php else: ?><p class="muted">Comments are currently disabled.</p><?php endif; ?>
            </section>
        </article>
        <aside class="article-aside">
            <section class="side-card author-card"><h2 class="side-label">About the author</h2>
                <?php if (!empty($profile['photo'])): ?><img class="author-avatar" src="<?= htmlspecialchars($basePath . '/' . ltrim($profile['photo'], '/'), ENT_QUOTES, 'UTF-8') ?>" alt="" onerror="this.remove()"><?php else: ?><div class="author-initials"><?= htmlspecialchars(strtoupper(substr($profile['name'] ?? 'SY', 0, 1)), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <div class="author-name"><?= htmlspecialchars($profile['name'] ?? $post['author'] ?? '', ENT_QUOTES, 'UTF-8') ?></div><div class="author-title"><?= htmlspecialchars($profile['title'] ?? 'Software Developer', ENT_QUOTES, 'UTF-8') ?></div>
                <a class="side-link" href="<?= htmlspecialchars($basePath . '/about', ENT_QUOTES, 'UTF-8') ?>">More about me <i class="bi bi-arrow-up-right"></i></a>
            </section>
            <?php if (!empty($related)): ?><section class="side-card"><h2 class="side-label">Keep reading</h2><?php foreach ($related as $r): ?><a class="related" href="<?= htmlspecialchars($blogUrl . '/' . rawurlencode($r['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><span class="related-icon"><i class="bi bi-journal-code"></i></span><span><span class="related-title"><?= htmlspecialchars($r['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><span class="related-date"><?= htmlspecialchars($r['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></span></a><?php endforeach; ?></section><?php endif; ?>
            <section class="side-card"><h2 class="side-label">Enjoyed this article?</h2><p class="muted" style="margin:0">Explore more notes on backend development, Laravel, and API design.</p><a class="side-link" href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>">Browse all articles <i class="bi bi-arrow-right"></i></a></section>
        </aside>
    </main>
    <footer class="end-nav"><div class="wrap end-nav-inner"><span>&copy; <?= date('Y') ?> <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><span><a href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-arrow-left"></i> All articles</a> <span aria-hidden="true">&middot;</span> <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">Portfolio</a></span></div></footer>
</div>
</body>
</html>
