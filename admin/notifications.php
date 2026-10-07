<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/../sendmail/emailHelper.php';

$user = readJson('admin_user.json') ?? [];
$to = trim((string)($user['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $sent = sendTestMail($to);
    setFlash(
        $sent ? 'success' : 'error',
        $sent ? 'SMTP test email sent to ' . $to : 'Email could not be sent. Check SMTP environment settings and the recipient email.'
    );
    header('Location: ' . adminUrl('notifications'));
    exit;
}

$pageTitle  = 'Email Test';
$activePage = 'notifications';
include __DIR__ . '/includes/layout.php';
?>

<div class="card" style="max-width:640px">
    <div class="card-header"><span class="card-title"><i class="bi bi-send-fill"></i> Test SMTP Setup</span></div>
    <p style="font-size:0.88rem;color:var(--muted);line-height:1.7;margin-bottom:18px">
        Send a test message to the admin email address to confirm the configured SMTP connection works.
    </p>
    <div class="form-group">
        <label class="form-label">Test recipient</label>
        <input type="email" class="form-control" value="<?= e($to) ?>" readonly>
    </div>
    <div style="padding:14px 16px;background:var(--bg);border:1px solid var(--border2);border-radius:8px;color:var(--muted);font-size:0.8rem;line-height:1.7;margin-bottom:18px">
        Configure <code>SMTP_USERNAME</code>, <code>SMTP_PASSWORD</code>, and optionally <code>SMTP_HOST</code>, <code>SMTP_PORT</code>, <code>SMTP_SECURE</code>, <code>SMTP_FROM_EMAIL</code>, and <code>CONTACT_EMAIL</code> in the server environment.
    </div>
    <form method="POST" action="<?= e(adminUrl('notifications')) ?>">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <button type="submit" class="btn btn-primary" <?= filter_var($to, FILTER_VALIDATE_EMAIL) ? '' : 'disabled' ?>><i class="bi bi-envelope-fill"></i> Send Test Email</button>
    </form>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
