<?php

declare(strict_types=1);

require_once __DIR__.'/../src/bootstrap.php';

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

<div class="card">
    <h2>Status</h2>
    <p class="muted">Base scaffold running. Patient roster, the inbound API, and the activity log land in the next steps.</p>
</div>

<?php require __DIR__.'/../partials/layout-bottom.php'; ?>
