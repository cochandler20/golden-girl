<?php
// ============================================================
// index.php — Home / Introduction Page
// The first page visitors see. Describes the services and
// gives a welcoming first impression of the Golden Girl brand.
// ============================================================

// Set the browser tab title before loading the header
$pageTitle = 'Home';

// Load the shared header (starts session, outputs <head> and nav)
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── HERO SECTION ─────────────────────────────────────────── -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <!-- Subtle category label -->
            <span class="hero-label">Real Estate &amp; Insurance</span>

            <h1>Your Next Chapter<br>Starts Here</h1>

            <p class="subtitle">
                You've been through a lot. Golden Girl is here to help you navigate
                real estate and insurance with confidence, clarity, and a steady hand
                by your side.
            </p>

            <!-- Primary call-to-action buttons -->
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>/schedule.php" class="btn btn-warm btn-lg">Book a Free Call</a>
                <a href="#services"     class="btn btn-outline btn-lg" style="color:#fff; border-color:rgba(255,255,255,0.5);">See Our Services</a>
            </div>
        </div>
    </div>
</section>

<!-- ── TRUST BAR ─────────────────────────────────────────────── -->
<section class="section-sm bg-teal-light">
    <div class="container text-center">
        <p style="color:var(--teal-dark); font-weight:700; font-size:0.9rem; letter-spacing:0.05em;">
            Trusted guidance for women navigating real estate &amp; insurance after divorce
        </p>
    </div>
</section>

<!-- ── SERVICES SECTION ──────────────────────────────────────── -->
<section class="section" id="services">
    <div class="container">

        <!-- Section heading -->
        <div class="text-center mb-4">
            <span class="section-label">What We Offer</span>
            <h2>Services Designed for <em>You</em></h2>
            <p style="color:var(--text-mid); max-width:560px; margin:0.8rem auto 0;">
                Whether you're buying your first solo home or making sure your family
                is protected, we've got you covered — step by step.
            </p>
        </div>

        <!-- Service cards grid -->
        <div class="cards-grid">

            <!-- Real Estate card -->
            <div class="card">
                <div class="card-icon teal">🏡</div>
                <h3>Real Estate Services</h3>
                <p>
                    Buying, selling, or figuring out what to do with the family home —
                    we guide you through every decision with patience and expertise.
                </p>
                <a href="<?= BASE_URL ?>/schedule.php?service=real-estate" class="btn btn-outline mt-2">Get Started</a>
            </div>

            <!-- Life Insurance card -->
            <div class="card">
                <div class="card-icon warm">🛡️</div>
                <h3>Life Insurance</h3>
                <p>
                    Protect yourself and your children's future. We'll help you find
                    the right policy that fits your new life and your budget.
                </p>
                <a href="<?= BASE_URL ?>/schedule.php?service=life-insurance" class="btn btn-outline mt-2">Learn More</a>
            </div>

            <!-- Consultation card -->
            <div class="card">
                <div class="card-icon teal">☎️</div>
                <h3>Free Consultation</h3>
                <p>
                    Not sure where to start? Schedule a no-pressure conversation so
                    we can understand your situation and point you in the right direction.
                </p>
                <a href="<?= BASE_URL ?>/schedule.php?service=consultation" class="btn btn-outline mt-2">Book a Call</a>
            </div>

        </div>
    </div>
</section>

<!-- ── QUOTE / MISSION BANNER ─────────────────────────────────── -->
<section class="section-sm bg-warm-light">
    <div class="container" style="max-width:720px;">
        <div class="quote-banner">
            "You don't have to have it all figured out. You just have to take the
            next step — and you don't have to take it alone."
            <cite>— Golden Girl</cite>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="text-center mb-4">
            <span class="section-label">Simple Process</span>
            <h2>How It Works</h2>
        </div>

        <!-- Step cards — numbered visually -->
        <div class="cards-grid">
            <div class="card text-center">
                <div style="font-size:2rem; font-weight:700; color:var(--warm); margin-bottom:0.8rem;">01</div>
                <h3>Create Your Account</h3>
                <p>Sign up in under a minute. Your information is always private and secure.</p>
            </div>
            <div class="card text-center">
                <div style="font-size:2rem; font-weight:700; color:var(--warm); margin-bottom:0.8rem;">02</div>
                <h3>Select Your Services</h3>
                <p>Tell us what you're looking for and share a little about your situation.</p>
            </div>
            <div class="card text-center">
                <div style="font-size:2rem; font-weight:700; color:var(--warm); margin-bottom:0.8rem;">03</div>
                <h3>Schedule a Call</h3>
                <p>Pick a time that works for you and we'll meet you right where you are.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA BANNER ─────────────────────────────────────────────── -->
<section class="section bg-teal text-center">
    <div class="container" style="max-width:620px;">
        <h2>Ready to Take the First Step?</h2>
        <p>
            Join other women who are building their futures with confidence.
            Your consultation is free and completely no-obligation.
        </p>
        <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; margin-top:1.8rem;">
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-warm btn-lg">Create an Account</a>
            <a href="<?= BASE_URL ?>/blog.php"     class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,0.5); color:#fff;">Read the Blog</a>
        </div>
    </div>
</section>

<?php
// Load the shared footer (outputs </main>, <footer>, and </html>)
require_once __DIR__ . '/includes/footer.php';
?>