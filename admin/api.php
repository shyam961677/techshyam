<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle  = 'API';
$activePage = 'api';
include __DIR__ . '/includes/layout.php';

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . appBasePath() . '/api/';
?>

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-plug-fill"></i> Public JSON API</span>
        <span class="badge badge-success">Read-only</span>
    </div>
    <p style="font-size:0.85rem;color:var(--muted);margin-bottom:20px">
        Your portfolio data is exposed as a simple read-only JSON REST API. Useful for headless integrations, mobile apps, or external widgets.
    </p>

    <?php
    $endpoints = [
        ['GET', 'profile',  'Personal info, social links, contact details'],
        ['GET', 'skills',   'Technical skills list'],
        ['GET', 'services', 'Services list'],
        ['GET', 'projects', 'Portfolio projects'],
        ['GET', 'blogs',    'Published blog posts'],
    ];
    foreach ($endpoints as [$method,$ep,$desc]):
    ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--bg);border:1px solid var(--border2);border-radius:8px;margin-bottom:8px;flex-wrap:wrap">
        <span class="badge badge-info" style="font-size:0.72rem;flex-shrink:0"><?= $method ?></span>
        <code style="font-size:0.82rem;color:var(--primary);flex:1;word-break:break-all"><?= e($baseUrl) ?><?= $ep ?></code>
        <span style="font-size:0.78rem;color:var(--muted)"><?= $desc ?></span>
        <a href="<?= e(appBasePath() . '/api/' . $ep) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-xs"><i class="bi bi-box-arrow-up-right"></i></a>
        <button onclick="copyUrl('<?= htmlspecialchars($baseUrl.$ep,ENT_QUOTES) ?>')" class="btn btn-secondary btn-xs"><i class="bi bi-clipboard"></i></button>
    </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-code-slash"></i> Example Usage</span></div>
    <div style="background:var(--bg);border:1px solid var(--border2);border-radius:8px;padding:16px;font-size:0.82rem">
        <pre style="color:#a5f3fc;margin:0;white-space:pre-wrap;font-family:monospace">// Fetch skills via JavaScript
fetch('<?= e($baseUrl) ?>skills')
  .then(res => res.json())
  .then(data => console.log(data));

// Response format:
[
  { "id": 1, "name": "PHP" },
  { "id": 2, "name": "MySQL" },
  ...
]</pre>
    </div>
</div>

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => alert('Copied: ' + url));
}
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
