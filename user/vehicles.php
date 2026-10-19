<?php
$pageTitle = "My Vehicles";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
requireLogin();
$pdo = (new Database())->connect();
$vehicles = $pdo->prepare("SELECT * FROM vehicles WHERE user_id=?"); $vehicles->execute([$_SESSION['user_id']]); $vehicles = $vehicles->fetchAll();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">My Vehicles</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="bi bi-plus-circle"></i> Add Vehicle
        </button>
    </div>

    <div class="row g-3">
        <?php foreach ($vehicles as $v): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between">
                        <i class="bi bi-car-front-fill fs-2 text-primary"></i>
                        <?php if ($v['is_default']): ?><span class="badge bg-primary align-self-start">Default</span><?php endif; ?>
                    </div>
                    <h5 class="fw-bold mt-2 mb-1"><?= htmlspecialchars($v['vehicle_number']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($v['model']) ?> · <?= $v['vehicle_type'] ?></p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteVehicle(<?= $v['id'] ?>)"><i class="bi bi-trash"></i></button>
                        <?php if (!$v['is_default']): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="setDefault(<?= $v['id'] ?>)">Set Default</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($vehicles)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1"></i><p>No vehicles yet. Add your first vehicle.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addVehicleForm">
                <div class="modal-header"><h5 class="modal-title">Add Vehicle</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <?= csrfField() ?>
                    <div class="mb-3"><label class="form-label">Vehicle Number</label>
                        <input name="vehicle_number" class="form-control" required placeholder="KA01AB1234"></div>
                    <div class="mb-3"><label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select" required>
                            <option value="4-wheeler">4-Wheeler</option>
                            <option value="2-wheeler">2-Wheeler</option>
                            <option value="EV">Electric Vehicle</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Model</label>
                        <input name="model" class="form-control" placeholder="Honda City"></div>
                    <div class="mb-3"><label class="form-label">Color</label>
                        <input name="color" class="form-control" placeholder="White"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
document.getElementById('addVehicleForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(e.target).entries());
    try {
        await apiCall(`${SITE_URL}/user/vehicles/add.php`, { method: 'POST', body: JSON.stringify(payload) });
        showToast('Vehicle added', 'success');
        setTimeout(() => location.reload(), 800);
    } catch {}
});
async function deleteVehicle(id) {
    if (!confirm('Delete this vehicle?')) return;
    try {
        await apiCall(`${SITE_URL}/user/vehicles/delete.php`, { method: 'POST', body: JSON.stringify({ id, csrf_token: '<?= csrfToken() ?>' }) });
        showToast('Deleted', 'success');
        setTimeout(() => location.reload(), 600);
    } catch {}
}
async function setDefault(id) {
    try {
        await apiCall(`${SITE_URL}/user/vehicles/edit.php`, { method: 'POST', body: JSON.stringify({ id, is_default: 1, csrf_token: '<?= csrfToken() ?>' }) });
        location.reload();
    } catch {}
}
</script>