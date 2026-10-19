<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();
$lat = (float)($_GET['lat'] ?? 0);
$lng = (float)($_GET['lng'] ?? 0);

$stmt = $pdo->query("SELECT p.*, 
    (SELECT COUNT(*) FROM parking_slots s WHERE s.parking_id=p.id AND s.status='available') AS available_slots,
    (SELECT COUNT(*) FROM parking_slots s WHERE s.parking_id=p.id) AS total_slots
    FROM parking_locations p WHERE p.is_active=1");
$list = $stmt->fetchAll();

$scored = [];
foreach ($list as $p) {
    $distance = calculateDistance($lat, $lng, (float)$p['latitude'], (float)$p['longitude']);
    $availRatio = $p['total_slots'] ? $p['available_slots'] / $p['total_slots'] : 0;

    // Smart score: weight distance, availability, price, rating
    $score = 0;
    $score += (1 / max($distance, 0.1)) * 40;         // closer = better
    $score += $availRatio * 30;                        // more availability
    $score += ($p['rating'] / 5) * 20;                 // rating
    $score += (1 / max($p['price_per_hour'], 1)) * 10; // cheaper

    $p['distance'] = round($distance, 2);
    $p['eta_minutes'] = round(($distance / 25) * 60);
    $p['score'] = round($score, 2);
    $scored[] = $p;
}

usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
$top = array_slice($scored, 0, 3);

// Generate human-readable reasons
foreach ($top as &$t) {
    $t['reason'] = "{$t['name']} is {$t['distance']} km away and has {$t['available_slots']} available spaces.";
}

jsonResponse(['success' => true, 'recommendations' => $top]);
?>