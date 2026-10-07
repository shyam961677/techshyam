<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$data = readJson('visitors.json') ?? ['total'=>0,'today'=>0,'this_week'=>0,'this_month'=>0,'logs'=>[]];

// Clear logs action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'clear') {
    verifyCsrfToken();
    $data['logs'] = [];
    writeJson('visitors.json', $data);
    setFlash('success', 'Visitor logs cleared.');
    header('Location: ' . adminUrl('visitors'));
    exit;
}

$pageTitle  = 'Visitor Analytics';
$activePage = 'visitors';
include __DIR__ . '/includes/layout.php';

// Device breakdown
$devices = ['Desktop'=>0,'Mobile'=>0,'Tablet'=>0];
foreach ($data['logs'] as $v) {
    $d = $v['device'] ?? 'Desktop';
    if (isset($devices[$d])) $devices[$d]++;
}
?>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
    <?php
    $stats = [
        ['Total Visitors','total','bi-people-fill','blue'],
        ['Today','today','bi-calendar-day','green'],
        ['This Week','this_week','bi-calendar-week','yellow'],
        ['This Month','this_month','bi-calendar-month','purple'],
    ];
    foreach ($stats as [$label,$key,$icon,$color]):
    ?>
    <div class="stat-card">
        <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
        <div class="stat-body">
            <h3><?= number_format($data[$key] ?? 0) ?></h3>
            <p><?= $label ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">
    <!-- Device breakdown -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="bi bi-pie-chart-fill" style="color:var(--primary)"></i> Device Breakdown</span>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;padding:8px 0">
            <?php foreach ($devices as $device => $count): ?>
            <div style="text-align:center;min-width:100px">
                <div style="font-size:2rem;font-weight:700;color:var(--text)"><?= $count ?></div>
                <div style="font-size:0.8rem;color:var(--muted)"><?= $device ?></div>
                <?php $total = array_sum($devices); $pct = $total > 0 ? round($count/$total*100) : 0; ?>
                <div style="height:4px;background:var(--border2);border-radius:2px;margin-top:8px">
                    <div style="height:4px;background:var(--primary);border-radius:2px;width:<?= $pct ?>%"></div>
                </div>
                <div style="font-size:0.72rem;color:var(--muted);margin-top:4px"><?= $pct ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Last updated -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="bi bi-clock" style="color:var(--info)"></i> Last Updated</span>
        </div>
        <div style="font-size:1.1rem;font-weight:600;color:var(--text)"><?= e($data['last_updated'] ?? 'Never') ?></div>
        <div style="color:var(--muted);font-size:0.8rem;margin-top:6px">Tracking <?= count($data['logs']) ?> log entries</div>
        <div style="margin-top:16px">
            <form method="POST" action="<?= e(adminUrl('visitors')) ?>" data-confirm="Clear all visitor logs?">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="clear">
                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Clear Logs</button>
            </form>
        </div>
    </div>
</div>

<!-- Visitor Logs -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-list-ul"></i> Visitor Logs</span>
    </div>
    <?php if (empty($data['logs'])): ?>
        <p style="color:var(--muted);text-align:center;padding:40px 0">No visitor logs yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>IP</th><th>Page</th><th>Device</th><th>Browser</th><th>Date/Time</th></tr></thead>
            <tbody>
            <?php foreach ($data['logs'] as $i => $v): ?>
            <tr>
                <td style="color:var(--muted);font-size:0.78rem"><?= $i + 1 ?></td>
                <td style="font-size:0.8rem"><?= e($v['ip'] ?? '—') ?></td>
                <td style="font-size:0.8rem;color:var(--muted);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($v['page'] ?? '/') ?></td>
                <td><span class="badge badge-info"><?= e($v['device'] ?? 'Desktop') ?></span></td>
                <td style="font-size:0.75rem;color:var(--muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e(substr($v['agent'] ?? '', 0, 50)) ?></td>
                <td style="color:var(--muted);font-size:0.78rem"><?= e($v['date'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
