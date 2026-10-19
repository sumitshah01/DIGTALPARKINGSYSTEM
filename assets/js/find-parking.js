let userPos = null;
let map, markers = [], userMarker = null;

document.addEventListener('DOMContentLoaded', () => {
    userPos = getSavedUserLocation();
    initMap();
    loadParking();
    if (userPos) {
        document.getElementById('sortBy').value = 'nearest';
        document.getElementById('locationStatus').textContent = 'Showing parking nearest to your location.';
    } else {
        detectUserLocation();
    }

    document.getElementById('locateButton').addEventListener('click', detectUserLocation);

    document.getElementById('searchBox').addEventListener('input', Utils.debounce(loadParking, 400));
    ['sortBy','evFilter','coveredFilter','accessibleFilter','open24Filter'].forEach(id => {
        document.getElementById(id).addEventListener('change', loadParking);
    });
});

function getSavedUserLocation() {
    try {
        const location = JSON.parse(sessionStorage.getItem('smartpark-user-location') || 'null');
        if (Number.isFinite(location?.lat) && Number.isFinite(location?.lng)) return location;
    } catch {}
    return null;
}

function detectUserLocation() {
    const button = document.getElementById('locateButton');
    const status = document.getElementById('locationStatus');
    if (!navigator.geolocation) {
        status.textContent = 'Location is not supported by this browser. Showing parking near Bangalore.';
        return;
    }

    button.disabled = true;
    status.textContent = 'Detecting your location...';
    navigator.geolocation.getCurrentPosition(position => {
        userPos = { lat: position.coords.latitude, lng: position.coords.longitude };
        try {
            sessionStorage.setItem('smartpark-user-location', JSON.stringify(userPos));
        } catch {}
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([userPos.lat, userPos.lng]).addTo(map).bindPopup('Your location');
        map.setView([userPos.lat, userPos.lng], 14);
        document.getElementById('sortBy').value = 'nearest';
        status.textContent = 'Showing parking nearest to your location.';
        button.disabled = false;
        loadParking();
    }, error => {
        const message = error.code === error.PERMISSION_DENIED
            ? 'Location permission denied.'
            : error.code === error.TIMEOUT
                ? 'Location request timed out.'
                : 'Could not detect your location.';
        status.textContent = `${message} Showing parking near Bangalore.`;
        button.disabled = false;
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 });
}

function initMap() {
    map = L.map('parkingMap').setView([userPos?.lat || 12.9716, userPos?.lng || 77.5946], userPos ? 14 : 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    if (userPos) userMarker = L.marker([userPos.lat, userPos.lng]).addTo(map).bindPopup('Your location');
}

async function loadParking() {
    const params = new URLSearchParams();
    if (userPos) { params.set('lat', userPos.lat); params.set('lng', userPos.lng); }
    params.set('q', document.getElementById('searchBox').value);
    params.set('sort', document.getElementById('sortBy').value);
    if (document.getElementById('evFilter').checked) params.set('ev', 1);
    if (document.getElementById('coveredFilter').checked) params.set('covered', 1);
    if (document.getElementById('accessibleFilter').checked) params.set('accessible', 1);
    if (document.getElementById('open24Filter').checked) params.set('open24', 1);

    const res = await fetch(`${SITE_URL}/api/parking/nearby.php?${params}`);
    const data = await res.json();
    renderList(data.data || []);
    renderMarkers(data.data || []);
}

function renderList(list) {
    const el = document.getElementById('parkingList');
    if (!list.length) {
        el.innerHTML = `<div class="col-12 text-center text-muted py-5">
            <i class="bi bi-inbox fs-1"></i><p>No parking found matching your filters.</p></div>`;
        return;
    }
    el.innerHTML = list.map(p => `
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">${p.name}</h6>
                    <span class="slot-badge available">${p.available_slots} free</span>
                </div>
                <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> ${p.area || ''} · ${p.distance ? p.distance + ' km' : ''}</p>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge bg-light text-dark">₹${p.price_per_hour}/hr</span>
                    ${p.has_ev_charging==1?'<span class="badge bg-info-subtle text-info">EV</span>':''}
                    ${p.has_covered==1?'<span class="badge bg-secondary-subtle text-secondary">Covered</span>':''}
                    ${p.has_accessible==1?'<span class="badge bg-primary-subtle text-primary">♿</span>':''}
                </div>
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>⭐ ${p.rating || 'N/A'}</span>
                    <span>${p.eta_minutes ? p.eta_minutes + ' min' : ''}</span>
                </div>
                <a href="parking-details.php?id=${p.id}" class="btn btn-primary btn-sm w-100">Reserve Slot</a>
            </div>
        </div>`).join('');
}

function renderMarkers(list) {
    markers.forEach(m => map.removeLayer(m)); markers = [];
    list.forEach(p => {
        const color = p.available_slots > 0 ? '#06d6a0' : '#ef476f';
        const icon = L.divIcon({
            html: `<div style="background:${color};width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3)">P</div>`,
            className: '', iconSize: [30,30]
        });
        const m = L.marker([p.latitude, p.longitude], { icon }).addTo(map)
            .bindPopup(`<b>${p.name}</b><br>${p.available_slots} slots free<br><a href="parking-details.php?id=${p.id}">Details →</a>`);
        markers.push(m);
    });
}