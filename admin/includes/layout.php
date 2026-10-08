<?php
/**
 * Admin Layout Partial
 * Usage: include at top of each admin page AFTER requireLogin()
 * $pageTitle and $activePage must be set before include
 */

$flash = getFlash();

$navItems = [
    ['href' => 'index',       'icon' => 'bi-speedometer2',  'label' => 'Dashboard'],
    ['href' => 'contacts',    'icon' => 'bi-envelope',       'label' => 'Contact Form'],
    ['href' => 'blogs',       'icon' => 'bi-journal-text',   'label' => 'Blog Management'],
    ['href' => 'visitors',    'icon' => 'bi-graph-up',       'label' => 'Visitor Analytics'],
    ['href' => 'search',      'icon' => 'bi-search',         'label' => 'Search'],
    ['href' => 'comments',    'icon' => 'bi-chat-dots',      'label' => 'Comments'],
    ['href' => 'account',     'icon' => 'bi-shield-lock',    'label' => 'Admin Auth'],
    ['href' => 'images',      'icon' => 'bi-images',         'label' => 'Image Upload'],
    ['href' => 'notifications','icon'=> 'bi-envelope-check','label' => 'Email Test'],
    ['divider' => true],
    ['href' => 'profile',     'icon' => 'bi-person-circle',  'label' => 'Profile / SEO'],
    ['href' => 'skills',      'icon' => 'bi-bar-chart-line', 'label' => 'Skills'],
    ['href' => 'services',    'icon' => 'bi-gear',           'label' => 'Services'],
    ['href' => 'projects',    'icon' => 'bi-columns-gap',    'label' => 'Projects'],
    ['divider' => true],
    ['href' => 'settings',    'icon' => 'bi-sliders',        'label' => 'Settings'],
    ['href' => 'seo',         'icon' => 'bi-tags',           'label' => 'SEO'],
    ['href' => 'api',         'icon' => 'bi-plug',           'label' => 'API'],
];

// Count unread contacts
$contacts = readJson('contacts.json') ?? [];
$unreadContacts = count(array_filter($contacts, fn($c) => empty($c['read'])));
$pendingComments = count(array_filter(readJson('comments.json') ?? [], fn($c) => empty($c['approved'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('techshyam-theme')||'dark'}catch(e){document.documentElement.dataset.theme='dark'}</script>
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Admin') ?> — TechShyam Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --sw: 260px;
            --topbar-h: 60px;
            --primary: #3b82f6;
            --primary-dark: #2563eb;

            --bg: #0f172a;
            --sidebar-bg: #020617;
            --card-bg: #1e293b;
            --border: #1e293b;
            --border2: #334155;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --hover-bg: #1e293b;

            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
        }
        :root[data-theme="light"] {
            color-scheme: light;
            --bg:#f3f6fb; --sidebar-bg:#fff; --card-bg:#fff; --border:#e3e9f2;
            --border2:#d5deeb; --text:#172033; --muted:#64748b; --hover-bg:#eef3fa;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        /* ── Sidebar ─────────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sw);
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border2);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .sidebar-brand {
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid var(--border2);
            flex-shrink: 0;
            gap: 10px;
        }
        .sidebar-brand .brand-icon {
            width: 34px; height: 34px;
            background: var(--primary);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff; flex-shrink: 0;
        }
        .sidebar-brand span {
            font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: -0.3px;
        }
        .sidebar-nav {
            flex: 1;
            padding: 10px 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--border2) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 4px; }
        .nav-divider {
            height: 1px;
            background: var(--border2);
            margin: 6px 16px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.83rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s;
            position: relative;
        }
        .nav-link i { font-size: 1rem; width: 18px; text-align: center; flex-shrink: 0; }
        .nav-link:hover { color: var(--text); background: var(--hover-bg); }
        .nav-link.active { color: var(--primary); background: rgba(59,130,246,0.1); border-left-color: var(--primary); font-weight: 600; }
        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
        }
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border2);
            flex-shrink: 0;
        }
        .sidebar-theme {
            display:flex; width:100%; align-items:center; gap:10px; margin-bottom:12px; padding:9px 10px;
            border:1px solid var(--border2); border-radius:8px; color:var(--text); background:var(--hover-bg);
            font:500 .78rem 'Poppins',sans-serif; text-align:left; cursor:pointer; transition:background .18s,border-color .18s;
        }
        .sidebar-theme:hover { border-color:var(--primary); }
        .sidebar-theme i { color:var(--warning); font-size:.95rem; }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-user .avatar {
            width: 32px; height: 32px;
            background: var(--primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; color: #fff; font-weight: 700; flex-shrink: 0;
        }
        .sidebar-user .user-info { flex: 1; overflow: hidden; }
        .sidebar-user .user-info strong {
            display: block;
            font-size: 0.8rem;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user .user-info span { font-size: 0.72rem; color: var(--muted); }
        .sidebar-user .logout-btn {
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--muted);
            font-size: 1.05rem;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.15s;
        }
        .sidebar-user .logout-btn:hover { color: var(--danger); }

        /* ── Topbar ──────────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sw);
            right: 0;
            height: var(--topbar-h);
            background: var(--sidebar-bg);
            border-bottom: 1px solid var(--border2);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            z-index: 100;
        }
        .topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        .topbar .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar .btn-icon {
            width: 36px; height: 36px;
            background: var(--hover-bg);
            border: 1px solid var(--border2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--muted);
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .topbar .btn-icon:hover { color: var(--text); border-color: var(--primary); }
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.4rem;
            cursor: pointer;
        }
        /* ── Main area ───────────────────────────────── */
        .main-wrap {
            margin-left: var(--sw);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .main-inner {
            padding: 28px;
        }

        /* ── Flash messages ──────────────────────────── */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        .flash.success { background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.3); color: #86efac; }
        .flash.error   { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #fca5a5; }
        .flash.warning { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); color: #fcd34d; }
        .flash.info    { background: rgba(6,182,212,0.1); border-color: rgba(6,182,212,0.3); color: #67e8f9; }

        /* ── Cards ───────────────────────────────────── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border2);
            border-radius: 12px;
            padding: 22px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border2);
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
        }
        /* ── Stat cards ──────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border2);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .stat-icon.blue  { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .stat-icon.green { background: rgba(34,197,94,0.15); color: #4ade80; }
        .stat-icon.yellow{ background: rgba(245,158,11,0.15); color: #fbbf24; }
        .stat-icon.red   { background: rgba(239,68,68,0.15); color: #f87171; }
        .stat-icon.purple{ background: rgba(168,85,247,0.15); color: #c084fc; }
        .stat-icon.cyan  { background: rgba(6,182,212,0.15); color: #22d3ee; }
        .stat-body h3 { font-size: 1.6rem; font-weight: 700; color: var(--text); line-height: 1; }
        .stat-body p  { font-size: 0.78rem; color: var(--muted); margin-top: 4px; }

        /* ── Table ───────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        thead th { padding: 10px 14px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border2); text-align: left; white-space: nowrap; }
        tbody td { padding: 12px 14px; border-bottom: 1px solid var(--border); color: var(--text); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }

        /* ── Buttons ─────────────────────────────────── */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.83rem; font-weight: 500; cursor: pointer; text-decoration: none; border: none; transition: all 0.15s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-danger  { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-success { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
        .btn-success:hover { background: rgba(34,197,94,0.25); }
        .btn-secondary { background: var(--hover-bg); color: var(--muted); border: 1px solid var(--border2); }
        .btn-secondary:hover { color: var(--text); }
        .btn-sm { padding: 5px 10px; font-size: 0.78rem; border-radius: 6px; }
        .btn-xs { padding: 3px 8px; font-size: 0.72rem; border-radius: 5px; }

        /* ── Form elements ───────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--muted); margin-bottom: 6px; }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg);
            border: 1.5px solid var(--border2);
            border-radius: 8px;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
        textarea.form-control { resize: vertical; min-height: 90px; }
        select.form-control { appearance: none; }
        .form-check { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .form-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--primary); }

        /* ── Badge ───────────────────────────────────── */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
        .badge-success { background: rgba(34,197,94,0.15); color: #4ade80; }
        .badge-danger  { background: rgba(239,68,68,0.15); color: #f87171; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .badge-info    { background: rgba(6,182,212,0.15); color: #22d3ee; }
        .badge-muted   { background: rgba(148,163,184,0.1); color: var(--muted); }

        /* ── Modal ───────────────────────────────────── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: var(--card-bg); border: 1px solid var(--border2); border-radius: 14px; padding: 28px; width: 90%; max-width: 540px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .modal-title { font-size: 1rem; font-weight: 600; color: var(--text); }
        .modal-close { background: none; border: none; color: var(--muted); font-size: 1.3rem; cursor: pointer; }
        .modal-close:hover { color: var(--danger); }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border2); }

        /* ── Overlay ─────────────────────────────────── */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 150; }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .topbar { left: 0; }
            .main-wrap { margin-left: 0; }
            .hamburger { display: block; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .stat-grid { grid-template-columns: 1fr; }
            .main-inner { padding: 16px; }
            .contact-info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-shield-fill-check"></i></div>
        <span>TechShyam</span>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($navItems as $item): ?>
            <?php if (!empty($item['divider'])): ?>
                <div class="nav-divider"></div>
            <?php else: ?>
                <a href="<?= e(adminUrl($item['href'])) ?>"
                   class="nav-link <?= ($activePage ?? '') === $item['href'] ? 'active' : '' ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <?= e($item['label']) ?>
                    <?php if ($item['href'] === 'contacts' && $unreadContacts > 0): ?>
                        <span class="nav-badge"><?= $unreadContacts ?></span>
                    <?php endif; ?>
                    <?php if ($item['href'] === 'comments' && $pendingComments > 0): ?>
                        <span class="nav-badge"><?= $pendingComments ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <button type="button" class="sidebar-theme" data-theme-toggle aria-label="Switch to bright mode" aria-pressed="false"><i class="bi bi-sun-fill" aria-hidden="true"></i><span>Bright mode</span></button>
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
            <div class="user-info">
                <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                <span>Administrator</span>
            </div>
            <form method="POST" action="<?= e(adminUrl('logout')) ?>">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
                <button type="submit" class="logout-btn" title="Logout" aria-label="Logout"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
</aside>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Topbar -->
<div class="topbar">
    <button class="hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>
    <div class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
    <div class="topbar-actions">
        <a href="<?= e(appBasePath() . '/') ?>" target="_blank" class="btn-icon" title="View Site">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <a href="<?= e(adminUrl('settings')) ?>" class="btn-icon" title="Settings">
            <i class="bi bi-gear"></i>
        </a>
        <form method="POST" action="<?= e(adminUrl('logout')) ?>">
            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
            <button type="submit" class="btn-icon" title="Logout" aria-label="Logout"><i class="bi bi-power"></i></button>
        </form>
    </div>
</div>

<!-- Main -->
<div class="main-wrap">
    <div class="main-inner">
        <?php if ($flash): ?>
        <div class="flash <?= e($flash['type']) ?>">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : ($flash['type'] === 'error' ? 'bi-x-circle-fill' : 'bi-info-circle-fill') ?>"></i>
            <?= e($flash['msg']) ?>
        </div>
        <?php endif; ?>
