const themeButton = document.getElementById('themeToggle');
const themeRoot = document.documentElement;

function applyTheme(theme) {
    themeRoot.dataset.theme = theme;
    if (themeButton) {
        const icon = themeButton.querySelector('i');
        if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        themeButton.setAttribute('aria-label', theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
        themeButton.title = theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
    }
}

let savedTheme = 'light';
try {
    savedTheme = localStorage.getItem('smartpark-theme') || 'light';
} catch {}
applyTheme(savedTheme === 'dark' ? 'dark' : 'light');

themeButton?.addEventListener('click', () => {
    const nextTheme = themeRoot.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
    try {
        localStorage.setItem('smartpark-theme', nextTheme);
    } catch {}
});