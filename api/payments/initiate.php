// api/payments/initiate.php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

$resId = (int)$d['reservation_id'];
$method = $d['method'] ?? 'upi';

$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
$stmt->execute([$resId, $_SESSION['user_id']]);
$res = $stmt->fetch();
if (!$res) jsonResponse(['success'=>false,'message'=>'Reservation not found'], 404);

$txnId = 'TXN' . strtoupper(bin2hex(random_bytes(6)));
$stmt = $pdo->prepare("INSERT INTO payments (user_id, reservation_id, amount, tax, total, method, transaction_id, status) VALUES (?,?,?,?,?,?,?,'pending')");
$stmt->execute([$_SESSION['user_id'], $resId, $res['amount'], $res['tax'], $res['total_amount'], $method, $txnId]);

jsonResponse(['success'=>true, 'payment_id'=>$pdo->lastInsertId(), 'txn_id'=>$txnId]);