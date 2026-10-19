<?php
$pageTitle = "Real-Time Monitoring";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid py-4 px-4">
    <h3 class="fw-bold mb-4"><i class="bi bi-activity"></i> Real-Time Monitoring <span class="badge bg-success pulse">LIVE</span></h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Available Now</small><h3 class="text-success fw-bold mb-0" id="liveAvail">-</h3></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Occupied Now</small><h3 class="text-danger fw-bold mb-0" id="liveOcc">-</h3></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Reserved</small><h3 class="text-warning fw-bold mb-0" id="liveRes">-</h3></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Occupancy %</small><h3 class="text-primary fw-bold mb-0" id="livePct">-</h3></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="fw-bold mb-0">Active Sessions</h5></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light"><tr><th>Vehicle</th><th>Parking</th><th>Slot</th><th>Entry</th><th>Duration</th><th>Status</th></tr></thead>
                <tbody id="activeSessions"></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
async function refresh() {
    const r = await fetch(`${SITE_URL}/api/admin/monitoring.php`).then(r => r.json());
    if (!r.success) return;
    document.getElementById('liveAvail').textContent = r.data.available;
    document.getElementById('liveOcc').textContent = r.data.occupied;
    document.getElementById('liveRes').textContent = r.data.reserved;
    document.getElementById('livePct').textContent = r.data.occupancy_rate + '%';
    document.getElementById('activeSessions').innerHTML = r.data.sessions.map(s => `
        <tr><td>${s.vehicle_number || '—'}</td><td>${s.parking_name}</td><td>${s.slot_number}</td>
        <td>${new Date(s.entry_time).toLocaleString()}</td>
        <td>${Math.round((Date.now() - new Date(s.entry_time))/60000)} min</td>
        <td><span class="badge bg-success">Active</span></td></tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">No active sessions</td></tr>';
}
refresh();
setInterval(refresh, 5000);
</script>