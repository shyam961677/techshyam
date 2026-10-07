<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user  = readJson('admin_user.json') ?? [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['_action'] ?? '';

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new1    = $_POST['new_password'] ?? '';
        $new2    = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'] ?? '')) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new1) < 12 || strlen($new1) > 128) {
            $error = 'New password must be between 12 and 128 characters.';
        } elseif ($new1 !== $new2) {
            $error = 'Passwords do not match.';
        } else {
            $user['password'] = password_hash($new1, PASSWORD_BCRYPT, ['cost' => 12]);
            if (writeJson('admin_user.json', $user)) {
                setFlash('success', 'Password changed successfully.');
                header('Location: ' . adminUrl('account')); exit;
            }
            $error = 'Could not save the new password. Check data folder permissions.';
        }
    }

    if ($action === 'update_profile' && empty($error)) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        if ($name === '' || strlen($name) > 100) {
            $error = 'Display name is required and must be under 100 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
            $error = 'Username must be 3–64 characters and use only letters, numbers, dots, underscores, or hyphens.';
        } else {
            $user['name'] = $name;
            $user['email'] = $email;
            $user['username'] = $username;
            if (writeJson('admin_user.json', $user)) {
                $_SESSION['admin_name']  = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                setFlash('success', 'Account updated.');
                header('Location: ' . adminUrl('account')); exit;
            }
            $error = 'Could not save account changes. Check data folder permissions.';
        }
    }
}

$pageTitle  = 'Admin Authentication';
$activePage = 'account';
include __DIR__ . '/includes/layout.php';
?>

<?php if ($error): ?>
<div class="flash error" role="alert" style="margin-bottom:16px"><i class="bi bi-exclamation-circle-fill"></i> <?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Account Info -->
<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-person-fill"></i> Account Details</span></div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="_action" value="update_profile">
        <div class="form-group">
            <label class="form-label">Display Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= e($user['username'] ?? '') ?>" required autocomplete="username">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Last Login</label>
            <input type="text" class="form-control" value="<?= e($user['last_login'] ?? 'Never') ?>" readonly style="opacity:0.5">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
    </form>
</div>

<!-- Change Password -->
<div class="card">
    <div class="card-header"><span class="card-title"><i class="bi bi-lock-fill"></i> Change Password</span></div>
    <form method="POST">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="_action" value="change_password">
        <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> Change Password</button>
    </form>
</div>

</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
