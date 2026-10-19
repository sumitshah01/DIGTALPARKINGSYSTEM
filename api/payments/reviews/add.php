<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pdo = (new Database())->connect();
$d = json_decode(file_get_contents('php://input'), true) ?? [];
if (!verifyCsrf($d['csrf_token'] ?? '')) jsonResponse(['success'=>false,'message'=>'Invalid CSRF'], 403);

$parkingId = filter_var($d['parking_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$rating = filter_var($d['rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$comment = trim((string)($d['comment'] ?? ''));
if (!$parkingId || $rating === false || $comment === '' || strlen($comment) > 8000) {
    jsonResponse(['success'=>false,'message'=>'Provide a valid parking location, rating, and comment up to 2000 characters'], 400);
}
$subRatings = [];
foreach (['cleanliness_rating', 'security_rating', 'accessibility_rating'] as $field) {
    $score = filter_var($d[$field] ?? 5, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    if ($score === false) {
        jsonResponse(['success'=>false,'message'=>'Review scores must be between 1 and 5'], 400);
    }
    $subRatings[] = $score;
}

$parking = $pdo->prepare('SELECT id FROM parking_locations WHERE id = ? AND is_active = 1');
$parking->execute([$parkingId]);
if (!$parking->fetchColumn()) {
    jsonResponse(['success'=>false,'message'=>'Parking location not found'], 404);
}

$stmt = $pdo->prepare("INSERT INTO reviews (user_id, parking_id, rating, cleanliness_rating, security_rating, accessibility_rating, comment) VALUES (?,?,?,?,?,?,?)");
$stmt->execute([
    $_SESSION['user_id'], $parkingId, $rating,
    ...$subRatings, $comment
]);

// Update average
$avg = $pdo->prepare("SELECT AVG(rating) as a, COUNT(*) as c FROM reviews WHERE parking_id=?");
$avg->execute([$parkingId]);
$row = $avg->fetch();
$pdo->prepare("UPDATE parking_locations SET rating=?, total_reviews=? WHERE id=?")
    ->execute([round($row['a'],2), $row['c'], $parkingId]);

jsonResponse(['success'=>true]);