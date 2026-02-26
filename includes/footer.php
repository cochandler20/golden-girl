<!-- ===================== SITE FOOTER ===================== -->
</main><!-- /#main-content -->

<footer class="site-footer">
    <div class="container footer-grid">

        <!-- Brand column -->
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>/index" class="logo">
                <span class="logo-icon">✦</span>
                <span class="logo-text">Golden Girl</span>
            </a>
            <p class="footer-tagline">Real estate &amp; insurance services for women starting their next chapter.</p>
        </div>

        <!-- Quick links column -->
        <div class="footer-links">
            <h4>Explore</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/index">Home</a></li>
                <li><a href="<?= BASE_URL ?>/blog">Blog</a></li>
                <li><a href="<?= BASE_URL ?>/schedule">Schedule a Call</a></li>
                <li><a href="<?= BASE_URL ?>/about">About Me</a></li>
            </ul>
        </div>

        <!-- Services column -->
        <div class="footer-links">
            <h4>Services</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/schedule?service=real-estate">Real Estate</a></li>
                <li><a href="<?= BASE_URL ?>/schedule?service=life-insurance">Life Insurance</a></li>
                <li><a href="<?= BASE_URL ?>/schedule?service=consultation">Free Consultation</a></li>
            </ul>
        </div>

        <!-- Contact / CTA column -->
        <div class="footer-cta">
            <h4>Ready to take the next step?</h4>
            <a href="<?= BASE_URL ?>/schedule" class="btn btn-warm">Book a Free Call</a>
            <p class="footer-privacy">
                <a href="#">Privacy Policy</a> &middot; <a href="#">Terms of Use</a>
            </p>
        </div>

    </div>

    <!-- Bottom bar -->
    <div class="footer-bottom">
        <div class="container">
            <!-- PHP date() auto-updates the copyright year -->
            <p>&copy; <?= date('Y') ?> Golden Girl. All rights reserved.</p>
        </div>
    </div>
</footer>
<!-- ========================================================= -->

<!-- Main JavaScript file — BASE_URL ensures correct path inside subfolder -->
<script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>