// api/payments/history.php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$stmt = $pdo->prepare("SELECT p.*, r.booking_code FROM payments p LEFT JOIN reservations r ON r.id=p.reservation_id WHERE p.user_id=? ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
jsonResponse(['success'=>true, 'data'=>$stmt->fetchAll()]);