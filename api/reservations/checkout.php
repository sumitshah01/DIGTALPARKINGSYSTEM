<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$data = json_decode(file_get_contents('php://input'), true);
$code = $data['booking_code'] ?? '';

$stmt = $pdo->prepare("SELECT r.*, p.price_per_hour, p.name AS parking_name 
    FROM reservations r JOIN parking_locations p ON p.id = r.parking_id
    WHERE r.booking_code = ? AND r.status = 'active'");
$stmt->execute([$code]);
$res = $stmt->fetch();
if (!$res) jsonResponse(['success' => false, 'message' => 'Active reservation not found'], 404);

$pdo->beginTransaction();
try {
    $entry = new DateTime($res['actual_entry_time']);
    $exit = new DateTime();
    $hours = max(1, ceil(($exit->getTimestamp() - $entry->getTimestamp()) / 3600));
    $amount = $hours * $res['price_per_hour'];
    $tax = round($amount * 0.18, 2);
    $total = $amount + $tax;

    $pdo->prepare("UPDATE reservations SET status='completed', actual_exit_time=NOW(), duration_hours=?, amount=?, tax=?, total_amount=? WHERE id=?")
        ->execute([$hours, $amount, $tax, $total, $res['id']]);
    $pdo->prepare("UPDATE parking_slots SET status='available' WHERE id=?")->execute([$res['slot_id']]);
    $pdo->prepare("UPDATE parking_sessions SET exit_time=NOW(), duration_minutes=?, amount=?, status='completed' WHERE reservation_id=?")
        ->execute([$hours*60, $total, $res['id']]);

    // Environmental impact contribution
    $impact = calculateEnvironmentalImpact(AVG_SEARCH_DISTANCE, AVG_SEARCH_TIME);
    $pdo->prepare("INSERT INTO environmental_statistics 
        (stat_date, fuel_saved_liters, co2_avoided_kg, search_time_saved_hours, vehicles_assisted, distance_saved_km)
        VALUES (CURDATE(), ?, ?, ?, 1, ?)
        ON DUPLICATE KEY UPDATE
        fuel_saved_liters = fuel_saved_liters + VALUES(fuel_saved_liters),
        co2_avoided_kg = co2_avoided_kg + VALUES(co2_avoided_kg),
        search_time_saved_hours = search_time_saved_hours + VALUES(search_time_saved_hours),
        vehicles_assisted = vehicles_assisted + 1,
        distance_saved_km = distance_saved_km + VALUES(distance_saved_km)")
        ->execute([$impact['fuel_saved'], $impact['co2_avoided'], $impact['search_time_saved']/60, AVG_SEARCH_DISTANCE]);

    // Notify
    sendNotification($pdo, $res['user_id'], 'Parking Session Ended',
        "You exited {$res['parking_name']}. Amount: ₹$total", 'payment');

    auditLog($pdo, $res['user_id'], 'check_out', 'reservations', $res['id']);
    $pdo->commit();

    jsonResponse(['success' => true, 'summary' => [
        'duration_hours' => $hours, 'amount' => $amount, 'tax' => $tax, 'total' => $total
    ]]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>