<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// Delete image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete' && !empty($_POST['file'])) {
    verifyCsrfToken();
    $file = basename($_POST['file']);  // prevent path traversal
    $path = UPLOAD_PATH . $file;
    if (file_exists($path)) {
        unlink($path);
        setFlash('success', "Image '$file' deleted.");
    }
    header('Location: ' . adminUrl('images')); exit;
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $uploaded = handleImageUpload('image', 'upload');
    if ($uploaded) {
        setFlash('success', 'Image uploaded: ' . $uploaded);
    } else {
        setFlash('error', 'Upload failed. Ensure file is a valid image under 5MB.');
    }
    header('Location: ' . adminUrl('images')); exit;
}

// Scan uploads folder
$images = [];
if (is_dir(UPLOAD_PATH)) {
    foreach (glob(UPLOAD_PATH . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) as $file) {
        $images[] = [
            'file' => basename($file),
            'path' => 'assets/images/uploads/' . basename($file),
            'size' => filesize($file),
            'time' => filemtime($file),
        ];
    }
    usort($images, fn($a,$b) => $b['time'] <=> $a['time']);
}

$pageTitle  = 'Image Upload';
$activePage = 'images';
include __DIR__ . '/includes/layout.php';
?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">

<!-- Upload Form -->
<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-cloud-upload-fill"></i> Upload Image</span></div>
    <form method="POST" enctype="multipart/form-data" id="uploadForm">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <div id="dropZone" style="border:2px dashed var(--border2);border-radius:10px;padding:30px;text-align:center;cursor:pointer;transition:border-color 0.2s;margin-bottom:16px"
             onclick="document.getElementById('fileInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--primary)'"
             ondragleave="this.style.borderColor='var(--border2)'"
             ondrop="handleDrop(event)">
            <i class="bi bi-cloud-arrow-up" style="font-size:2.5rem;color:var(--muted)"></i>
            <p style="color:var(--muted);font-size:0.85rem;margin-top:8px">Click or drag image here</p>
            <p style="color:var(--border2);font-size:0.75rem">JPG, PNG, GIF, WEBP · Max 5MB</p>
        </div>
        <input type="file" name="image" id="fileInput" accept="image/*" style="display:none" onchange="previewImage(this)">
        <img id="preview" style="display:none;width:100%;max-height:180px;object-fit:contain;border-radius:8px;margin-bottom:12px">
        <button type="submit" class="btn btn-primary" style="width:100%"><i class="bi bi-upload"></i> Upload</button>
    </form>
</div>

<!-- Gallery -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-images"></i> Uploaded Images (<?= count($images) ?>)</span>
    </div>
    <?php if (empty($images)): ?>
        <p style="color:var(--muted);text-align:center;padding:40px 0">No images uploaded yet.</p>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px">
        <?php foreach ($images as $img): ?>
        <div style="position:relative;border:1px solid var(--border2);border-radius:8px;overflow:hidden;background:var(--bg)">
            <img src="../<?= e($img['path']) ?>" style="width:100%;height:90px;object-fit:cover">
            <div style="padding:6px 8px">
                <div style="font-size:0.7rem;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($img['file']) ?></div>
                <div style="font-size:0.68rem;color:var(--border2)"><?= round($img['size']/1024) ?>KB</div>
            </div>
            <div style="display:flex;gap:4px;padding:0 6px 6px">
                <button onclick="copyPath('<?= e($img['path']) ?>')" class="btn btn-secondary btn-xs" style="flex:1" title="Copy path"><i class="bi bi-clipboard"></i></button>
                <form method="POST" action="<?= e(adminUrl('images')) ?>" style="flex:1" data-confirm="Delete this image?">
                    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="file" value="<?= e($img['file']) ?>">
                    <button type="submit" class="btn btn-danger btn-xs" style="width:100%" aria-label="Delete image"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('preview');
            prev.src = e.target.result;
            prev.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--border2)';
    const file = e.dataTransfer.files[0];
    if (file) {
        const inp = document.getElementById('fileInput');
        const dt = new DataTransfer();
        dt.items.add(file);
        inp.files = dt.files;
        previewImage(inp);
    }
}
function copyPath(path) {
    navigator.clipboard.writeText(path).then(() => alert('Copied: ' + path));
}
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
