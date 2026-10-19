document.addEventListener('DOMContentLoaded', async () => {
    const parkingId = document.querySelector('input[name="parking_id"]').value;

    // Load vehicles
    const v = await fetch(`${SITE_URL}/user/vehicles/list.php`).then(r => r.json());
    const vSelect = document.getElementById('vehicleSelect');
    if (v.data && v.data.length) {
        vSelect.innerHTML = v.data.map(x => `<option value="${x.id}" ${x.is_default==1?'selected':''}>${x.vehicle_number} (${x.model || x.vehicle_type})</option>`).join('');
    } else {
        vSelect.innerHTML = '<option value="">No vehicles — add one</option>';
    }

    // Map
    const lat = parseFloat(document.querySelector('meta[name="lat"]')?.content || 12.9716);
    // Simpler: read from data attributes
    const mapLat = window.PARKING_LAT || 12.9716, mapLng = window.PARKING_LNG || 77.5946;
    const map = L.map('detailsMap').setView([mapLat, mapLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([mapLat, mapLng]).addTo(map).bindPopup('Parking Location').openPopup();

    // Real-time slot updates
    setInterval(async () => {
        const res = await fetch(`${SITE_URL}/api/parking/availability.php?parking_id=${parkingId}`);
        const d = await res.json();
        if (!d.slots) return;
        d.slots.forEach(s => {
            const el = document.querySelector(`[data-slot="${s.id}"]`);
            if (!el) return;
            const color = {available:'success',occupied:'danger',reserved:'warning',maintenance:'secondary'}[s.status];
            el.className = `border rounded p-2 text-center slot-${s.status}`;
            el.querySelector('.badge').className = `badge bg-${color}`;
            el.querySelector('.badge').textContent = s.status;
        });
    }, 5000);

    // Reserve form
    document.getElementById('reserveForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        const payload = Object.fromEntries(form.entries());
        payload.csrf_token = form.get('csrf_token');
        try {
            const r = await apiCall(`${SITE_URL}/api/reservations/create.php`, {
                method: 'POST', body: JSON.stringify(payload)
            });
            showToast('Reservation successful! Booking: ' + r.reservation.booking_code, 'success');
            setTimeout(() => location.href = `${SITE_URL}/user/dashboard.php`, 1500);
        } catch (err) {}
    });

    document.getElementById('reviewForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        try {
            await apiCall(`${SITE_URL}/api/payments/reviews/add.php`, {
                method: 'POST', body: JSON.stringify(payload)
            });
            showToast('Review submitted', 'success');
            setTimeout(() => location.reload(), 700);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });
});