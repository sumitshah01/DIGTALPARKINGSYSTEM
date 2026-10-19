<?php
$pageTitle = "SmartPark - Find Parking. Save Fuel. Reduce Emissions.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- HERO -->
<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary-subtle text-primary mb-3">🌱 Smart City Parking Solution</span>
                <h1 class="display-4 fw-bold mb-3">
                    Find Parking.<br>
                    <span class="text-primary">Save Fuel.</span><br>
                    Reduce Emissions.
                </h1>
                <p class="lead text-muted mb-4">
                    Find available parking spaces in real time and reach your destination 
                    without wasting fuel searching for a spot.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= SITE_URL ?>/user/find-parking.php" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-geo-alt-fill"></i> Find Parking
                    </a>
                    <a href="<?= SITE_URL ?>/user/find-parking.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-bookmark-check"></i> Reserve a Slot
                    </a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4">
                        <h3 class="fw-bold text-primary mb-0" data-counter="1250">0</h3>
                        <small class="text-muted">Vehicles Assisted Today</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold text-success mb-0" data-counter="125">0</h3>
                        <small class="text-muted">Liters Fuel Saved</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold text-info mb-0" data-counter="290">0</h3>
                        <small class="text-muted">kg CO₂ Avoided</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div id="heroMap" class="rounded-4 shadow-lg overflow-hidden" style="height: 400px;" aria-label="Map showing nearby parking"></div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="text-muted">Get parking in 4 simple steps</p>
        </div>
        <div class="row g-4">
            <?php
            $steps = [
                ['bi-search', 'Enter Destination', 'Search by area, landmark, or destination.'],
                ['bi-geo-alt', 'Find Parking', 'See real-time availability near you.'],
                ['bi-bookmark-check', 'Reserve Slot', 'Book your preferred slot in seconds.'],
                ['bi-car-front', 'Navigate & Park', 'Get directions and park with QR entry.']
            ];
            foreach ($steps as $i => $s): ?>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="step-icon mx-auto mb-3">
                            <i class="bi <?= $s[0] ?> fs-2 text-primary"></i>
                        </div>
                        <h6 class="fw-bold">Step <?= $i+1 ?></h6>
                        <h5 class="fw-bold"><?= $s[1] ?></h5>
                        <p class="text-muted small mb-0"><?= $s[2] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- REAL-TIME PARKING -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Real-Time Parking Availability</h2>
            <p class="text-muted">Live slot status updated every few seconds</p>
        </div>
        <div class="row g-4" id="liveParkingCards">
            <!-- Loaded via JS from /api/parking/nearby.php -->
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Smart Features</h2>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['bi-cpu', 'AI Recommendations', 'Suggests the best parking based on your preferences.', 'ai-recommendations.svg', 'Illustrated map with a recommended parking location'],
                ['bi-ev-station', 'EV Charging', 'Find EV slots with live availability.', 'ev-charging.svg', 'Electric car connected to a charging station'],
                ['bi-qr-code', 'QR Entry/Exit', 'Contactless entry & exit via QR codes.', 'qr-entry-exit.svg', 'Phone displaying a QR code beside a parking gate'],
                ['bi-graph-up', 'Occupancy Prediction', 'Predict peak hours and demand.', 'occupancy-prediction.svg', 'Parking occupancy chart with predicted busy hours'],
                ['bi-piggy-bank', 'Dynamic Pricing', 'Prices adjust based on real-time demand.', 'dynamic-pricing.svg', 'Parking price indicator adapting to demand'],
                ['bi-tree', 'Carbon Tracking', 'See your personal environmental impact.', 'carbon-tracking.svg', 'Leaf and car illustration representing reduced emissions']
            ];
            foreach ($features as $f): ?>
                <div class="col-md-4">
                    <article class="card smart-feature-card border-0 shadow-sm h-100">
                        <div class="smart-feature-image">
                            <img src="<?= SITE_URL ?>/assets/images/smart-features/<?= $f[3] ?>" alt="<?= $f[4] ?>" loading="lazy">
                            <span class="smart-feature-icon" aria-hidden="true">
                                <i class="bi <?= $f[0] ?>"></i>
                            </span>
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold"><?= $f[1] ?></h5>
                            <p class="text-muted mb-0"><?= $f[2] ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ENVIRONMENTAL IMPACT -->
<section id="impact" class="py-5 bg-success-subtle">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">🌍 Today's Environmental Impact</h2>
            <p class="text-muted">Estimated metrics based on system data</p>
        </div>
        <div class="row g-4 text-center" id="impactStats">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-success" id="fuelSaved">0 L</h3>
                    <p class="text-muted mb-0">Fuel Saved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-info" id="co2Avoided">0 kg</h3>
                    <p class="text-muted mb-0">CO₂ Avoided</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-warning" id="timeSaved">0 hrs</h3>
                    <p class="text-muted mb-0">Search Time Saved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-primary" id="vehiclesHelped">0</h3>
                    <p class="text-muted mb-0">Vehicles Assisted</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What Users Say</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="text-warning mb-2">★★★★★</div>
                    <p class="text-muted">"I save at least 15 minutes every day. No more circling the block!"</p>
                    <small class="fw-bold">— Rahul S.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="text-warning mb-2">★★★★★</div>
                    <p class="text-muted">"The EV charging filter is a lifesaver for my electric car."</p>
                    <small class="fw-bold">— Priya P.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="text-warning mb-2">★★★★☆</div>
                    <p class="text-muted">"Great for finding parking in crowded areas. The QR entry is smooth."</p>
                    <small class="fw-bold">— Amit K.</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5"><h2 class="fw-bold">FAQ</h2></div>
        <div class="accordion" id="faqAcc">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#f1">
                        How does real-time availability work?
                    </button>
                </h2>
                <div id="f1" class="accordion-collapse collapse show" data-bs-parent="#faqAcc">
                    <div class="accordion-body">
                        Slot status is updated live via IoT sensors and manual check-ins. The UI refreshes automatically every few seconds.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f2">
                        Can I reserve a slot in advance?
                    </button>
                </h2>
                <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                    <div class="accordion-body">
                        Yes! Choose your parking, slot, vehicle, and time — we generate a QR ticket instantly.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f3">
                        What payment methods are supported?
                    </button>
                </h2>
                <div id="f3" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                    <div class="accordion-body">
                        UPI, Credit/Debit cards, Net banking, Wallet balance, and Cash.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section id="contact" class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h2 class="fw-bold">Get in Touch</h2>
                <p class="text-muted">Questions? We'd love to hear from you.</p>
                <form id="contactForm" class="row g-3">
                    <div class="col-md-6"><input class="form-control" placeholder="Name" required></div>
                    <div class="col-md-6"><input class="form-control" type="email" placeholder="Email" required></div>
                    <div class="col-12"><textarea class="form-control" rows="4" placeholder="Message" required></textarea></div>
                    <div class="col-12"><button class="btn btn-primary" id="contactSubmit">Send Message</button></div>
                </form>
            </div>
            <div class="col-md-6">
                <div id="miniMap" style="height: 600px; border-radius: 12px;"></div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= SITE_URL ?>/assets/js/realtime.js"></script>
<script src="<?= SITE_URL ?>/assets/js/map.js"></script>