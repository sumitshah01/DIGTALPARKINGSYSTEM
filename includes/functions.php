<?php
// includes/functions.php
require_once __DIR__ . '/csrf.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateBookingCode() {
    return 'SP' . strtoupper(bin2hex(random_bytes(5)));
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
    return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function calculateEnvironmentalImpact($distanceKm, $searchTimeMin = null) {
    $fuelSaved = $distanceKm * FUEL_PER_KM;
    $co2Avoided = $fuelSaved * CO2_PER_LITER;
    return [
        'fuel_saved' => round($fuelSaved, 3),
        'co2_avoided' => round($co2Avoided, 3),
        'search_time_saved' => $searchTimeMin ?? ($distanceKm * 5)
    ];
}

function sendNotification($pdo, $userId, $title, $message, $type = 'system', $link = null) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $title, $message, $type, $link]);
}

function auditLog($pdo, $userId, $action, $table = null, $recordId = null, $old = null, $new = null) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId, $action, $table, $recordId,
        $old ? json_encode($old) : null,
        $new ? json_encode($new) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateQRCode($data) {
    // Requires a QR library; simple placeholder
    return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);
}
?>