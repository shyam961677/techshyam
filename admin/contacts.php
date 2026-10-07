<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$contacts = readJson('contacts.json') ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['_action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $contacts = array_values(array_filter($contacts, fn($c) => (int)$c['id'] !== $id));
        writeJson('contacts.json', $contacts);
        setFlash('success', 'Message deleted.');
    } elseif ($action === 'readall') {
        foreach ($contacts as &$c) $c['read'] = true;
        unset($c);
        writeJson('contacts.json', $contacts);
        setFlash('success', 'All messages marked as read.');
    } elseif ($action === 'read' && $id > 0) {
        foreach ($contacts as &$c) { if ((int)$c['id'] === $id) { $c['read'] = true; break; } }
        unset($c);
        writeJson('contacts.json', $contacts);
        setFlash('success', 'Message marked as read.');
    }
    header('Location: ' . adminUrl('contacts'));
    exit;
}

// View single
$view = null;
if (($_GET['action'] ?? '') === 'view' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    foreach ($contacts as $c) {
        if ((int)$c['id'] === $id) { $view = $c; break; }
    }
}

$unread = count(array_filter($contacts, fn($c) => empty($c['read'])));
$pageTitle  = 'Contact Form';
$activePage = 'contacts';
include __DIR__ . '/includes/layout.php';
?>
<?php if ($view): ?>
<!-- Message detail modal -->
<div style="margin-bottom:16px">
    <a href="<?= e(adminUrl('contacts')) ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-envelope-open"></i> Message from <?= e($view['name']) ?></span>
        <div style="display:flex;gap:8px">
            <?php if (empty($view['read'])): ?>
            <form method="POST" action="<?= e(adminUrl('contacts')) ?>">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="read"><input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-check2"></i> Mark Read</button>
            </form>
            <?php endif; ?>
            <form method="POST" action="<?= e(adminUrl('contacts')) ?>" data-confirm="Delete this message?">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>
    <table style="font-size:0.88rem;width:auto;border:none">
        <tr><td style="padding:8px 16px 8px 0;color:var(--muted);white-space:nowrap;border:none">From</td><td style="border:none;padding:8px 0"><?= e($view['name']) ?></td></tr>
        <tr><td style="padding:8px 16px 8px 0;color:var(--muted);border:none">Email</td><td style="border:none;padding:8px 0"><a href="mailto:<?= e($view['email']) ?>" style="color:var(--primary)"><?= e($view['email']) ?></a></td></tr>
        <tr><td style="padding:8px 16px 8px 0;color:var(--muted);border:none">Phone</td><td style="border:none;padding:8px 0"><?= e($view['phone'] ?? '—') ?></td></tr>
        <tr><td style="padding:8px 16px 8px 0;color:var(--muted);border:none">Subject</td><td style="border:none;padding:8px 0"><?= e($view['subject']) ?></td></tr>
        <tr><td style="padding:8px 16px 8px 0;color:var(--muted);border:none">Date</td><td style="border:none;padding:8px 0"><?= e($view['date']) ?></td></tr>
    </table>
    <hr style="border-color:var(--border2);margin:16px 0">
    <p style="font-size:0.88rem;color:var(--text);line-height:1.7;white-space:pre-wrap"><?= e($view['message']) ?></p>
    <div style="margin-top:20px">
        <a href="mailto:<?= e($view['email']) ?>?subject=Re: <?= urlencode($view['subject']) ?>" class="btn btn-primary">
            <i class="bi bi-reply"></i> Reply via Email
        </a>
    </div>
</div>

<?php else: ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div>
        <span style="color:var(--muted);font-size:0.85rem"><?= count($contacts) ?> total messages<?= $unread > 0 ? ", <span style='color:var(--warning)'>$unread unread</span>" : '' ?></span>
    </div>
    <div style="display:flex;gap:8px">
        <?php if ($unread > 0): ?>
        <form method="POST" action="<?= e(adminUrl('contacts')) ?>">
            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="readall">
            <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-check2-all"></i> Mark All Read</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-envelope-fill"></i> Contact Messages</span>
    </div>
    <?php if (empty($contacts)): ?>
        <p style="color:var(--muted);text-align:center;padding:40px 0;font-size:0.9rem">No messages yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Phone</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach (array_reverse($contacts) as $c): ?>
            <tr style="<?= empty($c['read']) ? 'font-weight:600' : '' ?>">
                <td style="color:var(--muted);font-size:0.78rem"><?= (int)$c['id'] ?></td>
                <td><?= e($c['name']) ?></td>
                <td style="font-size:0.82rem"><?= e($c['email']) ?></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($c['subject']) ?></td>
                <td style="font-size:0.82rem;color:var(--muted)"><?= e($c['phone'] ?? '—') ?></td>
                <td style="color:var(--muted);font-size:0.78rem"><?= e(substr($c['date'],0,10)) ?></td>
                <td><?= empty($c['read']) ? '<span class="badge badge-warning">New</span>' : '<span class="badge badge-muted">Read</span>' ?></td>
                <td>
                    <div style="display:flex;gap:4px">
                        <a href="<?= e(adminUrl('contacts')) ?>?action=view&id=<?= (int)$c['id'] ?>" class="btn btn-secondary btn-xs" aria-label="View message"><i class="bi bi-eye"></i></a>
                        <form method="POST" action="<?= e(adminUrl('contacts')) ?>" data-confirm="Delete this message?">
                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete message"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
