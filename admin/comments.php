<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$comments = readJson('comments.json') ?? [];

// Manual add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['_action'] ?? 'add';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'approve' && $id > 0) {
        foreach ($comments as &$c) { if ((int)$c['id'] === $id) { $c['approved'] = true; break; } }
        unset($c);
        writeJson('comments.json', $comments);
        setFlash('success', 'Comment approved.');
        header('Location: ' . adminUrl('comments')); exit;
    }
    if ($action === 'delete' && $id > 0) {
        $comments = array_values(array_filter($comments, fn($c) => (int)$c['id'] !== $id));
        writeJson('comments.json', $comments);
        setFlash('success', 'Comment deleted.');
        header('Location: ' . adminUrl('comments')); exit;
    }
    if ($action !== 'add') {
        http_response_code(400);
        exit('Unknown comment action.');
    }
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $message === '') {
        setFlash('error', 'Name and comment are required.');
        header('Location: ' . adminUrl('comments')); exit;
    }
    $comments[] = [
        'id'       => nextId($comments),
        'post_id'  => (int)($_POST['post_id'] ?? 0),
        'name'     => $name,
        'email'    => trim($_POST['email'] ?? ''),
        'message'  => $message,
        'date'     => date('Y-m-d H:i:s'),
        'approved' => !empty($_POST['approved']),
    ];
    writeJson('comments.json', $comments);
    setFlash('success', 'Comment added.');
    header('Location: ' . adminUrl('comments')); exit;
}

$pageTitle  = 'Comments';
$activePage = 'comments';
include __DIR__ . '/includes/layout.php';

$blogs    = readJson('blogs.json') ?? [];
$pending  = array_filter($comments, fn($c) => empty($c['approved']));
$approved = array_filter($comments, fn($c) => !empty($c['approved']));
?>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card"><div class="stat-icon yellow"><i class="bi bi-chat-dots-fill"></i></div><div class="stat-body"><h3><?= count($comments) ?></h3><p>Total Comments</p></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div><div class="stat-body"><h3><?= count($approved) ?></h3><p>Approved</p></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="bi bi-clock-fill"></i></div><div class="stat-body"><h3><?= count($pending) ?></h3><p>Pending</p></div></div>
</div>

<!-- Add Comment Form -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-plus-circle"></i> Add Comment</span>
    </div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Commenter name">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Email address">
            </div>
            <div class="form-group">
                <label class="form-label">Blog Post</label>
                <select name="post_id" class="form-control">
                    <option value="0">-- General --</option>
                    <?php foreach ($blogs as $b): ?>
                    <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="3" required placeholder="Comment text"></textarea>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="approved" value="1"> Auto-approve
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Comment</button>
    </form>
</div>

<!-- Comments List -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-chat-left-dots"></i> All Comments</span>
    </div>
    <?php if (empty($comments)): ?>
        <p style="color:var(--muted);text-align:center;padding:40px 0">No comments yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Post</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach (array_reverse($comments) as $c):
                $postTitle = '—';
                foreach ($blogs as $b) { if ((int)$b['id'] === (int)($c['post_id'] ?? 0)) { $postTitle = $b['title']; break; } }
            ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td style="font-size:0.8rem;color:var(--muted)"><?= e($c['email'] ?? '—') ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.85rem"><?= e($c['message']) ?></td>
                <td style="font-size:0.78rem;color:var(--muted)"><?= e(substr($postTitle,0,30)) ?></td>
                <td style="font-size:0.78rem;color:var(--muted)"><?= e(substr($c['date'],0,10)) ?></td>
                <td><?= empty($c['approved']) ? '<span class="badge badge-warning">Pending</span>' : '<span class="badge badge-success">Approved</span>' ?></td>
                <td>
                    <div style="display:flex;gap:4px">
                        <?php if (empty($c['approved'])): ?>
                        <form method="POST" action="<?= e(adminUrl('comments')) ?>">
                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="approve"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn btn-success btn-xs" aria-label="Approve comment"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="<?= e(adminUrl('comments')) ?>" data-confirm="Delete this comment?">
                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete comment"><i class="bi bi-trash"></i></button>
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

<?php include __DIR__ . '/includes/layout_end.php'; ?>
