<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$projects = readJson('projects.json') ?? [];
$action   = $_GET['action'] ?? 'list';
$editId   = isset($_GET['id']) ? (int)$_GET['id'] : null;
$edit     = null;

if ($editId && in_array($action, ['edit'], true)) {
    foreach ($projects as $p) { if ((int)$p['id'] === $editId) { $edit = $p; break; } }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['_action'] ?? '', ['delete', 'toggle'], true)) {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['_action'] ?? '') === 'delete') {
        $projects = array_values(array_filter($projects, fn($p) => (int)$p['id'] !== $id));
        writeJson('projects.json', $projects);
        setFlash('success', 'Project deleted.');
    } else {
        foreach ($projects as &$p) {
            if ((int)$p['id'] === $id) { $p['active'] = !($p['active'] ?? true); break; }
        }
        unset($p);
        writeJson('projects.json', $projects);
    }
    header('Location: ' . adminUrl('projects')); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id    = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $url   = trim($_POST['url'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $image = $_POST['existing_image'] ?? '';

    $uploaded = handleImageUpload('image', 'proj');
    if ($uploaded) $image = $uploaded;

    if ($title !== '') {
        if ($id > 0) {
            foreach ($projects as &$p) {
                if ((int)$p['id'] === $id) { $p['title']=$title; $p['url']=$url; $p['description']=$desc; $p['image']=$image; break; }
            }
            unset($p);
            setFlash('success', 'Project updated.');
        } else {
            $projects[] = ['id'=>nextId($projects),'title'=>$title,'image'=>$image,'url'=>$url,'description'=>$desc,'order'=>count($projects)+1,'active'=>true];
            setFlash('success', 'Project added.');
        }
        writeJson('projects.json', $projects);
    }
        header('Location: ' . adminUrl('projects')); exit;
}

usort($projects, fn($a,$b) => ($a['order']??99) <=> ($b['order']??99));

$pageTitle  = 'Projects';
$activePage = 'projects';
include __DIR__ . '/includes/layout.php';
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<div style="margin-bottom:16px">
    <a href="<?= e(adminUrl('projects')) ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-<?= $edit ? 'pencil' : 'plus-lg' ?>"></i> <?= $edit ? 'Edit Project' : 'New Project' ?></span>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="_action" value="save">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <input type="hidden" name="existing_image" value="<?= e($edit['image'] ?? '') ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
                <label class="form-label">Project Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= e($edit['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Live URL</label>
                <input type="url" name="url" class="form-control" value="<?= e($edit['url'] ?? '') ?>" placeholder="https://…">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($edit['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Screenshot Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if (!empty($edit['image'])): ?>
                    <img src="../<?= e($edit['image']) ?>" style="height:80px;margin-top:8px;border-radius:6px;object-fit:cover">
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:8px">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
            <a href="<?= e(adminUrl('projects')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:20px">
    <a href="<?= e(adminUrl('projects')) ?>?action=new" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Project</a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px">
<?php foreach ($projects as $p): ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php if (!empty($p['image'])): ?>
    <img src="../<?= e($p['image']) ?>" style="width:100%;height:160px;object-fit:cover">
    <?php else: ?>
    <div style="height:120px;background:var(--border);display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:2rem;color:var(--muted)"></i></div>
    <?php endif; ?>
    <div style="padding:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <strong style="font-size:0.9rem"><?= e($p['title']) ?></strong>
            <form method="POST" action="<?= e(adminUrl('projects')) ?>" style="display:inline">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="toggle"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" style="border:0;background:transparent;padding:0;cursor:pointer" aria-label="Toggle project visibility">
                <?= !empty($p['active']) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-muted">Hidden</span>' ?>
                </button>
            </form>
        </div>
        <p style="font-size:0.78rem;color:var(--muted);margin-bottom:12px"><?= e($p['description'] ?? '') ?></p>
        <div style="display:flex;gap:6px">
            <a href="<?= e(adminUrl('projects')) ?>?action=edit&id=<?= (int)$p['id'] ?>" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="<?= e(adminUrl('projects')) ?>" style="display:inline" data-confirm="Delete project?">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" aria-label="Delete project"><i class="bi bi-trash"></i></button>
            </form>
            <?php if (!empty($p['url'])): ?>
            <a href="<?= e($p['url']) ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-box-arrow-up-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
