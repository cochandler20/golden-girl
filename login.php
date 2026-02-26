<?php
// ============================================================
// login.php — User Login Page
// Security measures:
//   - CSRF token validation on form submission
//   - Passwords compared with password_verify() (timing-safe)
//   - Session regenerated on successful login
//   - Generic error messages (don't hint which field is wrong)
//   - Rate limiting via session-based attempt tracking
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();

// If already logged in, bounce to the account page — no need to log in again
if (isLoggedIn()) {
    redirect(BASE_URL . '/account.php');
}

$error   = ''; // Holds error message to display
$success = ''; // Holds success message (e.g., just registered)

// Show success flash if redirected here after registration
if (isset($_GET['registered'])) {
    $success = 'Account created! Please sign in below.';
}

// ── Handle POST form submission ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF validation ---
    // Reject the request if the token is missing or tampered
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';

    } else {

        // --- Simple login attempt rate limiting ---
        // Count failed attempts in the session; block after 5 in a row
        $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
        $_SESSION['login_block_until'] = $_SESSION['login_block_until'] ?? 0;

        if ($_SESSION['login_block_until'] > time()) {
            // Still within the lockout window
            $waitSeconds = ceil(($_SESSION['login_block_until'] - time()) / 60);
            $error = "Too many failed attempts. Please wait {$waitSeconds} minute(s) before trying again.";

        } else {

            // --- Sanitize inputs ---
            // trim() removes whitespace; filter_var validates email format
            $email    = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
            $password = $_POST['password'] ?? ''; // Do NOT sanitize the raw password before hashing

            // Basic presence check
            if (empty($email) || empty($password)) {
                $error = 'Please enter your email and password.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {

                // --- Database lookup via prepared statement ---
                // The ? placeholder is safe against SQL injection
                $db   = getDB();
                $stmt = $db->prepare('SELECT id, first_name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(); // Returns array or false

                // --- Verify password ---
                // password_verify() is timing-safe and handles bcrypt cost factors
                if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {

                    // Success — reset failed attempt counter
                    $_SESSION['login_attempts']  = 0;
                    $_SESSION['login_block_until'] = 0;

                    // Check if the stored hash needs to be upgraded
                    // (e.g., after a cost factor increase)
                    if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                        $update  = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                        $update->execute([$newHash, $user['id']]);
                    }

                    // Set session variables and regenerate session ID
                    loginUser($user);

                    // Redirect to intended page or default account page
                    $redirect = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
                    // Safety: only allow paths that start with BASE_URL (prevents open redirect attacks)
                    if (empty($redirect) || !str_starts_with($redirect, BASE_URL . '/')) {
                        $redirect = BASE_URL . '/account.php';
                    }
                    redirect($redirect);

                } else {
                    // Failed — increment counter
                    $_SESSION['login_attempts']++;

                    if ($_SESSION['login_attempts'] >= 5) {
                        // Lock out for 15 minutes after 5 failed attempts
                        $_SESSION['login_block_until'] = time() + (15 * 60);
                        $error = 'Too many failed attempts. Please wait 15 minutes before trying again.';
                    } else {
                        // Generic error — never reveal which field was wrong
                        $remaining = 5 - $_SESSION['login_attempts'];
                        $error = "Incorrect email or password. {$remaining} attempt(s) remaining.";
                    }
                }
            }
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Welcome Back</span>
        <h1>Sign In</h1>
        <p>Access your Golden Girl account</p>
    </div>
</section>

<!-- ── LOGIN FORM ─────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="form-card">

            <!-- Flash messages -->
            <?php if ($error):   ?><div class="alert alert-error"   role="alert"><?= sanitize($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success" role="alert" data-auto-dismiss><?= sanitize($success) ?></div><?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/login.php" novalidate>
                <!-- CSRF hidden field — must be present and match session token -->
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           autocomplete="email"
                           required
                           value="<?= sanitize($_POST['email'] ?? '') ?>"
                           placeholder="you@example.com">
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <label for="password">
                        Password
                        <!-- Forgot password placeholder link -->
                        <a href="#" style="float:right; font-weight:400; font-size:0.85rem;">Forgot password?</a>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="current-password"
                           required
                           placeholder="Your password">
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
            </form>

            <!-- Divider -->
            <div class="divider mt-3">or</div>

            <!-- Link to registration page -->
            <p class="text-center mt-2" style="font-size:0.95rem; color:var(--text-mid);">
                Don't have an account?
                <a href="<?= BASE_URL ?>/register.php" style="font-weight:700;">Create one — it's free</a>
            </p>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>