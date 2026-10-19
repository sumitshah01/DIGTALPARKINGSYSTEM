<?php
$pageTitle = "Parking Details";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();

$pdo = (new Database())->connect();
$id = (int)($_GET['id'] ?? 0);

$p = $pdo->prepare("SELECT * FROM parking_locations WHERE id=?"); $p->execute([$id]); $parking = $p->fetch();
if (!$parking) { echo "<div class='container py-5'><h3>Parking not found</h3></div>"; require_once __DIR__.'/../includes/footer.php'; exit; }

$slots = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id=? ORDER BY slot_number"); $slots->execute([$id]); $slots = $slots->fetchAll();
$reviews = $pdo->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.parking_id=? AND r.is_approved=1 ORDER BY r.created_at DESC LIMIT 5");
$reviews->execute([$id]); $reviews = $reviews->fetchAll();

$summary = ['available'=>0,'occupied'=>0,'reserved'=>0];
foreach ($slots as $s) $summary[$s['status']] = ($summary[$s['status']] ?? 0) + 1;
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <img src="<?= $parking['images'] ? json_decode($parking['images'])[0] ?? '' : 'https://via.placeholder.com/800x300?text=Parking' ?>" class="card-img-top" style="height:250px;object-fit:cover;border-radius:12px 12px 0 0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 class="fw-bold mb-0"><?= htmlspecialchars($parking['name']) ?></h3>
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> <?= $parking['rating'] ?: 'N/A' ?></span>
                    </div>
                    <p class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($parking['address']) ?></p>
                    <div class="row text-center my-3">
                        <div class="col-3"><h4 class="fw-bold text-success mb-0"><?= $summary['available'] ?? 0 ?></h4><small class="text-muted">Available</small></div>
                        <div class="col-3"><h4 class="fw-bold text-danger mb-0"><?= $summary['occupied'] ?? 0 ?></h4><small class="text-muted">Occupied</small></div>
                        <div class="col-3"><h4 class="fw-bold text-warning mb-0"><?= $summary['reserved'] ?? 0 ?></h4><small class="text-muted">Reserved</small></div>
                        <div class="col-3"><h4 class="fw-bold text-primary mb-0"><?= $parking['total_slots'] ?></h4><small class="text-muted">Total</small></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($parking['has_ev_charging']): ?><span class="badge bg-info-subtle text-info"><i class="bi bi-ev-station"></i> EV Charging</span><?php endif; ?>
                        <?php if ($parking['has_accessible']): ?><span class="badge bg-primary-subtle text-primary">♿ Accessible</span><?php endif; ?>
                        <?php if ($parking['has_covered']): ?><span class="badge bg-secondary-subtle text-secondary">Covered</span><?php endif; ?>
                        <?php if ($parking['is_24_7']): ?><span class="badge bg-success-subtle text-success">24/7</span><?php endif; ?>
                    </div>
                    <p class="mt-3 mb-0"><strong class="fs-5 text-primary">₹<?= $parking['price_per_hour'] ?></strong> / hour</p>
                    <p class="text-muted small mb-0">Opening: <?= $parking['opening_time'] ?> — <?= $parking['closing_time'] ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="fw-bold mb-0">Live Slots <span class="badge bg-success pulse">LIVE</span></h5></div>
                <div class="card-body">
                    <div class="row g-2" id="slotGrid">
                        <?php foreach ($slots as $s):
                            $color = ['available'=>'success','occupied'=>'danger','reserved'=>'warning','maintenance'=>'secondary'][$s['status']];
                            $icon = ['EV'=>'⚡','accessible'=>'♿','2-wheeler'=>'🏍','4-wheeler'=>'🚗'][$s['slot_type']] ?? '🅿';
                        ?>
                            <div class="col-3 col-md-2">
                                <div class="border rounded p-2 text-center slot-<?= $s['status'] ?>" data-slot="<?= $s['id'] ?>">
                                    <div><?= $icon ?></div>
                                    <small class="fw-bold d-block"><?= $s['slot_number'] ?></small>
                                    <span class="badge bg-<?= $color ?>" style="font-size:0.6rem"><?= $s['status'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div id="detailsMap" class="mb-3" style="height:250px;border-radius:12px"></div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bi bi-bookmark-plus"></i> Reserve a Slot</h5></div>
                <div class="card-body">
                    <form id="reserveForm">
                        <?= csrfField() ?>
                        <input type="hidden" name="parking_id" value="<?= $parking['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Vehicle</label>
                            <select name="vehicle_id" class="form-select" id="vehicleSelect" required></select>
                            <a href="vehicles.php" class="small">+ Add new vehicle</a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Available Slot</label>
                            <select name="slot_id" class="form-select" required>
                                <?php foreach ($slots as $s): if ($s['status']==='available'): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['slot_number'] ?> (<?= $s['slot_type'] ?>)</option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-sm-6"><label class="form-label small fw-bold">Entry Time</label>
                                <input type="datetime-local" name="entry_time" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>"></div>
                            <div class="col-12 col-sm-6"><label class="form-label small fw-bold">Exit Time</label>
                                <input type="datetime-local" name="exit_time" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>"></div>
                        </div>
                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-check-circle"></i> Confirm Reservation</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white"><h5 class="fw-bold mb-0">Reviews</h5></div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted small">No reviews yet.</p>
                    <?php else: foreach ($reviews as $r): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong class="small"><?= htmlspecialchars($r['name']) ?></strong>
                                <span class="text-warning small"><?= str_repeat('★', $r['rating']) ?></span>
                            </div>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($r['comment']) ?></p>
                        </div>
                    <?php endforeach; endif; ?>
                    <form id="reviewForm" class="mt-3">
                        <?= csrfField() ?>
                        <input type="hidden" name="parking_id" value="<?= (int)$parking['id'] ?>">
                        <div class="mb-2">
                            <label class="form-label small fw-bold" for="reviewRating">Your rating</label>
                            <select id="reviewRating" name="rating" class="form-select" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Very poor</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold" for="reviewComment">Comment</label>
                            <textarea id="reviewComment" name="comment" class="form-control" rows="3" maxlength="2000" required></textarea>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" type="submit">Submit review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$extraJs = ['parking-details.js'];
?>
<script>
window.PARKING_LAT = <?= json_encode((float)$parking['latitude']) ?>;
window.PARKING_LNG = <?= json_encode((float)$parking['longitude']) ?>;
</script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>