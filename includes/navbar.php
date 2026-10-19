<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= SITE_URL ?>">
            <i class="bi bi-p-square-fill text-primary"></i> SmartPark
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/user/find-parking.php">Find Parking</a></li>
                <li class="nav-item nav-location-item">
                    <button type="button" id="navLocateButton" data-locate-user class="btn btn-outline-primary btn-sm nav-location-button" title="Find parking near your current location">
                        <i class="bi bi-crosshair"></i><span>Use my location</span>
                    </button>
                </li>
                <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#impact">Impact</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/vehicles.php">Vehicles</a></li>
                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/api/admin/dashboard.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/api/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item ms-2">
                        <button class="btn btn-icon" id="themeToggle"><i class="bi bi-moon-stars"></i></button>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-primary ms-2" href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary ms-2" href="<?= SITE_URL ?>/register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="mobile-location-bar">
    <button type="button" id="mobileLocateButton" data-locate-user class="btn btn-primary mobile-location-button">
        <i class="bi bi-crosshair"></i><span>Use my location</span>
    </button>
</div>