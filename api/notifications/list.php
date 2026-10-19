<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$_SESSION['user_id']]);
jsonResponse(['success'=>true, 'data'=>$stmt->fetchAll()]);