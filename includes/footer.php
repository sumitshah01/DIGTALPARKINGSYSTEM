<footer class="bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="bi bi-p-square-fill text-primary"></i> SmartPark</h5>
                <p class="text-muted">Find parking. Save fuel. Reduce emissions.</p>
            </div>
            <div class="col-md-2">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted">About</a></li>
                    <li><a href="#" class="text-muted">Features</a></li>
                    <li><a href="#" class="text-muted">FAQ</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Contact</h6>
                <p><i class="bi bi-envelope"></i> <a class="link-light" href="mailto:sumitshahsumit853@gmail.com">sumitshahsumit853@gmail.com</a></p>
                <p><i class="bi bi-telephone"></i> <a class="link-light" href="tel:9155252072">9155252072</a></p>
            </div>
            <div class="col-md-3">
                <h6>Follow Us</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a class="link-light" href="https://www.facebook.com/sumitparking" target="_blank" rel="noopener noreferrer" aria-label="Facebook: Sumit Parking"><i class="bi bi-facebook me-2"></i>Facebook: Sumit Parking</a></li>
                    <li class="mb-2"><a class="link-light" href="https://www.instagram.com/sumitparking/" target="_blank" rel="noopener noreferrer" aria-label="Instagram: sumitparking"><i class="bi bi-instagram me-2"></i>Instagram: sumitparking</a></li>
                    <li><a class="link-light" href="https://www.youtube.com/@sumitparking" target="_blank" rel="noopener noreferrer" aria-label="YouTube: sumitparking"><i class="bi bi-youtube me-2"></i>YouTube: sumitparking</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center text-muted mb-0">&copy; <?= date('Y') ?> SmartPark. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>const SITE_URL = "<?= SITE_URL ?>";</script>
<script src="<?= SITE_URL ?>/assets/js/main.js?v=3"></script>
<script src="<?= SITE_URL ?>/assets/js/theme.js"></script>
<?php if (!empty($extraJs)) foreach($extraJs as $js): ?>
    <script src="<?= SITE_URL ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; ?>
</body>
</html>