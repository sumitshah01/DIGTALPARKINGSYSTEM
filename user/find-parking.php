<?php
$pageTitle = "Find Parking - SmartPark";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="container py-4">
    <h3 class="fw-bold mb-3">Find Parking Near You</h3>

    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" id="searchBox" class="form-control" placeholder="Search by area, landmark, or parking name...">
            </div>
            <div class="col-md-3">
                <select id="sortBy" class="form-select">
                    <option value="nearest">Nearest</option>
                    <option value="cheapest">Cheapest</option>
                    <option value="availability">Most Available</option>
                    <option value="rating">Top Rated</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2 flex-wrap">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="evFilter"><label class="form-check-label small" for="evFilter">EV</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="coveredFilter"><label class="form-check-label small" for="coveredFilter">Covered</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="accessibleFilter"><label class="form-check-label small" for="accessibleFilter">Accessible</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="open24Filter"><label class="form-check-label small" for="open24Filter">24/7</label></div>
            </div>
            <div class="col-12 d-flex align-items-center gap-3 flex-wrap pt-2">
                <button type="button" id="locateButton" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-crosshair me-1"></i> Use my location
                </button>
                <p id="locationStatus" class="location-status small mb-0" role="status" aria-live="polite">Loading nearby parking...</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="row g-3" id="parkingList"></div>
        </div>
        <div class="col-lg-5">
            <div id="parkingMap" class="sticky-top" style="height: 600px; border-radius: 12px; top: 80px;"></div>
        </div>
    </div>
</div>
<?php
$extraJs = ['find-parking.js?v=2'];
require_once __DIR__ . '/../includes/footer.php';
?>