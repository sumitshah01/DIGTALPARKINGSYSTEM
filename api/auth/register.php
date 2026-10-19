<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

$name = sanitize($d['name'] ?? '');
$email = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($d['phone'] ?? '');
$password = $d['password'] ?? '';

if (!$name || !$email || strlen($password) < 8) {
    jsonResponse(['success'=>false,'message'=>'Invalid input. Password must be 8+ chars.'], 400);
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email=?"); $stmt->execute([$email]);
if ($stmt->fetch()) jsonResponse(['success'=>false,'message'=>'Email already registered'], 409);

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, is_verified) VALUES (?, ?, ?, ?, 1)");
$stmt->execute([$name, $email, $phone, $hash]);

jsonResponse(['success'=>true, 'message'=>'Registration successful']);