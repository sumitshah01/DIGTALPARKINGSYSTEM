<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$data = json_decode(file_get_contents('php://input'), true);
$code = $data['booking_code'] ?? '';

$stmt = $pdo->prepare("SELECT r.*, p.name AS parking_name, s.slot_number 
    FROM reservations r 
    JOIN parking_locations p ON p.id = r.parking_id
    JOIN parking_slots s ON s.id = r.slot_id
    WHERE r.booking_code = ? AND r.status = 'confirmed'");
$stmt->execute([$code]);
$res = $stmt->fetch();

if (!$res) jsonResponse(['success' => false, 'message' => 'Invalid or expired booking code'], 404);

$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE reservations SET status='active', actual_entry_time=NOW() WHERE id=?")
        ->execute([$res['id']]);
    $pdo->prepare("UPDATE parking_slots SET status='occupied' WHERE id=?")
        ->execute([$res['slot_id']]);
    $pdo->prepare("INSERT INTO parking_sessions (reservation_id, user_id, parking_id, slot_id, entry_time, status)
        VALUES (?, ?, ?, ?, NOW(), 'active')")
        ->execute([$res['id'], $res['user_id'], $res['parking_id'], $res['slot_id']]);

    auditLog($pdo, $res['user_id'], 'check_in', 'reservations', $res['id']);
    $pdo->commit();

    jsonResponse(['success' => true, 'message' => 'Checked in successfully', 'slot' => $res['slot_number']]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>