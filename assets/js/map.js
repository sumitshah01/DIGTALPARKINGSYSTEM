// assets/js/map.js
document.addEventListener('DOMContentLoaded', () => {
    const defaultPos = [12.9716, 77.5946];
    const maps = ['heroMap', 'miniMap']
        .map(id => document.getElementById(id))
        .filter(Boolean)
        .map(element => {
            const map = L.map(element).setView(defaultPos, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            return map;
        });
    if (!maps.length) return;

    // User location (browser)
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            maps.forEach(map => {
                map.setView([latitude, longitude], 13);
                L.marker([latitude, longitude]).addTo(map)
                    .bindPopup('<b>You are here</b>').openPopup();
                loadNearby(map, latitude, longitude);
            });
        }, () => maps.forEach(map => loadNearby(map, defaultPos[0], defaultPos[1])));
    } else {
        maps.forEach(map => loadNearby(map, defaultPos[0], defaultPos[1]));
    }
});

function loadNearby(map, lat, lng) {
    fetch(`${SITE_URL}/api/parking/nearby.php?lat=${lat}&lng=${lng}&limit=20`)
        .then(r => r.json())
        .then(d => {
            (d.data || []).forEach(p => {
                const color = p.available_slots > 0 ? '#06d6a0' : '#ef476f';
                const icon = L.divIcon({
                    html: `<div style="background:${color};width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)">P</div>`,
                    className: '', iconSize: [32, 32]
                });
                L.marker([p.latitude, p.longitude], { icon }).addTo(map)
                    .bindPopup(`
                        <b>${p.name}</b><br>
                        <small>${p.area}</small><br>
                        <span style="color:${color}">${p.available_slots} slots free</span><br>
                        <a href="${SITE_URL}/user/parking-details.php?id=${p.id}">View Details →</a>
                    `);
            });
        }).catch(console.error);
}