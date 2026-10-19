<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$pdo = (new Database())->connect();
$range = $_GET['range'] ?? '7';

$usage = $pdo->prepare("SELECT DATE(entry_time) as day, COUNT(*) as sessions, SUM(amount) as revenue
    FROM parking_sessions 
    WHERE entry_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    GROUP BY DATE(entry_time) ORDER BY day");
$usage->execute([(int)$range]);

$peak = $pdo->query("SELECT HOUR(entry_time) as hour, COUNT(*) as count 
    FROM parking_sessions GROUP BY HOUR(entry_time) ORDER BY hour")->fetchAll();

$vehicleTypes = $pdo->query("SELECT vehicle_type, COUNT(*) as count FROM vehicles GROUP BY vehicle_type")->fetchAll();

$topLocations = $pdo->query("SELECT p.name, COUNT(s.id) as sessions 
    FROM parking_locations p LEFT JOIN parking_sessions s ON s.parking_id=p.id
    GROUP BY p.id ORDER BY sessions DESC LIMIT 5")->fetchAll();

jsonResponse([
    'success' => true,
    'usage' => $usage->fetchAll(),
    'peak_hours' => $peak,
    'vehicle_types' => $vehicleTypes,
    'top_locations' => $topLocations
]);
?>