// api/payments/verify.php  (mock verification)
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);

$pdo->prepare("UPDATE payments SET status='success' WHERE id=? AND user_id=?")
    ->execute([(int)$d['payment_id'], $_SESSION['user_id']]);

sendNotification($pdo, $_SESSION['user_id'], 'Payment Successful',
    'Your payment of ₹'.$d['amount'].' was successful.', 'payment');
jsonResponse(['success'=>true]);