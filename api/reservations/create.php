<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$pdo = (new Database())->connect();
$data = json_decode(file_get_contents('php://input'), true);

if (!verifyCsrf($data['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

$parkingId = (int)($data['parking_id'] ?? 0);
$slotId = (int)($data['slot_id'] ?? 0);
$vehicleId = (int)($data['vehicle_id'] ?? 0);
$entryTime = $data['entry_time'] ?? null;
$exitTime = $data['exit_time'] ?? null;

if (!$parkingId || !$slotId || !$vehicleId || !$entryTime || !$exitTime) {
    jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
}

$start = DateTime::createFromFormat('!Y-m-d\\TH:i', $entryTime);
$end = DateTime::createFromFormat('!Y-m-d\\TH:i', $exitTime);
if (!$start || !$end || $start->format('Y-m-d\\TH:i') !== $entryTime || $end->format('Y-m-d\\TH:i') !== $exitTime) {
    jsonResponse(['success' => false, 'message' => 'Invalid reservation time'], 400);
}
if ($end <= $start) {
    jsonResponse(['success' => false, 'message' => 'Exit time must be after entry time'], 400);
}

$vehicle = $pdo->prepare('SELECT id FROM vehicles WHERE id = ? AND user_id = ? AND is_active = 1');
$vehicle->execute([$vehicleId, $_SESSION['user_id']]);
if (!$vehicle->fetchColumn()) {
    jsonResponse(['success' => false, 'message' => 'Selected vehicle is not available to this account'], 403);
}

$entryTime = $start->format('Y-m-d H:i:s');
$exitTime = $end->format('Y-m-d H:i:s');

$pdo->beginTransaction();
try {
    // Lock slot
    $slot = $pdo->prepare("SELECT * FROM parking_slots WHERE id = ? AND parking_id = ? FOR UPDATE");
    $slot->execute([$slotId, $parkingId]);
    $slotRow = $slot->fetch();
    if (!$slotRow || $slotRow['status'] !== 'available') {
        throw new Exception('Slot not available');
    }

    // Compute fee
    $parking = $pdo->prepare("SELECT * FROM parking_locations WHERE id = ?");
    $parking->execute([$parkingId]);
    $p = $parking->fetch();

    $hours = max(1, ceil(($end->getTimestamp() - $start->getTimestamp()) / 3600));
    $amount = $hours * $p['price_per_hour'];
    $tax = round($amount * 0.18, 2);
    $total = $amount + $tax;

    $bookingCode = generateBookingCode();
    $qr = generateQRCode($bookingCode);

    $ins = $pdo->prepare("INSERT INTO reservations 
        (user_id, parking_id, slot_id, vehicle_id, booking_code, qr_code, entry_time, expected_exit_time, duration_hours, amount, tax, total_amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
    $ins->execute([
        $_SESSION['user_id'], $parkingId, $slotId, $vehicleId,
        $bookingCode, $qr, $entryTime, $exitTime, $hours, $amount, $tax, $total
    ]);

    $pdo->prepare("UPDATE parking_slots SET status='reserved' WHERE id=?")->execute([$slotId]);

    $resId = $pdo->lastInsertId();
    sendNotification($pdo, $_SESSION['user_id'], 'Reservation Confirmed',
        "Your booking $bookingCode is confirmed at {$p['name']}", 'reservation',
        "/user/dashboard.php");

    auditLog($pdo, $_SESSION['user_id'], 'create_reservation', 'reservations', $resId);

    $pdo->commit();
    jsonResponse([
        'success' => true,
        'reservation' => [
            'id' => $resId, 'booking_code' => $bookingCode, 'qr_code' => $qr,
            'amount' => $amount, 'tax' => $tax, 'total' => $total,
            'duration_hours' => $hours
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
?>