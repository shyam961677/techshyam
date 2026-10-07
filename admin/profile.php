<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$profile = readJson('profile.json') ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $uploaded = handleImageUpload('photo', 'profile');
    $uploaded2 = handleImageUpload('photo2', 'profile2');

    $profile['name']    = trim($_POST['name'] ?? '');
    $profile['title']   = trim($_POST['title'] ?? '');
    $profile['tagline'] = trim($_POST['tagline'] ?? '');
    $profile['bio']     = trim($_POST['bio'] ?? '');
    $profile['email']   = trim($_POST['email'] ?? '');
    $profile['phone1']  = trim($_POST['phone1'] ?? '');
    $profile['phone2']  = trim($_POST['phone2'] ?? '');
    $profile['address'] = trim($_POST['address'] ?? '');
    $profile['resume']  = trim($_POST['resume'] ?? '');
    $profile['social']  = [
        'facebook'  => trim($_POST['facebook'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? ''),
        'twitter'   => trim($_POST['twitter'] ?? ''),
        'linkedin'  => trim($_POST['linkedin'] ?? ''),
    ];
    if ($uploaded)  $profile['photo']  = $uploaded;
    if ($uploaded2) $profile['photo2'] = $uploaded2;

    writeJson('profile.json', $profile);
    setFlash('success', 'Profile saved.');
    header('Location: ' . adminUrl('profile')); exit;
}

$pageTitle  = 'Profile';
$activePage = 'profile';
include __DIR__ . '/includes/layout.php';
?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

<div>
<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title"><i class="bi bi-person-circle"></i> Personal Info</span></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($profile['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Job Title</label>
            <input type="text" name="title" class="form-control" value="<?= e($profile['title'] ?? '') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Tagline</label>
            <input type="text" name="tagline" class="form-control" value="<?= e($profile['tagline'] ?? '') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="5"><?= e($profile['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Phone 1</label>
            <input type="text" name="phone1" class="form-control" value="<?= e($profile['phone1'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Phone 2</label>
            <input type="text" name="phone2" class="form-control" value="<?= e($profile['phone2'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Resume URL</label>
            <input type="text" name="resume" class="form-control" value="<?= e($profile['resume'] ?? '') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?= e($profile['address'] ?? '') ?></textarea>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title"><i class="bi bi-share-fill"></i> Social Links</span></div>
    <?php foreach (['facebook'=>'bi-facebook','instagram'=>'bi-instagram','twitter'=>'bi-twitter','linkedin'=>'bi-linkedin'] as $key=>$icon): ?>
    <div class="form-group">
        <label class="form-label"><i class="bi <?= $icon ?>"></i> <?= ucfirst($key) ?></label>
        <input type="url" name="<?= $key ?>" class="form-control" value="<?= e($profile['social'][$key] ?? '') ?>" placeholder="https://…">
    </div>
    <?php endforeach; ?>
</div>
</div>

<!-- Right column: photos -->
<div>
<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title"><i class="bi bi-person-bounding-box"></i> Profile Photo</span></div>
    <?php if (!empty($profile['photo'])): ?>
    <img src="../<?= e($profile['photo']) ?>" style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;margin-bottom:12px">
    <?php endif; ?>
    <input type="file" name="photo" class="form-control" accept="image/*">
    <div style="font-size:0.75rem;color:var(--muted);margin-top:6px">Max 5MB · jpg, png, webp</div>
</div>
<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-image"></i> About Section Photo</span></div>
    <?php if (!empty($profile['photo2'])): ?>
    <img src="../<?= e($profile['photo2']) ?>" style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;margin-bottom:12px">
    <?php endif; ?>
    <input type="file" name="photo2" class="form-control" accept="image/*">
</div>
</div>

</div><!-- grid -->

<div style="margin-top:20px">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Profile</button>
</div>
</form>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
