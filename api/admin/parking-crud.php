<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get') {
    $stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE id=?"); $stmt->execute([(int)$_GET['id']]);
    jsonResponse(['success'=>true, 'data'=>$stmt->fetch()]);
}

$d = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

if (($d['action'] ?? '') === 'delete') {
    $pdo->prepare("DELETE FROM parking_locations WHERE id=?")->execute([(int)$d['id']]);
    auditLog($pdo, $_SESSION['user_id'], 'delete_parking', 'parking_locations', $d['id']);
    jsonResponse(['success'=>true]);
}

// Add or edit
$fields = ['name','address','area','landmark','latitude','longitude','total_slots','price_per_hour','has_ev_charging','has_accessible','has_covered','is_24_7'];
$vals = [];
foreach ($fields as $f) $vals[$f] = $d[$f] ?? null;

if (!empty($d['id'])) {
    $set = implode(', ', array_map(fn($f) => "$f = :$f", $fields));
    $vals['id'] = (int)$d['id'];
    $sql = "UPDATE parking_locations SET $set WHERE id = :id";
    auditLog($pdo, $_SESSION['user_id'], 'update_parking', 'parking_locations', $d['id']);
} else {
    $cols = implode(', ', $fields);
    $ph = implode(', ', array_map(fn($f) => ":$f", $fields));
    $sql = "INSERT INTO parking_locations ($cols) VALUES ($ph)";
    auditLog($pdo, $_SESSION['user_id'], 'create_parking', 'parking_locations', null, null, $vals);
}
$stmt = $pdo->prepare($sql);
$stmt->execute($vals);
jsonResponse(['success'=>true, 'id'=>$pdo->lastInsertId()]);
?>