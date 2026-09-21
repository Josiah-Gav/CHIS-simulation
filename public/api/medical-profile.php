<?php

declare(strict_types=1);

require_once __DIR__.'/../../src/init.php';

header('Content-Type: application/json');

$clsuId = $_GET['clsu_id'] ?? '';
$path = "/api/patients/{$clsuId}/medical-profile";
$remote = $_SERVER['REMOTE_ADDR'] ?? null;

if (! Auth::isAuthorized($config)) {
    http_response_code(401);
    $logger->log('inbound', 'GET', $path, 401, 'missing or invalid API key', $remote);
    echo json_encode(['message' => 'Unauthorized.']);

    return;
}

$patient = Database::findPatient($pdo, $clsuId);

if (! $patient) {
    http_response_code(404);
    $logger->log('inbound', 'GET', $path, 404, 'no matching CHIS medical profile', $remote);
    echo json_encode(['message' => 'No matching CHIS medical profile.']);

    return;
}

$logger->log('inbound', 'GET', $path, 200, 'medical profile returned', $remote);
echo json_encode(Database::toMedicalProfile($patient));
