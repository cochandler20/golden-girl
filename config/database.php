<?php
// ============================================================
// config/database.php
// Establishes a secure PDO connection to MySQL.
// PDO is used instead of mysqli for its prepared-statement
// support, which prevents SQL injection attacks.
// ============================================================

// --- Database credentials ---
// Update these to match your XAMPP MySQL settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'golden_girl');
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASS', '');           // Default XAMPP MySQL password (empty)
define('DB_CHARSET', 'utf8mb4'); // Full Unicode support

/**
 * getDB()
 * Returns a singleton PDO instance so only one DB connection
 * is opened per request (performance + resource efficiency).
 */
function getDB(): PDO {
    static $pdo = null; // Static variable persists between calls within the same request

    if ($pdo === null) {
        // Build the DSN (Data Source Name) string
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            // Throw exceptions on DB errors instead of silently failing
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Return rows as associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Disable emulated prepares; use real server-side prepared statements
            // This is the most important SQL-injection defense
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real error server-side but never expose DB details to the browser
            error_log('Database connection failed: ' . $e->getMessage());
            // Show a generic user-facing message
            die('A database error occurred. Please try again later.');
        }
    }

    return $pdo;
}