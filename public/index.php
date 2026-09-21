<?php

declare(strict_types=1);

require_once __DIR__.'/../src/init.php';

$patientCount = count(Database::allPatients($pdo));
$recentActivity = $logger->recent(5);

require __DIR__.'/../partials/layout-top.php';
?>
<h1>CHIS Simulation</h1>
<p class="muted">
    A standalone stand-in for the Comprehensive Health Information System (CHIS),
    used to demonstrate that CLSU Telemedicine can exchange data with a real,
    separately running external system — not just call itself.
</p>

<div class="stats">
    <div class="stat-card">
        <span class="stat-value"><?= $patientCount ?></span>
        <span class="stat-label">Patients on file</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= count($recentActivity) ?></span>
        <span class="stat-label">Recent API calls</span>
    </div>
</div>

<h2>Patients</h2>
<table>
    <thead>
        <tr><th>CLSU ID</th><th>Name</th><th>Status</th><th>Blood</th><th>Allergies</th><th>Conditions</th></tr>
    </thead>
    <tbody>
    <?php foreach (Database::allPatients($pdo) as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['clsu_id']) ?></td>
            <td><?= htmlspecialchars($p['full_name']) ?></td>
            <td><span class="badge badge-ok"><?= htmlspecialchars($p['eligibility_status']) ?></span></td>
            <td><?= htmlspecialchars($p['blood_type'] ?? '—') ?></td>
            <td><?= htmlspecialchars(implode(', ', json_decode($p['known_allergies'] ?? '[]', true)) ?: 'None') ?></td>
            <td><?= htmlspecialchars(implode(', ', json_decode($p['chronic_conditions'] ?? '[]', true)) ?: 'None') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2 style="margin-top:28px">Recent API activity</h2>
<table>
    <thead>
        <tr><th>Time</th><th>Direction</th><th>Request</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php foreach ($recentActivity as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['created_at']) ?></td>
            <td><span class="badge badge-<?= htmlspecialchars($a['direction']) ?>"><?= htmlspecialchars($a['direction']) ?></span></td>
            <td><code><?= htmlspecialchars($a['method'].' '.$a['path']) ?></code></td>
            <td class="<?= ($a['status_code'] ?? 500) < 400 ? 'status-ok' : 'status-error' ?>"><?= (int) $a['status_code'] ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (! $recentActivity): ?>
        <tr><td colspan="4" class="muted">No API calls yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__.'/../partials/layout-bottom.php'; ?>
