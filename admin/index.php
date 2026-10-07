<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'index';

$visitors  = readJson('visitors.json') ?? [];
$contacts  = readJson('contacts.json') ?? [];
$blogs     = readJson('blogs.json') ?? [];
$comments  = readJson('comments.json') ?? [];
$projects  = readJson('projects.json') ?? [];

$unread    = count(array_filter($contacts, fn($c) => empty($c['read'])));
$pending   = count(array_filter($comments, fn($c) => empty($c['approved'])));
$published = count(array_filter($blogs, fn($b) => !empty($b['published'])));

// Recent contacts
$recentContacts = array_slice(array_reverse($contacts), 0, 5);
// Recent visitors
$recentVisitors = array_slice($visitors['logs'] ?? [], 0, 10);

include __DIR__ . '/includes/layout.php';
?>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
        <div class="stat-body">
            <h3><?= number_format($visitors['total'] ?? 0) ?></h3>
            <p>Total Visitors</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-calendar-day"></i></div>
        <div class="stat-body">
            <h3><?= number_format($visitors['today'] ?? 0) ?></h3>
            <p>Today's Visitors</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-envelope-fill"></i></div>
        <div class="stat-body">
            <h3><?= count($contacts) ?> <small style="font-size:0.9rem;color:var(--danger)"><?= $unread > 0 ? "($unread new)" : '' ?></small></h3>
            <p>Contact Messages</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-journal-richtext"></i></div>
        <div class="stat-body">
            <h3><?= count($blogs) ?></h3>
            <p>Blog Posts (<?= $published ?> published)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon cyan"><i class="bi bi-chat-dots-fill"></i></div>
        <div class="stat-body">
            <h3><?= count($comments) ?> <small style="font-size:0.9rem;color:var(--warning)"><?= $pending > 0 ? "($pending pending)" : '' ?></small></h3>
            <p>Comments</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-columns-gap"></i></div>
        <div class="stat-body">
            <h3><?= count($projects) ?></h3>
            <p>Projects</p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">
<!-- Recent Messages -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-envelope-fill" style="color:var(--primary)"></i> Recent Messages</span>
        <a href="<?= e(adminUrl('contacts')) ?>" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <?php if (empty($recentContacts)): ?>
        <p style="color:var(--muted);font-size:0.85rem;text-align:center;padding:30px 0;">No messages yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Subject</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentContacts as $c): ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($c['subject']) ?></td>
                <td style="color:var(--muted);font-size:0.78rem"><?= e(substr($c['date'],0,10)) ?></td>
                <td><?= empty($c['read']) ? '<span class="badge badge-warning">New</span>' : '<span class="badge badge-muted">Read</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Visitor Logs -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-graph-up" style="color:var(--success)"></i> Recent Visitors</span>
        <a href="<?= e(adminUrl('visitors')) ?>" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <?php if (empty($recentVisitors)): ?>
        <p style="color:var(--muted);font-size:0.85rem;text-align:center;padding:30px 0;">No visitor data yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>IP</th><th>Page</th><th>Device</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recentVisitors as $v): ?>
            <tr>
                <td style="font-size:0.78rem;color:var(--muted)"><?= e($v['ip']) ?></td>
                <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.78rem"><?= e($v['page']) ?></td>
                <td><span class="badge badge-info"><?= e($v['device']) ?></span></td>
                <td style="color:var(--muted);font-size:0.75rem"><?= e(substr($v['date'],5,11)) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- Quick Links -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-lightning-fill" style="color:var(--warning)"></i> Quick Actions</span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:10px">
        <a href="<?= e(adminUrl('blogs')) ?>?action=new" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Blog Post</a>
        <a href="<?= e(adminUrl('projects')) ?>?action=new" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Project</a>
        <a href="<?= e(adminUrl('skills')) ?>" class="btn btn-secondary"><i class="bi bi-bar-chart-line"></i> Edit Skills</a>
        <a href="<?= e(adminUrl('profile')) ?>" class="btn btn-secondary"><i class="bi bi-person-circle"></i> Edit Profile</a>
        <a href="<?= e(adminUrl('contacts')) ?>" class="btn btn-secondary"><i class="bi bi-envelope"></i> View Messages <?= $unread > 0 ? "<span class='badge badge-danger'>$unread</span>" : '' ?></a>
        <a href="<?= e(appBasePath() . '/') ?>" target="_blank" rel="noopener" class="btn btn-secondary"><i class="bi bi-eye"></i> View Live Site</a>
    </div>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
