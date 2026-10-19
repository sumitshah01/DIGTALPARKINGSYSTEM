<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
$pdo = (new Database())->connect();

$today = $pdo->query("SELECT * FROM environmental_statistics WHERE stat_date = CURDATE()")->fetch();
if (!$today) {
    $today = ['fuel_saved_liters'=>0,'co2_avoided_kg'=>0,'search_time_saved_hours'=>0,'vehicles_assisted'=>0,'distance_saved_km'=>0,'idling_reduced_minutes'=>0];
}

$weekly = $pdo->query("SELECT * FROM environmental_statistics 
    WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY stat_date")->fetchAll();

jsonResponse([
    'success' => true,
    'data' => $today,
    'weekly' => $weekly,
    'note' => 'Estimated values based on system data and configured assumptions.'
]);
?>