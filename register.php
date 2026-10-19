<?php
$pageTitle = "Create Account - SmartPark";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/user/dashboard.php'); exit; }
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="auth-page">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card border-0 shadow p-4">
                <h3 class="fw-bold mb-1">Create your account</h3>
                <p class="text-muted mb-4">Join SmartPark to find and reserve parking.</p>
                <form id="registerForm">
                    <?= csrfField() ?>
                    <div class="mb-3"><label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required></div>
                    <button class="btn btn-primary w-100 mb-3">Create Account</button>
                </form>
                <p class="text-center mt-3 mb-0 small">
                    Already registered? <a href="login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(e.target).entries());
    try {
        await apiCall(`${SITE_URL}/api/auth/register.php`, { method: 'POST', body: JSON.stringify(payload) });
        showToast('Account created. You can now log in.', 'success');
        setTimeout(() => location.href = `${SITE_URL}/login.php`, 900);
    } catch {}
});
</script>