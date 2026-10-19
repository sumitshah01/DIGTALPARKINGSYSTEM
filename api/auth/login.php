<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

$email = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $d['password'] ?? '';
if (!$email || !$password) jsonResponse(['success'=>false,'message'=>'Missing credentials'], 400);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['success'=>false,'message'=>'Invalid email or password'], 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['wallet'] = $user['wallet_balance'];

auditLog($pdo, $user['id'], 'login');
jsonResponse(['success'=>true, 'role'=>$user['role']]);