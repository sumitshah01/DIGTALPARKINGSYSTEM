// assets/js/monitoring.js
// Real-time admin monitoring dashboard

document.addEventListener('DOMContentLoaded', () => {
    refreshMonitoring();

    // Auto-refresh every 5 seconds
    setInterval(refreshMonitoring, 5000);
});

async function refreshMonitoring() {
    try {
        const res = await fetch(`${SITE_URL}/api/admin/monitoring.php`, {
            credentials: 'same-origin'
        });
        const r = await res.json();
        if (!r.success) return;

        const d = r.data;

        // Update stat cards
        updateStat('liveAvail', d.available);
        updateStat('liveOcc', d.occupied);
        updateStat('liveRes', d.reserved);
        updateStat('livePct', d.occupancy_rate + '%');

        // Update active sessions table
        renderSessions(d.sessions || []);

    } catch (err) {
        console.error('Monitoring refresh failed:', err);
    }
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (!el) return;

    const newVal = typeof value === 'number' ? value.toLocaleString() : value;

    // Only update if value changed (avoid flicker)
    if (el.textContent !== newVal) {
        el.textContent = newVal;
        el.classList.add('fade-in');
        setTimeout(() => el.classList.remove('fade-in'), 500);
    }
}

function renderSessions(sessions) {
    const tbody = document.getElementById('activeSessions');
    if (!tbody) return;

    if (!sessions.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No active sessions
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = sessions.map(s => {
        const duration = Math.round(
            (Date.now() - new Date(s.entry_time).getTime()) / 60000
        );
        const durationText = duration < 60
            ? `${duration} min`
            : `${Math.floor(duration / 60)}h ${duration % 60}m`;

        return `
            <tr class="fade-in">
                <td>
                    <strong>${escapeHtml(s.vehicle_number || '—')}</strong>
                </td>
                <td>${escapeHtml(s.parking_name || '—')}</td>
                <td>
                    <span class="badge bg-secondary">${escapeHtml(s.slot_number || '—')}</span>
                </td>
                <td>
                    <small>${formatDateTime(s.entry_time)}</small>
                </td>
                <td>
                    <span class="text-primary fw-bold">${durationText}</span>
                </td>
                <td>
                    <span class="badge bg-success pulse">Active</span>
                </td>
            </tr>`;
    }).join('');
}

function formatDateTime(dt) {
    const d = new Date(dt);
    return d.toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}