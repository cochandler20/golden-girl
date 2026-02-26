<?php
// ============================================================
// register.php — New User Registration
// Security measures:
//   - CSRF token on form
//   - Input validation (email format, password strength)
//   - Duplicate email check
//   - Password hashed with bcrypt (cost factor 12)
//   - Prepared statements throughout
//   - No user-supplied data ever echoed without sanitize()
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/account.php');
}

$errors = [];   // Array to collect all validation errors
$old    = [];   // Re-populate form fields on validation failure

// ── Handle POST form submission ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF check ---
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    } else {

        // --- Collect and sanitize input ---
        $old['first_name'] = trim($_POST['first_name'] ?? '');
        $old['last_name']  = trim($_POST['last_name']  ?? '');
        $old['email']      = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password          = $_POST['password']         ?? '';
        $confirmPassword   = $_POST['confirm_password'] ?? '';

        // --- Validation rules ---

        // Names
        if (empty($old['first_name'])) $errors[] = 'First name is required.';
        if (empty($old['last_name']))  $errors[] = 'Last name is required.';

        // Name length limits to prevent database overflow and abuse
        if (strlen($old['first_name']) > 80) $errors[] = 'First name is too long.';
        if (strlen($old['last_name'])  > 80) $errors[] = 'Last name is too long.';

        // Email format
        if (empty($old['email'])) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Password strength requirements
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        // Confirm password match
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // --- Database uniqueness check ---
        if (empty($errors)) {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$old['email']]);

            if ($stmt->fetch()) {
                // Email already exists — use generic message to avoid account enumeration
                $errors[] = 'An account with that email already exists.';
            }
        }

        // --- Create the account if no errors ---
        if (empty($errors)) {
            // Hash the password with bcrypt, cost factor 12
            // Higher cost = more computation required (slows brute-force attacks)
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $insert = $db->prepare('
                INSERT INTO users (first_name, last_name, email, password_hash, role)
                VALUES (?, ?, ?, ?, \'user\')
            ');
            $insert->execute([
                $old['first_name'],
                $old['last_name'],
                $old['email'],
                $passwordHash
            ]);

            // Redirect to login page with a success flag
            redirect(BASE_URL . '/login.php?registered=1');
        }
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Join Golden Girl</span>
        <h1>Create Your Account</h1>
        <p>Free to join. No obligations. Just support.</p>
    </div>
</section>

<!-- ── REGISTRATION FORM ──────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="form-card" style="max-width:560px;">

            <!-- Display all validation errors at the top -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <?php if (count($errors) === 1): ?>
                        <?= sanitize($errors[0]) ?>
                    <?php else: ?>
                        <strong>Please fix the following:</strong>
                        <ul style="margin-top:0.5rem; padding-left:1.2rem;">
                            <?php foreach ($errors as $err): ?>
                                <li><?= sanitize($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register.php" id="register-form" novalidate>
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Name row — side by side on wider screens -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text"
                               id="first_name"
                               name="first_name"
                               autocomplete="given-name"
                               required
                               maxlength="80"
                               value="<?= sanitize($old['first_name'] ?? '') ?>"
                               placeholder="Jane">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text"
                               id="last_name"
                               name="last_name"
                               autocomplete="family-name"
                               required
                               maxlength="80"
                               value="<?= sanitize($old['last_name'] ?? '') ?>"
                               placeholder="Smith">
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           autocomplete="email"
                           required
                           value="<?= sanitize($old['email'] ?? '') ?>"
                           placeholder="you@example.com">
                </div>

                <!-- Password with strength meter -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="new-password"
                           required
                           placeholder="Create a strong password">
                    <!-- Visual strength indicator — animated by main.js -->
                    <div class="password-strength">
                        <div class="strength-bar"><div class="strength-fill"></div></div>
                        <span class="strength-text"></span>
                    </div>
                    <small style="color:var(--text-light); font-size:0.8rem; display:block; margin-top:0.3rem;">
                        At least 8 characters with uppercase, a number, and a special character.
                    </small>
                </div>

                <!-- Confirm password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           autocomplete="new-password"
                           required
                           placeholder="Repeat your password">
                </div>

                <!-- Privacy notice -->
                <p style="font-size:0.82rem; color:var(--text-light); margin-bottom:1.2rem;">
                    By creating an account, you agree to our
                    <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.
                    We will never sell your information.
                </p>

                <!-- Submit -->
                <button type="submit" class="btn btn-warm btn-block btn-lg">Create My Account</button>
            </form>

            <div class="divider mt-3">already have an account?</div>

            <p class="text-center mt-2" style="font-size:0.95rem;">
                <a href="<?= BASE_URL ?>/login.php" style="font-weight:700;">Sign In</a>
            </p>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>