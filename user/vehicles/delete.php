<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
if (!verifyCsrf($data['csrf_token'] ?? '')) {
	jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

$vehicleId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$vehicleId) {
	jsonResponse(['success' => false, 'message' => 'Invalid vehicle'], 400);
}

$pdo = (new Database())->connect();
$stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = ? AND user_id = ?');
$stmt->execute([$vehicleId, $_SESSION['user_id']]);

if (!$stmt->rowCount()) {
	jsonResponse(['success' => false, 'message' => 'Vehicle not found'], 404);
}

jsonResponse(['success' => true]);
