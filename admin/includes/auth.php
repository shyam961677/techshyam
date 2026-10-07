<?php
/**
 * Admin Authentication Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    $isSecureRequest = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecureRequest,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function appBasePath(): string
{
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $adminPosition = strpos($scriptPath, '/admin/');
    return $adminPosition === false ? '' : substr($scriptPath, 0, $adminPosition);
}

function adminUrl(string $page = ''): string
{
    return appBasePath() . '/admin' . ($page === '' ? '' : '/' . ltrim($page, '/'));
}

function csrfToken(): string
{
    if (empty($_SESSION['_admin_csrf'])) {
        $_SESSION['_admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_admin_csrf'];
}

function verifyCsrfToken(): void
{
    $submitted = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrfToken(), $submitted)) {
        http_response_code(403);
        exit('Request could not be verified. Reload the page and try again.');
    }
}

define('DATA_PATH', __DIR__ . '/../../data/');
define('UPLOAD_PATH', __DIR__ . '/../../assets/images/uploads/');
define('UPLOAD_URL', '../assets/images/uploads/');

// ── Helper: read / write JSON files ─────────────────────────────────────────

function readJson(string $file): mixed
{
    $path = DATA_PATH . $file;
    if (!file_exists($path)) return null;
    $content = file_get_contents($path);
    return json_decode($content, true);
}

function writeJson(string $file, mixed $data): bool
{
    $path = DATA_PATH . $file;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

// ── Next auto-increment ID ───────────────────────────────────────────────────

function nextId(array $items): int
{
    if (empty($items)) return 1;
    return max(array_column($items, 'id')) + 1;
}

// ── Auth helpers ─────────────────────────────────────────────────────────────

function isLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . adminUrl('login'));
        exit;
    }
}

function adminLogin(string $username, string $password): bool
{
    $user = readJson('admin_user.json');
    if (!$user) return false;

    if ($user['username'] === $username && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['_admin_csrf'] = bin2hex(random_bytes(32));
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name']      = $user['name'];
        $_SESSION['admin_email']     = $user['email'];

        // Update last login
        $user['last_login'] = date('Y-m-d H:i:s');
        writeJson('admin_user.json', $user);

        return true;
    }
    return false;
}

function adminLogout(): void
{
    $_SESSION = [];
    session_destroy();
    header('Location: ' . adminUrl('login'));
    exit;
}

// ── Visitor Tracking ─────────────────────────────────────────────────────────

function trackVisitor(): void
{
    // Only track once per session
    if (!empty($_SESSION['visit_tracked'])) return;
    $_SESSION['visit_tracked'] = true;

    $data = readJson('visitors.json') ?? [
        'total' => 0, 'today' => 0, 'this_week' => 0,
        'this_month' => 0, 'last_updated' => '', 'logs' => []
    ];

    $today = date('Y-m-d');

    // Reset daily counter if new day
    if ($data['last_updated'] !== $today) {
        $data['today'] = 0;
        $data['last_updated'] = $today;
    }

    // Reset weekly counter if new week (Monday)
    if (date('N') == 1 && ($data['last_week_reset'] ?? '') !== date('Y-W')) {
        $data['this_week'] = 0;
        $data['last_week_reset'] = date('Y-W');
    }

    // Reset monthly counter if new month
    if (($data['last_month_reset'] ?? '') !== date('Y-m')) {
        $data['this_month'] = 0;
        $data['last_month_reset'] = date('Y-m');
    }

    $data['total']++;
    $data['today']++;
    $data['this_week']++;
    $data['this_month']++;

    // Keep last 100 log entries
    $log = [
        'ip'      => anonymizeIp($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        'page'    => $_SERVER['REQUEST_URI'] ?? '/',
        'agent'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 80),
        'date'    => date('Y-m-d H:i:s'),
        'device'  => detectDevice($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ];
    array_unshift($data['logs'], $log);
    $data['logs'] = array_slice($data['logs'], 0, 100);

    writeJson('visitors.json', $data);
}

function anonymizeIp(string $ip): string
{
    // Mask last octet for privacy
    $parts = explode('.', $ip);
    if (count($parts) === 4) {
        $parts[3] = 'xxx';
        return implode('.', $parts);
    }
    return $ip;
}

function detectDevice(string $ua): string
{
    $ua = strtolower($ua);
    if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) return 'Mobile';
    if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'Tablet';
    return 'Desktop';
}

// ── Flash messages ───────────────────────────────────────────────────────────

function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Safe output ──────────────────────────────────────────────────────────────

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Image upload helper ──────────────────────────────────────────────────────

function handleImageUpload(string $field, string $prefix = 'img'): ?string
{
    if (empty($_FILES[$field]['tmp_name'])) return null;

    $file   = $_FILES[$field];
    $ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed, true)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;  // 5 MB limit

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $validMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $validMimes, true)) return null;

    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest     = UPLOAD_PATH . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/images/uploads/' . $filename;
    }
    return null;
}
