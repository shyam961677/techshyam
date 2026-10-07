<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$services = readJson('services.json') ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    $services = array_values(array_filter($services, fn($s) => (int)$s['id'] !== $id));
    writeJson('services.json', $services);
    setFlash('success', 'Service deleted.');
    header('Location: ' . adminUrl('services')); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'toggle') {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    foreach ($services as &$s) {
        if ((int)$s['id'] === $id) { $s['active'] = !($s['active'] ?? true); break; }
    }
    unset($s);
    writeJson('services.json', $services);
    header('Location: ' . adminUrl('services')); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id    = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $icon  = trim($_POST['icon'] ?? 'bi-boxes');
    $desc  = trim($_POST['description'] ?? '');

    if ($title !== '') {
        if ($id > 0) {
            foreach ($services as &$s) {
                if ((int)$s['id'] === $id) { $s['title']=$title; $s['icon']=$icon; $s['description']=$desc; break; }
            }
            unset($s);
            setFlash('success', 'Service updated.');
        } else {
            $services[] = ['id'=>nextId($services),'icon'=>$icon,'title'=>$title,'description'=>$desc,'order'=>count($services)+1,'active'=>true];
            setFlash('success', 'Service added.');
        }
        writeJson('services.json', $services);
    }
    header('Location: ' . adminUrl('services')); exit;
}

usort($services, fn($a,$b) => ($a['order']??99) <=> ($b['order']??99));

$pageTitle  = 'Services';
$activePage = 'services';
include __DIR__ . '/includes/layout.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-gear"></i> Services (<?= count($services) ?>)</span>
    </div>
    <?php if (empty($services)): ?>
        <p style="color:var(--muted);text-align:center;padding:30px 0">No services yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Icon</th><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td><i class="bi <?= e($s['icon']) ?>" style="font-size:1.3rem;color:var(--primary)"></i></td>
                <td style="font-weight:600;font-size:0.85rem"><?= e($s['title']) ?></td>
                <td style="font-size:0.8rem;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($s['description']) ?></td>
                <td><form method="POST" action="<?= e(adminUrl('services')) ?>">
                    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="toggle"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button type="submit" style="border:0;background:transparent;padding:0;cursor:pointer" aria-label="Toggle <?= e($s['title']) ?> visibility">
                    <?= !empty($s['active']) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-muted">Hidden</span>' ?>
                    </button>
                </form></td>
                <td>
                    <div style="display:flex;gap:4px">
                        <button onclick="editService(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)" class="btn btn-secondary btn-xs"><i class="bi bi-pencil"></i></button>
                        <form method="POST" action="<?= e(adminUrl('services')) ?>" data-confirm="Delete this service?">
                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete service"><i class="bi bi-trash"></i></button>
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

<div class="card" id="serviceFormCard">
    <div class="card-header">
        <span class="card-title" id="serviceFormTitle"><i class="bi bi-plus-circle"></i> Add Service</span>
    </div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" id="serviceId" value="0">
        <div class="form-group">
            <label class="form-label">Bootstrap Icon Class</label>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="text" name="icon" id="serviceIcon" class="form-control" placeholder="bi-boxes" value="bi-boxes" oninput="document.getElementById('iconPreview').className='bi '+this.value">
                <i id="iconPreview" class="bi bi-boxes" style="font-size:1.5rem;color:var(--primary);flex-shrink:0"></i>
            </div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:4px">Find icons at <a href="https://icons.getbootstrap.com" target="_blank" style="color:var(--primary)">icons.getbootstrap.com</a></div>
        </div>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" id="serviceTitle" class="form-control" required placeholder="Service title">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" id="serviceDesc" class="form-control" rows="3" placeholder="Short description"></textarea>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
            <button type="button" onclick="resetServiceForm()" class="btn btn-secondary">Clear</button>
        </div>
    </form>
</div>
</div>

<script>
function editService(s) {
    document.getElementById('serviceId').value = s.id;
    document.getElementById('serviceTitle').value = s.title;
    document.getElementById('serviceIcon').value = s.icon;
    document.getElementById('serviceDesc').value = s.description || '';
    document.getElementById('iconPreview').className = 'bi ' + s.icon;
    document.getElementById('serviceFormTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Service';
}
function resetServiceForm() {
    document.getElementById('serviceId').value = 0;
    document.getElementById('serviceTitle').value = '';
    document.getElementById('serviceIcon').value = 'bi-boxes';
    document.getElementById('serviceDesc').value = '';
    document.getElementById('iconPreview').className = 'bi bi-boxes';
    document.getElementById('serviceFormTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Add Service';
}
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
