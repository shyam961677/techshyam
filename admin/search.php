<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$query   = trim($_GET['q'] ?? '');
$results = [];

if ($query !== '') {
    $sources = [
        ['file' => 'blogs.json',    'type' => 'Blog Post',  'link' => adminUrl('blogs') . '?action=edit&id='],
        ['file' => 'projects.json', 'type' => 'Project',    'link' => adminUrl('projects') . '?action=edit&id='],
        ['file' => 'services.json', 'type' => 'Service',    'link' => adminUrl('services')],
        ['file' => 'contacts.json', 'type' => 'Contact',    'link' => adminUrl('contacts') . '?action=view&id='],
        ['file' => 'comments.json', 'type' => 'Comment',    'link' => adminUrl('comments')],
    ];

    foreach ($sources as $src) {
        $items = readJson($src['file']) ?? [];
        foreach ($items as $item) {
            // Flatten item to string for search
            $text = strtolower(implode(' ', array_map(fn($v) => is_array($v) ? implode(' ',$v) : (string)$v, $item)));
            if (str_contains($text, strtolower($query))) {
                $results[] = [
                    'type'  => $src['type'],
                    'link'  => $src['link'] . ($item['id'] ?? ''),
                    'title' => $item['title'] ?? $item['name'] ?? $item['subject'] ?? 'Item #' . ($item['id'] ?? '?'),
                    'snippet' => substr(strip_tags($item['excerpt'] ?? $item['description'] ?? $item['message'] ?? $item['bio'] ?? ''), 0, 120),
                ];
            }
        }
    }
}

$pageTitle  = 'Search';
$activePage = 'search';
include __DIR__ . '/includes/layout.php';
?>

<div class="card" style="margin-bottom:20px">
    <form method="GET" action="<?= e(adminUrl('search')) ?>" style="display:flex;gap:10px">
        <input type="text" name="q" class="form-control" value="<?= e($query) ?>" placeholder="Search blogs, projects, contacts, comments…" autofocus>
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
    </form>
</div>

<?php if ($query !== ''): ?>
<div style="margin-bottom:14px;color:var(--muted);font-size:0.85rem">
    Found <strong style="color:var(--text)"><?= count($results) ?></strong> result<?= count($results) !== 1 ? 's' : '' ?> for "<strong style="color:var(--primary)"><?= e($query) ?></strong>"
</div>

<?php if (empty($results)): ?>
    <div class="card" style="text-align:center;padding:40px">
        <i class="bi bi-search" style="font-size:2rem;color:var(--muted)"></i>
        <p style="color:var(--muted);margin-top:10px">No results found. Try different keywords.</p>
    </div>
<?php else: ?>
    <?php foreach ($results as $r): ?>
    <div class="card" style="margin-bottom:12px;padding:16px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <span class="badge badge-info"><?= e($r['type']) ?></span>
            <a href="<?= e($r['link']) ?>" style="font-size:0.9rem;font-weight:600;color:var(--primary);text-decoration:none">
                <?= e($r['title']) ?>
            </a>
        </div>
        <?php if ($r['snippet']): ?>
        <p style="font-size:0.82rem;color:var(--muted);line-height:1.5"><?= e($r['snippet']) ?>…</p>
        <?php endif; ?>
        <a href="<?= e($r['link']) ?>" class="btn btn-secondary btn-xs" style="margin-top:8px"><i class="bi bi-arrow-right"></i> Open</a>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php else: ?>
<div class="card" style="text-align:center;padding:50px">
    <i class="bi bi-search" style="font-size:3rem;color:var(--muted)"></i>
    <p style="color:var(--muted);margin-top:12px;font-size:0.9rem">Type a keyword above to search across all content.</p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
