document.addEventListener('DOMContentLoaded', async () => {
    const detailModalElement = document.getElementById('metricDetailModal');
    const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
    document.querySelectorAll('[data-metric]').forEach(button => {
        button.addEventListener('click', () => showMetricDetails(button.dataset.metric, detailModal));
    });

    const stats = await fetch(`${SITE_URL}/api/admin/dashboard-stats.php`).then(r => r.json());
    if (stats.success) {
        Object.entries(stats.data).forEach(([k, v]) => {
            document.querySelectorAll(`[data-stat="${k}"]`).forEach(el => {
                el.textContent = typeof v === 'number' && k.includes('revenue') ? v.toFixed(2) : Number(v).toLocaleString();
            });
        });
    }

    const a = await fetch(`${SITE_URL}/api/admin/analytics.php?range=7`).then(r => r.json());
    if (!a.success) return;

    // Usage chart
    new Chart(document.getElementById('usageChart'), {
        type: 'line',
        data: {
            labels: a.usage.map(u => u.day),
            datasets: [{
                label: 'Sessions',
                data: a.usage.map(u => u.sessions),
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67,97,238,0.15)',
                tension: 0.4, fill: true
            }]
        }
    });

    // Vehicle chart
    new Chart(document.getElementById('vehicleChart'), {
        type: 'doughnut',
        data: {
            labels: a.vehicle_types.map(v => v.vehicle_type),
            datasets: [{ data: a.vehicle_types.map(v => v.count),
                backgroundColor: ['#4361ee','#06d6a0','#ef476f'] }]
        }
    });

    // Peak hours
    new Chart(document.getElementById('peakChart'), {
        type: 'bar',
        data: {
            labels: a.peak_hours.map(p => p.hour + ':00'),
            datasets: [{ label: 'Entries', data: a.peak_hours.map(p => p.count),
                backgroundColor: '#4cc9f0' }]
        }
    });
});

async function showMetricDetails(metric, modal) {
    const title = document.getElementById('metricDetailTitle');
    const status = document.getElementById('metricDetailStatus');
    const tableWrap = document.getElementById('metricDetailTableWrap');
    title.textContent = 'Loading details...';
    status.textContent = 'Retrieving records';
    tableWrap.replaceChildren();
    modal.show();

    try {
        const details = await apiCall(`${SITE_URL}/api/admin/dashboard-details.php?metric=${encodeURIComponent(metric)}`);
        title.textContent = details.title;

        if (!details.rows.length) {
            status.textContent = 'No records found for this metric.';
            return;
        }

        status.textContent = details.rows.length === 100 ? 'Showing the first 100 records.' : `${details.rows.length} records`;
        const table = document.createElement('table');
        table.className = 'table table-sm table-hover align-middle mb-0';
        const head = document.createElement('thead');
        const headerRow = document.createElement('tr');
        details.columns.forEach(label => {
            const cell = document.createElement('th');
            cell.scope = 'col';
            cell.textContent = label;
            headerRow.appendChild(cell);
        });
        head.appendChild(headerRow);
        table.appendChild(head);

        const body = document.createElement('tbody');
        details.rows.forEach(row => {
            const tableRow = document.createElement('tr');
            row.forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value === null || value === '' ? '—' : String(value);
                tableRow.appendChild(cell);
            });
            body.appendChild(tableRow);
        });
        table.appendChild(body);
        tableWrap.appendChild(table);
    } catch (error) {
        title.textContent = 'Unable to load details';
        status.textContent = error.message;
    }
}