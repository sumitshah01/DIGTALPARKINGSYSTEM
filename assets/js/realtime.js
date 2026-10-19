// assets/js/realtime.js
const Realtime = {
    interval: null,
    start(callback, ms = 5000) {
        this.stop();
        this.interval = setInterval(callback, ms);
    },
    stop() {
        if (this.interval) clearInterval(this.interval);
    }
};

// Auto-refresh live parking cards on landing & user dashboards
function refreshLiveParking() {
    const container = document.getElementById('liveParkingCards');
    if (!container) return;
    fetch(`${SITE_URL}/api/parking/nearby.php?limit=3`)
        .then(r => r.json())
        .then(data => renderParkingCards(container, data.data || []))
        .catch(console.error);
}

function renderParkingCards(container, list) {
    if (!list.length) {
        container.innerHTML = `<div class="col-12 text-center text-muted py-5">
            <i class="bi bi-inbox fs-1"></i><p>No parking locations found.</p></div>`;
        return;
    }
    container.innerHTML = list.map(p => `
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 fade-in">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="fw-bold mb-0">${p.name}</h5>
                    <span class="slot-badge available">
                        <i class="bi bi-circle-fill" style="font-size:8px"></i>
                        ${p.available_slots} free
                    </span>
                </div>
                <p class="text-muted small"><i class="bi bi-geo-alt"></i> ${p.area || p.address}</p>
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="badge bg-light text-dark">${p.distance ? p.distance.toFixed(1) + ' km' : 'Nearby'}</span>
                    <span class="badge bg-light text-dark">₹${p.price_per_hour}/hr</span>
                    ${p.has_ev_charging == 1 ? '<span class="badge bg-info-subtle text-info"><i class="bi bi-ev-station"></i> EV</span>' : ''}
                </div>
                <div class="progress mb-3" style="height:6px">
                    <div class="progress-bar bg-success" style="width:${(p.available_slots/p.total_slots)*100}%"></div>
                </div>
                <a href="${SITE_URL}/user/parking-details.php?id=${p.id}" class="btn btn-primary w-100">
                    View Details <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>`).join('');
}

// Load environmental impact stats
function loadImpactStats() {
    fetch(`${SITE_URL}/api/analytics/environmental.php`)
        .then(r => r.json())
        .then(d => {
            const s = d.data || {};
            const set = (id, val, suffix='') => {
                const el = document.getElementById(id);
                if (el) el.textContent = Number(val).toLocaleString() + suffix;
            };
            set('fuelSaved', s.fuel_saved_liters || 0, ' L');
            set('co2Avoided', s.co2_avoided_kg || 0, ' kg');
            set('timeSaved', s.search_time_saved_hours || 0, ' hrs');
            set('vehiclesHelped', s.vehicles_assisted || 0);
        }).catch(console.error);
}

document.addEventListener('DOMContentLoaded', () => {
    refreshLiveParking();
    loadImpactStats();
    Realtime.start(() => { refreshLiveParking(); loadImpactStats(); }, 7000);
});