<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$settings = readJson('settings.json') ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $settings['comments_enabled']   = !empty($_POST['comments_enabled']);

    writeJson('settings.json', $settings);
    setFlash('success', 'Settings saved.');
    header('Location: ' . adminUrl('settings')); exit;
}

$pageTitle  = 'Settings';
$activePage = 'settings';
include __DIR__ . '/includes/layout.php';
?>

<div class="card" style="max-width:600px">
    <div class="card-header"><span class="card-title"><i class="bi bi-sliders"></i> Site Settings</span></div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <div style="display:grid;gap:12px">
            <?php $key = 'comments_enabled'; ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border2);border-radius:8px">
                <div>
                    <div style="font-size:0.85rem;font-weight:500;color:var(--text)">Blog comments</div>
                    <div style="font-size:0.75rem;color:var(--muted)">Allow visitors to comment on blog posts</div>
                </div>
                <label style="display:inline-flex;align-items:center;cursor:pointer">
                    <input type="checkbox" name="<?= $key ?>" value="1" <?= !empty($settings[$key]) ? 'checked' : '' ?>
                           style="width:0;height:0;opacity:0;position:absolute"
                           onchange="this.parentElement.querySelector('.toggle-track').style.background=this.checked?'var(--primary)':'var(--border2)'">
                    <span class="toggle-track" style="display:inline-block;width:38px;height:22px;background:<?= !empty($settings[$key]) ? 'var(--primary)' : 'var(--border2)' ?>;border-radius:11px;position:relative;transition:background 0.2s">
                        <span style="position:absolute;top:3px;left:<?= !empty($settings[$key]) ? '19px' : '3px' ?>;width:16px;height:16px;background:#fff;border-radius:50%;transition:left 0.2s"></span>
                    </span>
                </label>
            </div>
        </div>

        <div style="margin-top:20px">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
        </div>
    </form>
</div>

<script>
// Animate toggle thumb on change
document.querySelectorAll('input[type=checkbox]').forEach(cb => {
    cb.addEventListener('change', function() {
        const track = this.parentElement.querySelector('.toggle-track');
        const thumb = track.querySelector('span');
        track.style.background = this.checked ? 'var(--primary)' : 'var(--border2)';
        thumb.style.left = this.checked ? '19px' : '3px';
    });
});
</script>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
