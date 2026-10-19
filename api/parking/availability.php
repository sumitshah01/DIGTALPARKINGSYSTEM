<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$parkingId = (int)($_GET['parking_id'] ?? 0);

$stmt = $pdo->prepare("SELECT slot_type, status, COUNT(*) as count 
    FROM parking_slots WHERE parking_id = ? GROUP BY slot_type, status");
$stmt->execute([$parkingId]);
$rows = $stmt->fetchAll();

$summary = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'maintenance' => 0];
$byType = [];

foreach ($rows as $r) {
    $summary[$r['status']] = ($summary[$r['status']] ?? 0) + $r['count'];
    $byType[$r['slot_type']][$r['status']] = $r['count'];
}

$slots = $pdo->prepare("SELECT id, slot_number, slot_type, status FROM parking_slots WHERE parking_id = ? ORDER BY slot_number");
$slots->execute([$parkingId]);

jsonResponse([
    'success' => true,
    'summary' => $summary,
    'by_type' => $byType,
    'slots' => $slots->fetchAll(),
    'timestamp' => date('c')
]);
?>