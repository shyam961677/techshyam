<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$skills = readJson('skills.json') ?? [];

// Save order (drag/drop via AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'reorder') {
    verifyCsrfToken();
    $ids = array_map('intval', explode(',', $_POST['order'] ?? ''));
    $sorted = [];
    foreach ($ids as $pos => $id) {
        foreach ($skills as $s) {
            if ((int)$s['id'] === $id) { $s['order'] = $pos + 1; $sorted[] = $s; break; }
        }
    }
    writeJson('skills.json', $sorted);
    echo json_encode(['ok'=>true]); exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    $skills = array_values(array_filter($skills, fn($s) => (int)$s['id'] !== $id));
    writeJson('skills.json', $skills);
    setFlash('success', 'Skill deleted.');
    header('Location: ' . adminUrl('skills')); exit;
}

// Save / Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id      = (int)($_POST['id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    if ($name !== '') {
        if ($id > 0) {
            foreach ($skills as &$s) {
                if ((int)$s['id'] === $id) { $s['name'] = $name; unset($s['percent']); break; }
            }
            unset($s);
            setFlash('success', 'Skill updated.');
        } else {
            $skills[] = ['id' => nextId($skills), 'name' => $name, 'order' => count($skills) + 1];
            setFlash('success', 'Skill added.');
        }
        writeJson('skills.json', $skills);
    }
    header('Location: ' . adminUrl('skills')); exit;
}

usort($skills, fn($a,$b) => ($a['order']??99) <=> ($b['order']??99));

$pageTitle  = 'Skills';
$activePage = 'skills';
include __DIR__ . '/includes/layout.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<!-- Skill List -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-bar-chart-line"></i> Skills (drag to reorder)</span>
    </div>
    <?php if (empty($skills)): ?>
        <p style="color:var(--muted);text-align:center;padding:30px 0">No skills yet.</p>
    <?php else: ?>
    <ul id="skillList" style="list-style:none;padding:0">
        <?php foreach ($skills as $s): ?>
        <li data-id="<?= (int)$s['id'] ?>" style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border2);cursor:grab">
            <i class="bi bi-grip-vertical" style="color:var(--muted);font-size:1.1rem;flex-shrink:0"></i>
            <div style="flex:1">
                <span style="font-size:0.88rem;font-weight:500"><?= e($s['name']) ?></span>
            </div>
            <div style="display:flex;gap:4px;flex-shrink:0">
                <button onclick="editSkill(<?= (int)$s['id'] ?>, <?= e(json_encode($s['name'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>)"
                        class="btn btn-secondary btn-xs"><i class="bi bi-pencil"></i></button>
                <form method="POST" action="<?= e(adminUrl('skills')) ?>" style="display:inline" data-confirm="Delete skill?">
                    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="_action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete <?= e($s['name']) ?>"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<!-- Add / Edit Form -->
<div>
    <div class="card" id="skillFormCard">
        <div class="card-header">
            <span class="card-title" id="formCardTitle"><i class="bi bi-plus-circle"></i> Add Skill</span>
        </div>
        <form method="POST" id="skillForm">
            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="id" id="skillId" value="0">
            <div class="form-group">
                <label class="form-label">Skill Name</label>
                <input type="text" name="name" id="skillName" class="form-control" required placeholder="e.g. React">
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// Drag-to-reorder
const list = document.getElementById('skillList');
if (list) {
    Sortable.create(list, {
        animation: 150,
        handle: '.bi-grip-vertical',
        onEnd: function() {
            const ids = [...list.querySelectorAll('li')].map(el => el.dataset.id).join(',');
            fetch('<?= e(adminUrl('skills')) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type':'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: '_action=reorder&order=' + ids
            });
        }
    });
}

function editSkill(id, name) {
    document.getElementById('skillId').value = id;
    document.getElementById('skillName').value = name;
    document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Skill';
    document.getElementById('skillFormCard').scrollIntoView({behavior:'smooth'});
}
function resetForm() {
    document.getElementById('skillId').value = 0;
    document.getElementById('skillForm').reset();
    document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Add Skill';
}
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
