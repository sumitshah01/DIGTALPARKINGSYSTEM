<?php
$pageTitle = "Admin Dashboard";
$extraCss = ['admin-dashboard.css'];
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main class="container-fluid admin-dashboard py-4 px-3 px-lg-4">
    <div class="admin-dashboard-heading">
        <div>
            <p class="admin-eyebrow mb-1">SmartPark Operations</p>
            <h1 class="admin-page-title mb-0"><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
        </div>
        <span class="admin-view-label"><i class="bi bi-grid-1x2"></i> Overview</span>
    </div>

    <div class="row g-3 mb-4 admin-stat-grid" id="statsRow">
        <?php
        $cards = [
            ['total_locations','Parking Locations','bi-geo-alt','primary'],
            ['total_slots','Total Slots','bi-grid-3x3','secondary'],
            ['available_slots','Available','bi-check-circle','success'],
            ['occupied_slots','Occupied','bi-x-circle','danger'],
            ['reserved_slots','Reserved','bi-bookmark','warning'],
            ['total_users','Users','bi-people','info'],
            ['active_vehicles','Vehicles','bi-car-front','dark'],
            ['active_reservations','Active Bookings','bi-calendar-check','primary'],
        ];
        foreach ($cards as $c): ?>
            <div class="col-6 col-md-3 col-lg-3">
                <button type="button" class="admin-stat-link" data-metric="<?= $c[0] ?>" aria-label="View <?= $c[1] ?> details">
                    <div class="card admin-stat-card border-0 shadow-sm p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="admin-stat-label"><?= $c[1] ?></small>
                                <h2 class="admin-stat-value mb-0" data-stat="<?= $c[0] ?>">0</h2>
                            </div>
                            <span class="admin-stat-icon text-<?= $c[3] ?>"><i class="bi <?= $c[2] ?>"></i></span>
                        </div>
                    </div>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-6"><button type="button" class="admin-stat-link" data-metric="revenue_today" aria-label="View today's payment details"><div class="card admin-summary-card admin-revenue-card border-0 p-3 h-100"><small class="admin-stat-label">Revenue Today</small><h2 class="admin-summary-value text-success mb-0">₹<span data-stat="revenue_today">0</span></h2></div></button></div>
        <div class="col-6 col-md-6"><button type="button" class="admin-stat-link" data-metric="occupancy_rate" aria-label="View occupancy by location"><div class="card admin-summary-card admin-occupancy-card border-0 p-3 h-100"><small class="admin-stat-label">Occupancy Rate</small><h2 class="admin-summary-value text-primary mb-0"><span data-stat="occupancy_rate">0</span>%</h2></div></button></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card admin-chart-card border-0 p-3 p-lg-4 h-100">
                <h2 class="admin-chart-title">Parking Usage <span>Last 7 Days</span></h2>
                <div class="admin-chart-canvas"><canvas id="usageChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card admin-chart-card border-0 p-3 p-lg-4 h-100">
                <h2 class="admin-chart-title">Vehicle Types</h2>
                <div class="admin-chart-canvas admin-chart-canvas-doughnut"><canvas id="vehicleChart"></canvas></div>
            </div>
        </div>
        <div class="col-12">
            <div class="card admin-chart-card border-0 p-3 p-lg-4">
                <h2 class="admin-chart-title">Peak Parking Hours</h2>
                <div class="admin-chart-canvas admin-chart-canvas-peak"><canvas id="peakChart"></canvas></div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="metricDetailModal" tabindex="-1" aria-labelledby="metricDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="metricDetailTitle">Metric Details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="metricDetailStatus" class="text-muted mb-3" role="status" aria-live="polite"></p>
                <div id="metricDetailTableWrap" class="table-responsive"></div>
            </div>
        </div>
    </div>
</div>
<?php
$extraJs = ['admin.js?v=2'];
require_once __DIR__ . '/../../includes/footer.php';
?>