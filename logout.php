<?php
// ============================================================
// logout.php — Logs the user out and redirects to home.
// No HTML output — purely a redirect handler.
// ============================================================

require_once __DIR__ . '/config/auth.php';
startSecureSession();

// Destroy session data and cookie
logoutUser();

// Redirect to home page after logout
redirect(BASE_URL . '/index.php');