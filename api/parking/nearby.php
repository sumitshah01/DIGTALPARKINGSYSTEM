<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$pdo = (new Database())->connect();

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : null;
$ev = isset($_GET['ev']) ? (int)$_GET['ev'] : 0;
$covered = isset($_GET['covered']) ? (int)$_GET['covered'] : 0;
$accessible = isset($_GET['accessible']) ? (int)$_GET['accessible'] : 0;
$open24 = isset($_GET['open24']) ? (int)$_GET['open24'] : 0;
$sort = $_GET['sort'] ?? 'nearest';

$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM parking_slots s WHERE s.parking_id=p.id AND s.status='available') AS available_slots,
        (SELECT COUNT(*) FROM parking_slots s WHERE s.parking_id=p.id AND s.status='occupied') AS occupied_slots,
        (SELECT COUNT(*) FROM parking_slots s WHERE s.parking_id=p.id AND s.status='reserved') AS reserved_slots
        FROM parking_locations p WHERE p.is_active = 1";
$params = [];

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.area LIKE ? OR p.address LIKE ? OR p.landmark LIKE ?)";
    array_push($params, $search, $search, $search, $search);
}
if ($ev) { $sql .= " AND p.has_ev_charging = 1"; }
if ($covered) { $sql .= " AND p.has_covered = 1"; }
if ($accessible) { $sql .= " AND p.has_accessible = 1"; }
if ($open24) { $sql .= " AND p.is_24_7 = 1"; }

// Sorting
switch ($sort) {
    case 'cheapest': $sql .= " ORDER BY p.price_per_hour ASC"; break;
    case 'availability': $sql .= " ORDER BY available_slots DESC"; break;
    case 'rating': $sql .= " ORDER BY p.rating DESC"; break;
    default: $sql .= " ORDER BY p.name ASC";
}

$sql .= " LIMIT " . (int)$limit;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Compute distance
foreach ($results as &$r) {
    if ($lat !== null && $lng !== null) {
        $r['distance'] = round(calculateDistance($lat, $lng, (float)$r['latitude'], (float)$r['longitude']), 2);
        $r['eta_minutes'] = round(($r['distance'] / 25) * 60); // assume 25km/h city speed
    }
}
if ($sort === 'nearest' && $lat !== null) {
    usort($results, fn($a, $b) => ($a['distance'] ?? 0) <=> ($b['distance'] ?? 0));
}

jsonResponse(['success' => true, 'data' => $results]);
?>