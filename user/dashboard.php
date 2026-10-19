<?php
$pageTitle = "Dashboard - SmartPark";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$pdo = (new Database())->connect();
$uid = $_SESSION['user_id'];

$vehicles = $pdo->prepare("SELECT * FROM vehicles WHERE user_id=?"); $vehicles->execute([$uid]); $vehicles = $vehicles->fetchAll();
$reservations = $pdo->prepare("SELECT r.*, p.name AS parking_name, s.slot_number FROM reservations r
    JOIN parking_locations p ON p.id=r.parking_id JOIN parking_slots s ON s.id=r.slot_id
    WHERE r.user_id=? AND r.status IN ('confirmed','active') ORDER BY r.entry_time DESC LIMIT 5");
$reservations->execute([$uid]); $reservations = $reservations->fetchAll();
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]); $notifs = $notifs->fetchAll();
?>
<div class="container py-4 dashboard-page">
    <div class="d-flex justify-content-between mb-4 dashboard-welcome dashboard-welcome-panel">
        <div>
            <h3 class="fw-bold">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h3>
            <p class="text-muted">Here's your parking overview</p>
        </div>
        <a href="find-parking.php" class="btn btn-primary align-self-center dashboard-find-btn"><i class="bi bi-plus-circle"></i> Find Parking</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 dashboard-stat dashboard-stat--reservations">
                <div class="d-flex justify-content-between"><small class="text-muted">Active Reservations</small><i class="bi bi-bookmark-check text-primary fs-5 dashboard-stat-icon"></i></div>
                <h3 class="fw-bold mb-0"><?= count($reservations) ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 dashboard-stat dashboard-stat--vehicles">
                <div class="d-flex justify-content-between"><small class="text-muted">My Vehicles</small><i class="bi bi-car-front text-success fs-5 dashboard-stat-icon"></i></div>
                <h3 class="fw-bold mb-0"><?= count($vehicles) ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 dashboard-stat dashboard-stat--notifications">
                <div class="d-flex justify-content-between"><small class="text-muted">Notifications</small><i class="bi bi-bell text-warning fs-5 dashboard-stat-icon"></i></div>
                <h3 class="fw-bold mb-0"><?= count($notifs) ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 dashboard-stat dashboard-stat--wallet">
                <div class="d-flex justify-content-between"><small class="text-muted">Wallet</small><i class="bi bi-wallet2 text-info fs-5 dashboard-stat-icon"></i></div>
                <h3 class="fw-bold mb-0">₹<?= number_format($_SESSION['wallet'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm dashboard-panel">
                <div class="card-header bg-white dashboard-panel-header"><h5 class="fw-bold mb-0">Active Reservations</h5></div>
                <div class="card-body p-0">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p>No active reservations</p>
                            <a href="find-parking.php" class="btn btn-primary btn-sm">Book Parking</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light"><tr><th>Booking Code</th><th>Parking</th><th>Slot</th><th>Entry</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td><code><?= $r['booking_code'] ?></code></td>
                                        <td><?= htmlspecialchars($r['parking_name']) ?></td>
                                        <td><?= $r['slot_number'] ?></td>
                                        <td><?= date('d M, H:i', strtotime($r['entry_time'])) ?></td>
                                        <td><span class="badge bg-<?= $r['status']=='active'?'success':'primary' ?>"><?= ucfirst($r['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 dashboard-panel">
                <div class="card-header bg-white d-flex justify-content-between align-items-center dashboard-panel-header">
                    <h5 class="fw-bold mb-0">Notifications</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($notifs)): ?>
                        <div class="p-3 text-muted small">No new notifications</div>
                    <?php else: foreach ($notifs as $n): ?>
                        <div class="list-group-item dashboard-list-item">
                            <div class="fw-bold small"><?= htmlspecialchars($n['title']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($n['message']) ?></div>
                            <small class="text-muted"><?= date('d M, H:i', strtotime($n['created_at'])) ?></small>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm dashboard-panel">
                <div class="card-header bg-white d-flex justify-content-between align-items-center dashboard-panel-header">
                    <h5 class="fw-bold mb-0">My Vehicles</h5>
                    <a href="vehicles.php" class="small">Manage</a>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($vehicles)): ?>
                        <div class="p-3 text-muted small">No vehicles added. <a href="vehicles.php">Add one</a></div>
                    <?php else: foreach ($vehicles as $v): ?>
                        <div class="list-group-item d-flex justify-content-between dashboard-list-item">
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($v['vehicle_number']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($v['model']) ?> · <?= $v['vehicle_type'] ?></small>
                            </div>
                            <?php if ($v['is_default']): ?><span class="badge bg-primary align-self-center">Default</span><?php endif; ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>