<?php
$pageTitle = "Login - SmartPark";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) {
    $dashboard = isAdmin() ? '/api/admin/dashboard.php' : '/user/dashboard.php';
    header('Location: ' . SITE_URL . $dashboard);
    exit;
}
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="auth-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card border-0 shadow p-4">
                <h3 class="fw-bold mb-1">Welcome back</h3>
                <p class="text-muted mb-4">Login to your SmartPark account</p>
                <form id="loginForm">
                    <?= csrfField() ?>
                    <div class="mb-3"><label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required></div>
                    <button class="btn btn-primary w-100 mb-3">Login</button>
                </form>
                <p class="text-center mt-3 mb-0 small">
                    Don't have an account? <a href="register.php">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(e.target).entries());
    try {
        const r = await apiCall(`${SITE_URL}/api/auth/login.php`, { method: 'POST', body: JSON.stringify(payload) });
        showToast('Login successful', 'success');
        setTimeout(() => location.href = r.role === 'admin' ? `${SITE_URL}/api/admin/dashboard.php` : `${SITE_URL}/user/dashboard.php`, 700);
    } catch (err) {
        showToast(err.message, 'danger');
    }
});
</script>