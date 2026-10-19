<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$pdo = (new Database())->connect();
$metric = $_GET['metric'] ?? '';

$details = [
    'total_locations' => [
        'title' => 'Parking Locations',
        'columns' => ['ID', 'Name', 'Area', 'City', 'Slots', 'Price / hour'],
        'sql' => 'SELECT id, name, area, city, total_slots, price_per_hour FROM parking_locations ORDER BY name LIMIT 100'
    ],
    'total_slots' => [
        'title' => 'All Parking Slots',
        'columns' => ['Parking', 'Slot', 'Type', 'Status', 'Floor'],
        'sql' => "SELECT p.name, s.slot_number, s.slot_type, s.status, s.floor FROM parking_slots s JOIN parking_locations p ON p.id=s.parking_id ORDER BY p.name, s.slot_number LIMIT 100"
    ],
    'available_slots' => [
        'title' => 'Available Parking Slots',
        'columns' => ['Parking', 'Slot', 'Type', 'Floor'],
        'sql' => "SELECT p.name, s.slot_number, s.slot_type, s.floor FROM parking_slots s JOIN parking_locations p ON p.id=s.parking_id WHERE s.status='available' ORDER BY p.name, s.slot_number LIMIT 100"
    ],
    'occupied_slots' => [
        'title' => 'Occupied Parking Slots',
        'columns' => ['Parking', 'Slot', 'Type', 'Floor'],
        'sql' => "SELECT p.name, s.slot_number, s.slot_type, s.floor FROM parking_slots s JOIN parking_locations p ON p.id=s.parking_id WHERE s.status='occupied' ORDER BY p.name, s.slot_number LIMIT 100"
    ],
    'reserved_slots' => [
        'title' => 'Reserved Parking Slots',
        'columns' => ['Parking', 'Slot', 'Type', 'Floor'],
        'sql' => "SELECT p.name, s.slot_number, s.slot_type, s.floor FROM parking_slots s JOIN parking_locations p ON p.id=s.parking_id WHERE s.status='reserved' ORDER BY p.name, s.slot_number LIMIT 100"
    ],
    'total_users' => [
        'title' => 'Registered Users',
        'columns' => ['Name', 'Email', 'Phone', 'Joined'],
        'sql' => "SELECT name, email, phone, created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 100"
    ],
    'active_vehicles' => [
        'title' => 'Registered Vehicles',
        'columns' => ['Vehicle', 'Type', 'Model', 'Owner'],
        'sql' => 'SELECT v.vehicle_number, v.vehicle_type, v.model, u.name FROM vehicles v JOIN users u ON u.id=v.user_id ORDER BY v.vehicle_number LIMIT 100'
    ],
    'active_reservations' => [
        'title' => 'Active Bookings',
        'columns' => ['Booking', 'User', 'Parking', 'Slot', 'Entry', 'Exit', 'Status'],
        'sql' => "SELECT r.booking_code, u.name, p.name, s.slot_number, r.entry_time, r.expected_exit_time, r.status FROM reservations r JOIN users u ON u.id=r.user_id JOIN parking_locations p ON p.id=r.parking_id JOIN parking_slots s ON s.id=r.slot_id WHERE r.status IN ('confirmed','active') ORDER BY r.entry_time LIMIT 100"
    ],
    'revenue_today' => [
        'title' => 'Successful Payments Today',
        'columns' => ['Receipt', 'Transaction', 'Method', 'Total', 'Paid'],
        'sql' => "SELECT receipt_number, transaction_id, method, total, created_at FROM payments WHERE status='success' AND DATE(created_at)=CURDATE() ORDER BY created_at DESC LIMIT 100"
    ],
    'occupancy_rate' => [
        'title' => 'Occupancy by Location',
        'columns' => ['Parking', 'Total', 'Available', 'Occupied', 'Reserved', 'Occupancy'],
        'sql' => "SELECT p.name, COUNT(s.id), SUM(s.status='available'), SUM(s.status='occupied'), SUM(s.status='reserved'), CONCAT(ROUND(SUM(s.status='occupied') / NULLIF(COUNT(s.id), 0) * 100, 2), '%') FROM parking_locations p LEFT JOIN parking_slots s ON s.parking_id=p.id GROUP BY p.id ORDER BY p.name LIMIT 100"
    ]
];

if (!isset($details[$metric])) {
    jsonResponse(['success' => false, 'message' => 'Unknown dashboard metric'], 400);
}

$definition = $details[$metric];
$rows = $pdo->query($definition['sql'])->fetchAll(PDO::FETCH_NUM);
jsonResponse([
    'success' => true,
    'title' => $definition['title'],
    'columns' => $definition['columns'],
    'rows' => $rows
]);