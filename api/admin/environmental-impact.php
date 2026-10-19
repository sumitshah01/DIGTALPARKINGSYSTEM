<?php
$pageTitle = "Environmental Impact";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="container py-4">
    <h3 class="fw-bold mb-1">🌍 Environmental Impact Dashboard</h3>
    <p class="text-muted small">Estimated values based on system data and configured assumptions.</p>

    <div class="row g-3 my-3" id="impactCards">
        <div class="col-md-3"><div class="card border-0 shadow-sm p-4 text-center"><h2 class="text-success mb-0" id="fuelVal">0</h2><small class="text-muted">Liters Fuel Saved</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-4 text-center"><h2 class="text-info mb-0" id="co2Val">0</h2><small class="text-muted">kg CO₂ Avoided</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-4 text-center"><h2 class="text-warning mb-0" id="timeVal">0</h2><small class="text-muted">Hours Search Time Saved</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm p-4 text-center"><h2 class="text-primary mb-0" id="vehVal">0</h2><small class="text-muted">Vehicles Assisted</small></div></div>
    </div>

    <div class="card border-0 shadow-sm p-3">
        <h5 class="fw-bold">Weekly Environmental Impact</h5>
        <canvas id="impactChart" height="100"></canvas>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
(async () => {
    const d = await fetch(`${SITE_URL}/api/analytics/environmental.php`).then(r => r.json());
    const t = d.data;
    document.getElementById('fuelVal').textContent = Number(t.fuel_saved_liters).toLocaleString() + ' L';
    document.getElementById('co2Val').textContent = Number(t.co2_avoided_kg).toLocaleString() + ' kg';
    document.getElementById('timeVal').textContent = Number(t.search_time_saved_hours).toLocaleString() + ' hrs';
    document.getElementById('vehVal').textContent = Number(t.vehicles_assisted).toLocaleString();

    new Chart(document.getElementById('impactChart'), {
        type: 'line',
        data: {
            labels: d.weekly.map(w => w.stat_date),
            datasets: [
                { label: 'Fuel (L)', data: d.weekly.map(w => w.fuel_saved_liters), borderColor: '#06d6a0', tension: 0.4 },
                { label: 'CO₂ (kg)', data: d.weekly.map(w => w.co2_avoided_kg), borderColor: '#4cc9f0', tension: 0.4 }
            ]
        }
    });
})();
</script>