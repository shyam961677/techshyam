<?php
require_once __DIR__ . '/admin/includes/auth.php';
trackVisitor();

$blogs    = readJson('blogs.json') ?? [];
$profile  = readJson('profile.json') ?? [];
$settings = readJson('settings.json') ?? [];

// Only show published posts, newest first
$published = array_reverse(array_values(array_filter($blogs, fn($b) => !empty($b['published']))));

// Simple category filter
$cat = trim($_GET['cat'] ?? '');
if ($cat !== '') {
    $published = array_filter($published, fn($b) => strtolower($b['category'] ?? '') === strtolower($cat));
}

$seo = $profile['seo'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog — <?= htmlspecialchars($profile['name'] ?? 'Portfolio', ENT_QUOTES) ?></title>
    <meta name="description" content="Articles and tutorials by <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { font-family:'Poppins',sans-serif; background:#f5f8fc; color:#444; }
        .blog-hero { background:#0f172a; color:#f1f5f9; padding:60px 0 40px; text-align:center; }
        .blog-hero h1 { font-size:2rem; font-weight:700; }
        .blog-hero p { color:#94a3b8; margin-top:8px; }
        .blog-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; padding:40px 0; }
        .blog-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.07); transition:transform .2s,box-shadow .2s; }
        .blog-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
        .blog-card img { width:100%; height:190px; object-fit:cover; }
        .blog-card-img-placeholder { width:100%; height:190px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; }
        .blog-card-body { padding:20px; }
        .blog-card-cat { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#3b82f6; margin-bottom:8px; }
        .blog-card-title { font-size:.95rem; font-weight:700; color:#1e293b; line-height:1.4; margin-bottom:8px; text-decoration:none; display:block; }
        .blog-card-title:hover { color:#3b82f6; }
        .blog-card-excerpt { font-size:.82rem; color:#64748b; line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .blog-card-meta { display:flex; align-items:center; gap:12px; margin-top:14px; font-size:.75rem; color:#94a3b8; }
        .tag { display:inline-block; padding:2px 8px; background:#eff6ff; color:#3b82f6; border-radius:20px; font-size:.72rem; font-weight:600; margin-right:4px; }
        .cat-filter { display:flex; gap:8px; flex-wrap:wrap; justify-content:center; margin:20px 0; }
        .cat-filter a { padding:6px 16px; border-radius:20px; font-size:.8rem; font-weight:600; text-decoration:none; background:#fff; color:#64748b; border:1.5px solid #e2e8f0; transition:all .2s; }
        .cat-filter a.active, .cat-filter a:hover { background:#3b82f6; color:#fff; border-color:#3b82f6; }
        .back-link { color:#3b82f6; text-decoration:none; font-size:.85rem; display:inline-flex; align-items:center; gap:4px; }
        .back-link:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="blog-hero">
    <div class="container">
        <a href="index1.php" class="back-link" style="color:#94a3b8;margin-bottom:16px;display:inline-flex"><i class="bi bi-arrow-left"></i> Back to Portfolio</a>
        <h1><i class="bi bi-journal-richtext"></i> Blog</h1>
        <p>Articles, tutorials and thoughts by <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?></p>
    </div>
</div>

<div class="container py-4">
    <?php
    // Collect categories
    $allCats = array_unique(array_filter(array_column($blogs, 'category')));
    if (!empty($allCats)):
    ?>
    <div class="cat-filter">
        <a href="/blog" class="<?= $cat === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($allCats as $c): ?>
        <a href="/blog?cat=<?= urlencode($c) ?>" class="<?= strtolower($cat) === strtolower($c) ? 'active' : '' ?>"><?= htmlspecialchars($c, ENT_QUOTES) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($published)): ?>
    <div style="text-align:center;padding:80px 0;color:#94a3b8">
        <i class="bi bi-journal-x" style="font-size:3rem"></i>
        <p style="margin-top:12px">No posts published yet.</p>
    </div>
    <?php else: ?>
    <div class="blog-grid">
        <?php foreach ($published as $post): ?>
        <div class="blog-card">
            <?php if (!empty($post['image'])): ?>
                <img src="<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" loading="lazy">
            <?php else: ?>
                <div class="blog-card-img-placeholder"><i class="bi bi-image" style="font-size:2rem;color:#cbd5e1"></i></div>
            <?php endif; ?>
            <div class="blog-card-body">
                <?php if (!empty($post['category'])): ?>
                <div class="blog-card-cat"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></div>
                <?php endif; ?>
                <a href="/blog/<?= htmlspecialchars($post['slug'] ?? '', ENT_QUOTES) ?>" class="blog-card-title">
                    <?= htmlspecialchars($post['title'], ENT_QUOTES) ?>
                </a>
                <p class="blog-card-excerpt"><?= htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES) ?></p>
                <div class="blog-card-meta">
                    <span><i class="bi bi-person"></i> <?= htmlspecialchars($post['author'] ?? '', ENT_QUOTES) ?></span>
                    <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($post['date'] ?? '', ENT_QUOTES) ?></span>
                    <span><i class="bi bi-eye"></i> <?= (int)($post['views'] ?? 0) ?></span>
                </div>
                <?php if (!empty($post['tags'])): ?>
                <div style="margin-top:10px">
                    <?php foreach (array_slice($post['tags'], 0, 3) as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<footer style="background:#0f172a;color:#64748b;text-align:center;padding:24px;font-size:.82rem;margin-top:40px">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES) ?> · <a href="index1.php" style="color:#3b82f6">Portfolio</a>
</footer>
</body>
</html>
