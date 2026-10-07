<?php
require_once __DIR__ . '/admin/includes/auth.php';
trackVisitor();

$slug     = trim($_GET['slug'] ?? '');
$blogs    = readJson('blogs.json') ?? [];
$profile  = readJson('profile.json') ?? [];
$settings = readJson('settings.json') ?? [];

// Find the post
$post = null;
$postIndex = null;
foreach ($blogs as $i => $b) {
    if (($b['slug'] ?? '') === $slug && !empty($b['published'])) {
        $post = $b;
        $postIndex = $i;
        break;
    }
}

if (!$post) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404</title></head><body style="font-family:Poppins,sans-serif;text-align:center;padding:80px;background:#0f172a;color:#f1f5f9">
          <h1>404 — Post Not Found</h1><p><a href="/blog" style="color:#3b82f6">← Back to Blog</a></p></body></html>';
    exit;
}

// Increment view count
$blogs[$postIndex]['views'] = (int)($post['views'] ?? 0) + 1;
writeJson('blogs.json', $blogs);

// Related posts (same category, excluding current)
$related = array_slice(array_filter($blogs, fn($b) =>
    !empty($b['published']) &&
    $b['slug'] !== $slug &&
    ($b['category'] ?? '') === ($post['category'] ?? '')
), 0, 3);

// Comments for this post
$allComments = readJson('comments.json') ?? [];
$postComments = array_filter($allComments, fn($c) => (int)($c['post_id'] ?? 0) === (int)$post['id'] && !empty($c['approved']));

// Handle new comment submission
$commentError = '';
$commentSuccess = '';
if (!empty($settings['comments_enabled']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cName    = htmlspecialchars(trim($_POST['c_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cEmail   = filter_var(trim($_POST['c_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $cMessage = htmlspecialchars(trim($_POST['c_message'] ?? ''), ENT_QUOTES, 'UTF-8');

    if ($cName === '' || $cMessage === '') {
        $commentError = 'Name and message are required.';
    } else {
        $allComments[] = [
            'id'       => (empty($allComments) ? 1 : max(array_column($allComments, 'id')) + 1),
            'post_id'  => (int)$post['id'],
            'name'     => $cName,
            'email'    => $cEmail,
            'message'  => $cMessage,
            'date'     => date('Y-m-d H:i:s'),
            'approved' => false,
        ];
        writeJson('comments.json', $allComments);
        $commentSuccess = 'Comment submitted! It will appear after review.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES) ?> — <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES) ?>">
    <?php if (!empty($post['image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { font-family:'Poppins',sans-serif; background:#f5f8fc; color:#444; }
        .post-hero { background:#0f172a; color:#f1f5f9; padding:60px 0 40px; }
        .post-hero .cat { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#3b82f6; margin-bottom:12px; }
        .post-hero h1 { font-size:1.8rem; font-weight:700; line-height:1.35; max-width:720px; }
        .post-hero .meta { display:flex; gap:16px; flex-wrap:wrap; margin-top:14px; color:#94a3b8; font-size:.82rem; }
        .post-hero .meta span { display:flex; align-items:center; gap:5px; }
        .post-cover { width:100%; max-height:400px; object-fit:cover; border-radius:12px; margin:28px 0; box-shadow:0 8px 32px rgba(0,0,0,.1); }
        .post-body { font-size:.92rem; line-height:1.85; color:#334155; }
        .post-body h2,h3 { color:#1e293b; margin:28px 0 12px; font-weight:700; }
        .post-body p { margin-bottom:16px; }
        .post-body a { color:#3b82f6; }
        .post-body pre { background:#1e293b; color:#a5f3fc; border-radius:8px; padding:16px; overflow-x:auto; font-size:.82rem; }
        .post-body code { background:#eff6ff; color:#2563eb; padding:2px 6px; border-radius:4px; font-size:.85em; }
        .tag { display:inline-block; padding:4px 10px; background:#eff6ff; color:#3b82f6; border-radius:20px; font-size:.75rem; font-weight:600; margin-right:6px; margin-bottom:6px; }
        .section-title { font-size:1.1rem; font-weight:700; color:#1e293b; border-left:3px solid #3b82f6; padding-left:12px; margin-bottom:20px; }
        .comment-card { background:#fff; border-radius:10px; padding:16px; box-shadow:0 1px 6px rgba(0,0,0,.06); margin-bottom:12px; }
        .comment-card .name { font-weight:600; font-size:.88rem; color:#1e293b; }
        .comment-card .date { font-size:.75rem; color:#94a3b8; margin-left:10px; }
        .comment-card .msg  { font-size:.85rem; color:#475569; margin-top:6px; line-height:1.6; }
        .related-card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06); display:flex; gap:14px; padding:14px; text-decoration:none; color:inherit; transition:box-shadow .2s; }
        .related-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.12); }
        .related-card img { width:80px; height:60px; object-fit:cover; border-radius:6px; flex-shrink:0; }
        .related-card .r-title { font-size:.83rem; font-weight:600; color:#1e293b; }
        .related-card .r-date  { font-size:.74rem; color:#94a3b8; margin-top:4px; }
        .form-control { width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:8px; font-family:'Poppins',sans-serif; font-size:.85rem; outline:none; transition:border-color .2s; }
        .form-control:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
        textarea.form-control { resize:vertical; min-height:90px; }
        .btn-primary { background:#3b82f6; color:#fff; border:none; border-radius:8px; padding:10px 24px; font-family:'Poppins',sans-serif; font-weight:600; font-size:.88rem; cursor:pointer; transition:background .2s; }
        .btn-primary:hover { background:#2563eb; }
        .alert { padding:12px 16px; border-radius:8px; font-size:.85rem; margin-bottom:16px; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .back-link { color:#3b82f6; text-decoration:none; font-size:.85rem; display:inline-flex; align-items:center; gap:4px; margin-bottom:20px; }
    </style>
</head>
<body>

<div class="post-hero">
    <div class="container">
        <?php if (!empty($post['category'])): ?>
        <div class="cat"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></div>
        <?php endif; ?>
        <h1><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></h1>
        <div class="meta">
            <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($post['author'] ?? '', ENT_QUOTES) ?></span>
            <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($post['date'] ?? '', ENT_QUOTES) ?></span>
            <span><i class="bi bi-eye"></i> <?= (int)($post['views'] ?? 0) ?> views</span>
            <span><i class="bi bi-chat-dots"></i> <?= count($postComments) ?> comments</span>
        </div>
    </div>
</div>

<div class="container py-5">
    <a href="/blog" class="back-link"><i class="bi bi-arrow-left"></i> All Posts</a>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:32px;align-items:start">

    <!-- Article body -->
    <div>
        <?php if (!empty($post['image'])): ?>
        <img src="<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" class="post-cover">
        <?php endif; ?>

        <div class="post-body">
            <?php
            // Render content — support both HTML and plain text
            $content = $post['content'] ?? '';
            // If it contains no HTML tags, convert newlines to paragraphs
            if (strip_tags($content) === $content) {
                echo '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES)) . '</p>';
            } else {
                echo $content; // Stored HTML (from admin panel)
            }
            ?>
        </div>

        <?php if (!empty($post['tags'])): ?>
        <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0">
            <span style="font-size:.8rem;color:#94a3b8;margin-right:8px">Tags:</span>
            <?php foreach ($post['tags'] as $tag): ?>
            <span class="tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div style="margin-top:40px">
            <div class="section-title">Comments (<?= count($postComments) ?>)</div>

            <?php if (empty($postComments)): ?>
            <p style="color:#94a3b8;font-size:.88rem">No comments yet. Be the first!</p>
            <?php else: ?>
            <?php foreach ($postComments as $c): ?>
            <div class="comment-card">
                <div><span class="name"><?= htmlspecialchars($c['name'], ENT_QUOTES) ?></span><span class="date"><?= htmlspecialchars(substr($c['date'],0,10), ENT_QUOTES) ?></span></div>
                <div class="msg"><?= htmlspecialchars($c['message'], ENT_QUOTES) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($settings['comments_enabled'])): ?>
            <div style="margin-top:24px">
                <div class="section-title">Leave a Comment</div>
                <?php if ($commentError): ?><div class="alert alert-error"><?= htmlspecialchars($commentError, ENT_QUOTES) ?></div><?php endif; ?>
                <?php if ($commentSuccess): ?><div class="alert alert-success"><?= htmlspecialchars($commentSuccess, ENT_QUOTES) ?></div><?php endif; ?>
                <form method="POST">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                        <input type="text" name="c_name" class="form-control" placeholder="Your Name *" required>
                        <input type="email" name="c_email" class="form-control" placeholder="Email (optional)">
                    </div>
                    <textarea name="c_message" class="form-control" placeholder="Write your comment…" required style="margin-bottom:12px"></textarea>
                    <button type="submit" class="btn-primary"><i class="bi bi-send"></i> Post Comment</button>
                </form>
            </div>
            <?php else: ?>
            <p style="color:#94a3b8;font-size:.82rem">Comments are currently disabled.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Author card -->
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:20px;text-align:center">
            <?php if (!empty($profile['photo'])): ?>
            <img src="<?= htmlspecialchars($profile['photo'], ENT_QUOTES) ?>" style="width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:10px">
            <?php endif; ?>
            <div style="font-weight:700;font-size:.9rem;color:#1e293b"><?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?></div>
            <div style="font-size:.78rem;color:#94a3b8;margin-bottom:12px"><?= htmlspecialchars($profile['title'] ?? '', ENT_QUOTES) ?></div>
            <a href="/blog" style="font-size:.8rem;color:#3b82f6;text-decoration:none">← All Posts</a>
        </div>

        <!-- Related posts -->
        <?php if (!empty($related)): ?>
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06)">
            <div class="section-title" style="margin-bottom:16px">Related Posts</div>
            <?php foreach ($related as $r): ?>
            <a href="/blog/<?= htmlspecialchars($r['slug'] ?? '', ENT_QUOTES) ?>" class="related-card" style="margin-bottom:10px">
                <?php if (!empty($r['image'])): ?>
                <img src="<?= htmlspecialchars($r['image'], ENT_QUOTES) ?>" alt="">
                <?php else: ?>
                <div style="width:80px;height:60px;background:#f1f5f9;border-radius:6px;flex-shrink:0"></div>
                <?php endif; ?>
                <div>
                    <div class="r-title"><?= htmlspecialchars($r['title'], ENT_QUOTES) ?></div>
                    <div class="r-date"><?= htmlspecialchars($r['date'] ?? '', ENT_QUOTES) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    </div><!-- grid -->
</div>

<footer style="background:#0f172a;color:#64748b;text-align:center;padding:24px;font-size:.82rem;margin-top:40px">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?> ·
    <a href="/blog" style="color:#3b82f6">Blog</a> ·
    <a href="index1.php" style="color:#3b82f6">Portfolio</a>
</footer>
</body>
</html>
