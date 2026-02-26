<?php
// ============================================================
// about.php — About the Site Owner
// Content placeholders are provided for the owner to fill in.
// ============================================================

$pageTitle = 'About Me';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">The Person Behind Golden Girl</span>
        <h1>About Me</h1>
        <p>A friend who happens to know real estate and insurance.</p>
    </div>
</section>

<!-- ── ABOUT CONTENT ─────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="about-layout">

            <!-- ── LEFT: Photo & credentials ─────────────────── -->
            <div>
                <!-- Photo placeholder — replace src with actual image -->
                <div class="about-photo" aria-label="Photo of the site owner">
                    👩
                    <!-- To use a real photo, replace the emoji with:
                         <img src="<?= BASE_URL ?>/images/owner-photo.jpg" alt="[Owner name]">
                         and remove the font-size style from .about-photo -->
                </div>

                <!-- Credential bullets — update these with real info -->
                <ul class="about-credentials mt-3">
                    <li>Licensed Real Estate Agent — [State]</li>
                    <li>Certified Life Insurance Specialist</li>
                    <li>[Years] Years of Experience</li>
                    <li>Member of [Association Name]</li>
                    <li>[Any other credential]</li>
                </ul>
            </div>

            <!-- ── RIGHT: Bio text ──────────────────────────── -->
            <div>
                <!-- Name and tagline — update with real info -->
                <span class="section-label">Hi, I'm</span>
                <h2>[Your Name Here]</h2>

                <!-- Bio paragraphs — owner to fill in -->
                <p style="font-size:1.05rem; color:var(--text-mid); margin-bottom:1.5rem;">
                    <!-- Placeholder: replace with your real story -->
                    This is where your story goes. Tell visitors who you are, why you
                    started Golden Girl, and what drives you to help women navigate
                    real estate and insurance during one of life's most challenging transitions.
                </p>

                <p style="color:var(--text-mid);">
                    Whether it's helping someone find their first solo home, making sure
                    their children are protected, or simply being a calm and knowledgeable
                    voice during a stressful time — this is work that matters to me personally.
                    [Replace with your own words.]
                </p>

                <p style="color:var(--text-mid);">
                    [Optional third paragraph — background, community involvement,
                    personal interests, or a note about your own experience.]
                </p>

                <!-- Quote / personal statement -->
                <div class="quote-banner mt-3">
                    "Every woman deserves to feel financially secure and at home in her new life."
                    <cite>— [Your Name]</cite>
                </div>

                <!-- CTA at the bottom of the bio -->
                <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>/schedule.php" class="btn btn-warm">Book a Free Call</a>
                    <a href="<?= BASE_URL ?>/blog.php"     class="btn btn-outline">Read the Blog</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ── VALUES SECTION ─────────────────────────────────────────── -->
<section class="section bg-teal-light">
    <div class="container">
        <div class="text-center mb-4">
            <span class="section-label">What I Believe In</span>
            <h2>The Golden Girl Approach</h2>
        </div>

        <div class="cards-grid">
            <div class="card text-center">
                <div class="card-icon warm" style="margin:0 auto 1rem;">🤝</div>
                <h3>Compassion First</h3>
                <p>Before strategy, before numbers — I listen. Your situation is unique and deserves to be treated that way.</p>
            </div>
            <div class="card text-center">
                <div class="card-icon teal" style="margin:0 auto 1rem;">💡</div>
                <h3>Clear Guidance</h3>
                <p>No jargon, no pressure. I explain every option in plain language so you can make confident decisions.</p>
            </div>
            <div class="card text-center">
                <div class="card-icon warm" style="margin:0 auto 1rem;">🌿</div>
                <h3>Your Pace</h3>
                <p>This is your journey. We move as fast or as slowly as you need — with full support every step of the way.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>