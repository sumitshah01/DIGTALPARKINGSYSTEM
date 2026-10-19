<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);
if (!empty($d['is_default'])) {
    $pdo->prepare("UPDATE vehicles SET is_default=0 WHERE user_id=?")->execute([$_SESSION['user_id']]);
    $pdo->prepare("UPDATE vehicles SET is_default=1 WHERE id=? AND user_id=?")->execute([(int)$d['id'], $_SESSION['user_id']]);
}
jsonResponse(['success'=>true]);