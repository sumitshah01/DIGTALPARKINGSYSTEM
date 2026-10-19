<?php
$pageTitle = "Parking Locations";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
$pdo = (new Database())->connect();
$rows = $pdo->query("SELECT * FROM parking_locations ORDER BY id DESC")->fetchAll();
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between mb-3">
        <h3 class="fw-bold">Parking Locations</h3>
        <button class="btn btn-primary" onclick="openAddModal()"><i class="bi bi-plus-circle"></i> Add Location</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr>
                    <th>ID</th><th>Name</th><th>Area</th><th>Slots</th><th>Price/hr</th><th>EV</th><th>Rating</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['area']) ?></td>
                        <td><?= $r['total_slots'] ?></td>
                        <td>₹<?= $r['price_per_hour'] ?></td>
                        <td><?= $r['has_ev_charging'] ? '✅' : '—' ?></td>
                        <td>⭐ <?= $r['rating'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editLocation(<?= $r['id'] ?>)"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLocation(<?= $r['id'] ?>)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="locModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Parking Location</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="locForm">
                    <input type="hidden" name="id" id="locId">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Area</label><input name="area" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Latitude</label><input name="latitude" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Longitude</label><input name="longitude" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Total Slots</label><input type="number" name="total_slots" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Price/hr</label><input type="number" name="price_per_hour" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Landmark</label><input name="landmark" class="form-control"></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="has_ev_charging" class="form-check-input"><label class="form-check-label">EV Charging</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="has_accessible" class="form-check-input"><label class="form-check-label">Accessible</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="has_covered" class="form-check-input"><label class="form-check-label">Covered</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="is_24_7" class="form-check-input"><label class="form-check-label">24/7</label></div></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveLocation()">Save</button>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
let modal = new bootstrap.Modal(document.getElementById('locModal'));
function openAddModal() {
    document.getElementById('locForm').reset();
    document.getElementById('locId').value = '';
    modal.show();
}
async function editLocation(id) {
    const r = await fetch(`${SITE_URL}/api/admin/parking-crud.php?action=get&id=${id}`).then(r=>r.json());
    if (!r.success) return;
    const f = document.getElementById('locForm');
    Object.entries(r.data).forEach(([k,v]) => { if (f[k]) f[k].value = (v==1 && f[k].type==='checkbox') ? true : v; });
    modal.show();
}
async function saveLocation() {
    const data = Object.fromEntries(new FormData(document.getElementById('locForm')).entries());
    data.has_ev_charging = document.querySelector('[name=has_ev_charging]').checked ? 1 : 0;
    data.has_accessible = document.querySelector('[name=has_accessible]').checked ? 1 : 0;
    data.has_covered = document.querySelector('[name=has_covered]').checked ? 1 : 0;
    data.is_24_7 = document.querySelector('[name=is_24_7]').checked ? 1 : 0;
    data.csrf_token = '<?= csrfToken() ?>';
    await apiCall(`${SITE_URL}/api/admin/parking-crud.php`, { method: 'POST', body: JSON.stringify(data) });
    showToast('Saved successfully', 'success');
    modal.hide();
    setTimeout(() => location.reload(), 700);
}
async function deleteLocation(id) {
    if (!confirm('Delete this parking location?')) return;
    await apiCall(`${SITE_URL}/api/admin/parking-crud.php`, { method: 'POST', body: JSON.stringify({ action: 'delete', id, csrf_token: '<?= csrfToken() ?>' }) });
    showToast('Deleted', 'success');
    setTimeout(() => location.reload(), 600);
}
</script>