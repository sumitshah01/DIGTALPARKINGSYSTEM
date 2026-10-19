<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

$stmt = $pdo->prepare("INSERT INTO vehicles (user_id, vehicle_number, vehicle_type, model, color) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], sanitize($d['vehicle_number']), $d['vehicle_type'], sanitize($d['model'] ?? ''), sanitize($d['color'] ?? '')]);
jsonResponse(['success'=>true, 'id'=>$pdo->lastInsertId()]);