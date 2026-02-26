<?php
// ============================================================
// config/auth.php
// Session management, CSRF protection, and auth helper
// functions used across the entire application.
// ============================================================

// ── Base URL ──────────────────────────────────────────────────
// Defined here (not database.php) because auth.php is included
// on EVERY page, including pages that don't use the database
// (e.g. index.php, about.php). This guarantees BASE_URL is
// always available before header.php tries to use it.
// Change to '' (empty string) when deployed to a web server root.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/golden-girl');
}

// --- Development error display ---
// Shows PHP errors on screen so blank white pages reveal their cause.
// IMPORTANT: Set both to 0 before going live on a real server.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Secure session configuration ---
// These must be set BEFORE session_start() is called

// Prevent JavaScript from reading the session cookie (XSS defense)
ini_set('session.cookie_httponly', '1');

// Mark cookie as secure when served over HTTPS
// Set to '1' when deploying to production with SSL
ini_set('session.cookie_secure', '0');

// Restrict cookie to same-site requests only (CSRF defense)
ini_set('session.cookie_samesite', 'Strict');

// Use cookies only — prevent session ID in URL (session hijacking defense)
ini_set('session.use_only_cookies', '1');

// Use strong session ID hashing
ini_set('session.hash_function', 'sha256');

/**
 * startSecureSession()
 * Initializes or resumes the session with security checks.
 * Called at the top of every page.
 */
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Begin the session
    }

    // Regenerate session ID periodically to prevent session fixation attacks
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true); // true = delete the old session file
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        // Regenerate every 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * generateCsrfToken()
 * Creates a random token tied to the current session.
 * Must be embedded in every form and verified on submission.
 * Prevents Cross-Site Request Forgery (CSRF) attacks.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes generates cryptographically secure random data
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * verifyCsrfToken($token)
 * Compares submitted token against the session token.
 * hash_equals() prevents timing-based attacks.
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * isLoggedIn()
 * Returns true if the current visitor has an active session.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * isAdmin()
 * Returns true only if the logged-in user has the admin role.
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * requireLogin()
 * Redirects to login page if the visitor is not authenticated.
 * Use at the top of any protected page.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        // BASE_URL prefixes the path so it works inside a subfolder on XAMPP
        header('Location: ' . BASE_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit; // Always exit after a redirect to stop further script execution
    }
}

/**
 * requireAdmin()
 * Redirects non-admins away from admin-only pages.
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403); // 403 Forbidden
        header('Location: ' . BASE_URL . '/index?error=unauthorized');
        exit;
    }
}

/**
 * loginUser($user)
 * Sets session variables after successful authentication.
 * Regenerates session ID to prevent session fixation.
 */
function loginUser(array $user): void {
    session_regenerate_id(true); // New session ID on privilege change (security best practice)
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['user_email']     = $user['email'];
    $_SESSION['user_first_name']= $user['first_name'];
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['last_regeneration'] = time();
}

/**
 * logoutUser()
 * Destroys the session completely and redirects to home.
 */
function logoutUser(): void {
    // Unset all session variables
    $_SESSION = [];

    // Delete the session cookie from the browser
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    // Destroy the session data on the server
    session_destroy();
}

/**
 * sanitize($input)
 * Escapes HTML special characters for safe output in HTML.
 * Always use this when echoing user-supplied data.
 * Prevents Cross-Site Scripting (XSS) attacks.
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * redirect($url)
 * Safe redirect helper that prevents header injection.
 */
function redirect(string $url): void {
    // Remove any newlines that could inject additional headers
    $url = str_replace(["\r", "\n"], '', $url);
    header('Location: ' . $url);
    exit;
}