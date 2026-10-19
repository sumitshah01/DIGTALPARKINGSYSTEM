window.Utils = window.Utils || {};
window.Utils.debounce = window.Utils.debounce || ((callback, delay) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), delay);
    };
});

async function apiCall(url, options = {}) {
    const headers = new Headers(options.headers || {});
    if (typeof options.body === 'string' && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(url, { ...options, headers });
    let data;
    try {
        data = await response.json();
    } catch {
        throw new Error('The server returned an invalid response.');
    }

    if (!response.ok || data.success === false) {
        throw new Error(data.message || `Request failed (${response.status}).`);
    }
    return data;
}

function showToast(message, type = 'info') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
    toast.querySelector('.toast-body').textContent = message;
    container.appendChild(toast);

    const instance = bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 });
    toast.addEventListener('hidden.bs.toast', () => toast.remove(), { once: true });
    instance.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const locateButtons = [...document.querySelectorAll('[data-locate-user]')];
    if (!locateButtons.length) return;

    locateButtons.forEach(locateButton => locateButton.addEventListener('click', () => {
        if (!navigator.geolocation) {
            showToast('Location detection is not available in this browser.', 'danger');
            return;
        }

        locateButtons.forEach(button => {
            button.disabled = true;
            const label = button.querySelector('span');
            if (label) label.textContent = 'Detecting location...';
        });

        navigator.geolocation.getCurrentPosition(position => {
            const location = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            try {
                sessionStorage.setItem('smartpark-user-location', JSON.stringify(location));
            } catch {}

            window.location.href = `${SITE_URL}/user/find-parking.php`;
        }, error => {
            locateButtons.forEach(button => {
                button.disabled = false;
                const label = button.querySelector('span');
                if (label) label.textContent = 'Use my location';
            });
            const message = error.code === error.PERMISSION_DENIED
                ? 'Location permission was denied.'
                : error.code === error.TIMEOUT
                    ? 'Location request timed out. Please try again.'
                    : 'Could not detect your location. Please try again.';
            showToast(message, 'danger');
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 });
    }));
});