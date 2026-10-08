<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../rate_limit.php';

// Already logged in
if (isLoggedIn()) {
    header('Location: ' . adminUrl('index'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rateLimit('admin_login', 10, 300);
    verifyCsrfToken();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (!adminLogin($username, $password)) {
        $error = 'Invalid username or password.';
    } else {
        header('Location: ' . adminUrl('index'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('techshyam-theme')||'dark'}catch(e){document.documentElement.dataset.theme='dark'}</script>
    <title>Admin Login — TechShyam</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { color-scheme: light; font-family: 'Poppins', sans-serif; color: #192338; background: #f2f5fa; }
        *, *::before, *::after { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; background: #fff; }
        .login-shell { width: 100%; min-height: 100vh; display: grid; grid-template-columns: minmax(360px, .9fr) minmax(0, 1.1fr); overflow: hidden; background: #fff; }
        .brand-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: clamp(36px, 5vw, 76px); color: #fff; background: linear-gradient(150deg, #101e36 0%, #173c69 58%, #246bb1 100%); }
        .brand-panel::before, .brand-panel::after { position: absolute; content: ''; border: 1px solid rgba(255,255,255,.13); border-radius: 50%; pointer-events: none; }
        .brand-panel::before { width: 470px; height: 470px; right: -210px; top: 15%; box-shadow: 0 0 0 42px rgba(255,255,255,.035), 0 0 0 84px rgba(255,255,255,.025); }
        .brand-panel::after { width: 280px; height: 280px; right: -135px; top: 28%; background: rgba(255,255,255,.035); }
        .brand, .brand-copy, .brand-footer { position: relative; z-index: 1; }
        .brand { display: inline-flex; align-items: center; gap: 12px; font-size: 1.05rem; font-weight: 600; letter-spacing: .01em; }
        .brand-mark { display: grid; width: 42px; height: 42px; place-items: center; border: 1px solid rgba(255,255,255,.25); border-radius: 13px; background: rgba(255,255,255,.12); font-size: 1.15rem; }
        .brand-copy { max-width: 440px; margin: auto 0; padding: 52px 0; }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: #b8d6ff; font-size: .72rem; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; }
        .eyebrow::before { width: 22px; height: 1px; content: ''; background: #8abaff; }
        .brand-copy h1 { margin: 0; font-size: clamp(2.5rem, 4vw, 4rem); line-height: 1.08; letter-spacing: -.055em; }
        .brand-copy p { max-width: 310px; margin: 20px 0 0; color: #c3d2e6; font-size: .92rem; line-height: 1.8; }
        .brand-footer { color: #b3c5dc; font-size: .72rem; }
        .form-panel { display: flex; flex-direction: column; justify-content: center; padding: 64px clamp(32px, 6vw, 96px); background: linear-gradient(145deg, #fff 35%, #f7f9fc); }
        .form-panel > * { width: 100%; max-width: 420px; align-self: center; }
        .form-heading { margin-bottom: 34px; }
        .form-heading .secure-label { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 18px; color: #3471c7; font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .form-heading h2 { margin: 0; color: #1a263b; font-size: 1.85rem; letter-spacing: -.045em; }
        .form-heading p { margin: 9px 0 0; color: #7b879a; font-size: .86rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #344158; font-size: .77rem; font-weight: 600; }
        .input-wrap { position: relative; }
        .input-wrap > i { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: #98a5b8; font-size: 1rem; pointer-events: none; }
        .form-group input { width: 100%; height: 52px; padding: 0 46px 0 44px; border: 1px solid #dce3ed; border-radius: 11px; outline: none; color: #1d2a40; background: #fbfcfe; font: inherit; font-size: .84rem; transition: border-color .18s, box-shadow .18s, background .18s; }
        .form-group input::placeholder { color: #aab4c2; }
        .form-group input:focus { border-color: #4384d8; background: #fff; box-shadow: 0 0 0 4px rgba(67,132,216,.12); }
        .toggle-pass { position: absolute; top: 50%; right: 13px; display: grid; width: 30px; height: 30px; transform: translateY(-50%); place-items: center; border: 0; border-radius: 7px; color: #7d8aa0; background: transparent; cursor: pointer; }
        .toggle-pass:hover { color: #2866b0; background: #eef4fc; }
        .btn-login { display: flex; width: 100%; height: 52px; align-items: center; justify-content: center; gap: 10px; margin-top: 8px; border: 0; border-radius: 11px; color: #fff; background: linear-gradient(100deg, #2868b5, #347fd0); box-shadow: 0 9px 18px rgba(44,111,190,.2); font: inherit; font-size: .86rem; font-weight: 600; cursor: pointer; transition: transform .18s, box-shadow .18s, filter .18s; }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 12px 22px rgba(44,111,190,.27); filter: brightness(1.04); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:focus-visible, .toggle-pass:focus-visible { outline: 3px solid rgba(52,127,208,.35); outline-offset: 3px; }
        .alert-error { display: flex; align-items: center; gap: 9px; margin-bottom: 20px; padding: 12px 14px; border: 1px solid #f4caca; border-radius: 10px; color: #a83c3c; background: #fff5f4; font-size: .78rem; }
        .form-note { display: flex; align-items: center; gap: 8px; margin-top: 24px; color: #8995a7; font-size: .71rem; }
        .form-note i { color: #53a17e; }
        .login-theme { position:fixed; z-index:5; top:18px; right:20px; display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border:1px solid #dce3ed; border-radius:999px; color:#26344a; background:#fff; font:600 .78rem 'Poppins',sans-serif; cursor:pointer; box-shadow:0 8px 24px rgba(20,35,60,.12); }
        :root[data-theme="dark"] { color-scheme:dark; }
        :root[data-theme="dark"] body, :root[data-theme="dark"] .login-shell { background:#0b1120; }
        :root[data-theme="dark"] .form-panel { background:linear-gradient(145deg,#111a2c 35%,#0b1120); }
        :root[data-theme="dark"] .form-heading h2 { color:#f1f5f9; }
        :root[data-theme="dark"] .form-heading p, :root[data-theme="dark"] .form-group label { color:#a3b1c5; }
        :root[data-theme="dark"] .form-group input { color:#f1f5f9; background:#172235; border-color:#334155; }
        :root[data-theme="dark"] .form-group input:focus { background:#1b2a40; }
        :root[data-theme="dark"] .login-theme { color:#f1f5f9; background:#172235; border-color:#334155; }
        @media (max-width: 700px) {
            .login-shell { min-height: 100vh; grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .brand-panel { min-height: 155px; padding: 24px 28px; }
            .brand-copy { display: none; }
            .brand-footer { margin-top: 25px; font-size: .68rem; }
            .brand-panel::before { width: 190px; height: 190px; top: -68px; right: -38px; }
            .brand-panel::after { width: 130px; height: 130px; top: -38px; right: -8px; }
            .form-panel { padding: 38px 28px 34px; }
            .form-heading { margin-bottom: 27px; }
            .form-heading h2 { font-size: 1.65rem; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <button type="button" class="login-theme" data-theme-toggle aria-label="Switch to bright mode" aria-pressed="false"><i class="bi bi-sun-fill" aria-hidden="true"></i><span>Bright mode</span></button>
    <main class="login-shell">
        <section class="brand-panel" aria-label="TechShyam administration">
            <div class="brand"><span class="brand-mark"><i class="bi bi-layers-fill" aria-hidden="true"></i></span> TechShyam</div>
            <div class="brand-copy">
                <span class="eyebrow">Admin workspace</span>
                <h1>Your work,<br>all in one place.</h1>
                <p>Manage your portfolio, publish updates, and keep everything running smoothly.</p>
            </div>
            <div class="brand-footer">© <?= date('Y') ?> TechShyam</div>
        </section>
        <section class="form-panel">
            <div class="form-heading">
                <span class="secure-label"><i class="bi bi-shield-check" aria-hidden="true"></i> Secure sign in</span>
                <h2>Welcome back</h2>
                <p>Enter your admin credentials to continue.</p>
            </div>
            <?php if ($error): ?>
            <div class="alert-error" role="alert">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <?= e($error) ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <input type="text" id="username" name="username"
                               value="<?= e($_POST['username'] ?? '') ?>"
                               placeholder="Your username" autocomplete="username" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Your password" autocomplete="current-password" required>
                        <button type="button" class="toggle-pass" onclick="togglePass()" aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye" id="eyeIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    Sign in to dashboard <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
            <div class="form-note"><i class="bi bi-lock-fill" aria-hidden="true"></i> Admin access is restricted to authorized users.</div>
        </section>
    </main>
    <script>
        (function() {
            const root = document.documentElement;
            const toggles = document.querySelectorAll('[data-theme-toggle]');
            function applyTheme(theme) {
                const light = theme === 'light';
                root.dataset.theme = light ? 'light' : 'dark';
                toggles.forEach(button => {
                    button.setAttribute('aria-pressed', String(light));
                    button.setAttribute('aria-label', light ? 'Switch to dark mode' : 'Switch to bright mode');
                    button.innerHTML = `<i class="bi ${light ? 'bi-moon-stars-fill' : 'bi-sun-fill'}" aria-hidden="true"></i><span>${light ? 'Dark mode' : 'Bright mode'}</span>`;
                });
            }
            let savedTheme = 'dark';
            try { savedTheme = localStorage.getItem('techshyam-theme') || 'dark'; } catch (error) {}
            applyTheme(savedTheme);
            toggles.forEach(button => button.addEventListener('click', () => {
                const nextTheme = root.dataset.theme === 'light' ? 'dark' : 'light';
                applyTheme(nextTheme);
                try { localStorage.setItem('techshyam-theme', nextTheme); } catch (error) {}
            }));
        })();
        function togglePass() {
            const inp = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            if (inp.type === 'password') {
                inp.type = 'text';
                eye.className = 'bi bi-eye-slash';
                document.querySelector('.toggle-pass').setAttribute('aria-label', 'Hide password');
                document.querySelector('.toggle-pass').setAttribute('aria-pressed', 'true');
            } else {
                inp.type = 'password';
                eye.className = 'bi bi-eye';
                document.querySelector('.toggle-pass').setAttribute('aria-label', 'Show password');
                document.querySelector('.toggle-pass').setAttribute('aria-pressed', 'false');
            }
        }
    </script>
</body>
</html>
