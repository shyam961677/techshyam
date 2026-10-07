<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$profile = readJson('profile.json') ?? [];
$seo     = $profile['seo'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $profile['seo'] = [
        'meta_title'       => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
        'og_image'         => trim($_POST['og_image'] ?? ''),
    ];
    writeJson('profile.json', $profile);
    setFlash('success', 'SEO settings saved.');
    header('Location: ' . adminUrl('seo')); exit;
}

$pageTitle  = 'SEO';
$activePage = 'seo';
include __DIR__ . '/includes/layout.php';
?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-tags-fill"></i> SEO Meta Tags</span></div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="form-group">
            <label class="form-label">Meta Title <span style="color:var(--muted);font-size:0.75rem">(recommended: 50-60 chars)</span></label>
            <input type="text" name="meta_title" class="form-control" maxlength="70"
                   value="<?= e($seo['meta_title'] ?? '') ?>" oninput="updatePreview()" id="metaTitle">
            <div style="font-size:0.72rem;color:var(--muted);margin-top:4px"><span id="titleCount">0</span>/60 characters</div>
        </div>
        <div class="form-group">
            <label class="form-label">Meta Description <span style="color:var(--muted);font-size:0.75rem">(recommended: 150-160 chars)</span></label>
            <textarea name="meta_description" class="form-control" rows="3" maxlength="200"
                      oninput="updatePreview()" id="metaDesc"><?= e($seo['meta_description'] ?? '') ?></textarea>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:4px"><span id="descCount">0</span>/160 characters</div>
        </div>
        <div class="form-group">
            <label class="form-label">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="form-control"
                   value="<?= e($seo['meta_keywords'] ?? '') ?>" placeholder="php, developer, portfolio, shyam yadav">
        </div>
        <div class="form-group">
            <label class="form-label">OG Image URL (Social share image)</label>
            <input type="text" name="og_image" class="form-control"
                   value="<?= e($seo['og_image'] ?? '') ?>" placeholder="assets/images/shyam1.jpg">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save SEO</button>
    </form>
</div>

<!-- Google Preview -->
<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-google"></i> Search Preview</span></div>
    <div style="background:#fff;border-radius:8px;padding:14px;font-family:Arial,sans-serif">
        <div id="previewTitle" style="color:#1a0dab;font-size:1rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= e($seo['meta_title'] ?? 'Page Title') ?>
        </div>
        <div style="color:#006621;font-size:0.78rem;margin:2px 0">https://techshyam.com</div>
        <div id="previewDesc" style="color:#545454;font-size:0.82rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
            <?= e($seo['meta_description'] ?? 'Page description will appear here…') ?>
        </div>
    </div>

    <div class="card-header" style="margin-top:16px"><span class="card-title"><i class="bi bi-facebook"></i> Social Share Preview</span></div>
    <div style="background:#1877f2;border-radius:8px;overflow:hidden">
        <?php if (!empty($seo['og_image'])): ?>
        <img src="../<?= e($seo['og_image']) ?>" style="width:100%;height:100px;object-fit:cover">
        <?php else: ?>
        <div style="height:80px;background:#0d47a1;display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="color:#fff;font-size:1.5rem"></i></div>
        <?php endif; ?>
        <div style="padding:10px 12px;background:#fff">
            <div style="font-size:0.72rem;color:#606770;text-transform:uppercase">techshyam.com</div>
            <div style="font-size:0.85rem;font-weight:700;color:#1d2129"><?= e($seo['meta_title'] ?? '') ?></div>
            <div style="font-size:0.78rem;color:#606770"><?= e(substr($seo['meta_description'] ?? '',0,80)) ?>…</div>
        </div>
    </div>
</div>

</div>

<script>
const titleEl = document.getElementById('metaTitle');
const descEl  = document.getElementById('metaDesc');

function updatePreview() {
    document.getElementById('previewTitle').textContent = titleEl.value || 'Page Title';
    document.getElementById('previewDesc').textContent  = descEl.value  || 'Page description…';
    document.getElementById('titleCount').textContent   = titleEl.value.length;
    document.getElementById('descCount').textContent    = descEl.value.length;
}

// Init counts
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
