<?php
// ============================================================
// schedule.php — Scheduling & Service Request Page
// Combines a service-selection form with a Calendly embed.
// Users log in to save their service request to the DB,
// then complete scheduling via Calendly.
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();

$errors  = [];
$success = '';

// ── Handle service request form submission ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Must be logged in to submit a service request
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php?redirect=' . urlencode(BASE_URL . '/schedule.php'));
    }

    // CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {

        // ── Collect checked services ──────────────────────────
        // Only allow values from a known whitelist to prevent injection
        $allowedServices = ['Real Estate', 'Life Insurance', 'Free Consultation'];
        $rawServices     = $_POST['services'] ?? [];

        // Filter to only accepted values
        $selectedServices = array_filter($rawServices, fn($s) => in_array($s, $allowedServices, true));

        if (empty($selectedServices)) {
            $errors[] = 'Please select at least one service.';
        }

        // Collect and sanitize the notes/message field
        $notes = trim($_POST['notes'] ?? '');
        if (strlen($notes) > 2000) {
            $errors[] = 'Your message is too long (max 2000 characters).';
        }

        if (empty($errors)) {
            $db = getDB();

            // Store the appointment request in the database
            $stmt = $db->prepare('
                INSERT INTO appointments (user_id, services, notes)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([
                $_SESSION['user_id'],
                implode(', ', $selectedServices), // Store as comma-separated string
                $notes
            ]);

            $success = 'Your service request has been saved! Please use the calendar below to book your appointment time.';
        }
    }
}

$pageTitle = 'Schedule a Consultation';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Let's Connect</span>
        <h1>Schedule Your Consultation</h1>
        <p>Tell us what you need, then pick a time that works for you.</p>
    </div>
</section>

<!-- ── SCHEDULE CONTENT ──────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="schedule-layout">

            <!-- ── LEFT: Service request form ───────────────── -->
            <div>
                <h2 style="margin-bottom:0.5rem;">What Are You Looking For?</h2>
                <p style="color:var(--text-mid); margin-bottom:2rem;">
                    Select the services you're interested in and tell us a bit about
                    your situation. Then use the calendar to book a time.
                </p>

                <?php if (!isLoggedIn()): ?>
                    <!-- Prompt guests to log in before submitting -->
                    <div class="alert alert-info">
                        <strong>Please <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode(BASE_URL . '/schedule.php') ?>">sign in</a></strong>
                        or <a href="<?= BASE_URL ?>/register.php">create a free account</a> to save
                        your service request and book an appointment.
                    </div>
                <?php endif; ?>

                <!-- Success message after form submit -->
                <?php if ($success): ?>
                    <div class="alert alert-success" data-auto-dismiss role="alert">
                        <?= sanitize($success) ?>
                    </div>
                <?php endif; ?>

                <!-- Validation errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" role="alert">
                        <?php foreach ($errors as $e): ?><p><?= sanitize($e) ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Service selection form -->
                <form id="service-form" method="POST" action="<?= BASE_URL ?>/schedule.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <!-- Service checkboxes -->
                    <div class="form-group">
                        <label>Select Services <span style="color:var(--warm);">*</span></label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="services[]" value="Real Estate"
                                       <?= (isset($_POST['services']) && in_array('Real Estate', $_POST['services'])) ? 'checked' : '' ?>>
                                <span>🏡 <strong>Real Estate</strong> — Buying, selling, or navigating property decisions</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="services[]" value="Life Insurance"
                                       <?= (isset($_POST['services']) && in_array('Life Insurance', $_POST['services'])) ? 'checked' : '' ?>>
                                <span>🛡️ <strong>Life Insurance</strong> — Protecting yourself and your family's future</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="services[]" value="Free Consultation"
                                       <?= (isset($_POST['services']) && in_array('Free Consultation', $_POST['services'])) ? 'checked' : '' ?>>
                                <span>☎️ <strong>Free Consultation</strong> — Not sure yet? Let's just talk.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes field -->
                    <div class="form-group">
                        <label for="notes">Tell Us More <small style="font-weight:400; color:var(--text-light);">(optional)</small></label>
                        <textarea id="notes"
                                  name="notes"
                                  maxlength="2000"
                                  placeholder="Share a little about what you're looking for or what's on your mind. No detail is too small."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                        <small style="color:var(--text-light);">Max 2000 characters. Your information is private and secure.</small>
                    </div>

                    <!-- Submit — only available to logged-in users -->
                    <?php if (isLoggedIn()): ?>
                        <button type="submit" class="btn btn-warm btn-lg" style="width:100%;">
                            Save My Request &amp; Book a Time →
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode(BASE_URL . '/schedule.php') ?>" class="btn btn-warm btn-lg" style="display:block; text-align:center;">
                            Sign In to Continue
                        </a>
                    <?php endif; ?>

                </form>

                <!-- Reassurance copy below the form -->
                <div style="margin-top:1.5rem; padding:1.2rem; background:var(--warm-light); border-radius:var(--radius); border-left:4px solid var(--warm);">
                    <p style="margin:0; font-size:0.88rem; color:var(--text-mid);">
                        🔒 <strong>Your privacy matters.</strong> The information you share
                        is only seen by Golden Girl and is never sold or shared with third parties.
                    </p>
                </div>
            </div>

            <!-- ── RIGHT: Calendly embed ────────────────────── -->
            <div>
                <h2 style="margin-bottom:0.5rem;">Pick a Time</h2>
                <p style="color:var(--text-mid); margin-bottom:1.5rem;">
                    Choose an available slot on the calendar below.
                </p>

                <!-- Calendly inline embed -->
                <div class="calendly-wrap">
                    <!-- ⚠️ ACTION REQUIRED:
                         Replace 'your-calendly-username' below with your actual
                         Calendly username or event URL.
                         e.g. https://calendly.com/goldengirl/consultation
                    -->
                    <div class="calendly-inline-widget"
                         data-url="https://calendly.com/your-calendly-username/consultation?hide_gdpr_banner=1&primary_color=2A9D8F"
                         style="min-width:280px; height:600px;">
                    </div>

                    <!-- Calendly embed script — loaded asynchronously for performance -->
                    <script async src="https://assets.calendly.com/assets/external/widget.js"></script>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>