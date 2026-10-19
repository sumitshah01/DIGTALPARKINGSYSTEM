<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$pdo = (new Database())->connect();

$stats = [
    'total_locations' => $pdo->query("SELECT COUNT(*) FROM parking_locations")->fetchColumn(),
    'total_slots' => $pdo->query("SELECT COUNT(*) FROM parking_slots")->fetchColumn(),
    'available_slots' => $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='available'")->fetchColumn(),
    'occupied_slots' => $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='occupied'")->fetchColumn(),
    'reserved_slots' => $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='reserved'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'active_vehicles' => $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn(),
    'active_reservations' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN ('confirmed','active')")->fetchColumn(),
    'revenue_total' => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM payments WHERE status='success'")->fetchColumn(),
    'revenue_today' => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM payments WHERE status='success' AND DATE(created_at)=CURDATE()")->fetchColumn(),
];

$stats['occupancy_rate'] = $stats['total_slots'] > 0 
    ? round(($stats['occupied_slots'] / $stats['total_slots']) * 100, 2) : 0;

$impact = $pdo->query("SELECT * FROM environmental_statistics WHERE stat_date=CURDATE()")->fetch() ?: [];
$stats['fuel_saved'] = $impact['fuel_saved_liters'] ?? 0;
$stats['co2_avoided'] = $impact['co2_avoided_kg'] ?? 0;

jsonResponse(['success' => true, 'data' => $stats]);
?>