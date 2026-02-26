<?php
// ============================================================
// admin/toggle-post.php — Admin: Toggle Blog Post Visibility
// Flips a post's is_published status (draft ↔ published).
// Security:
//   - requireAdmin() guard
//   - CSRF token verified via GET param
//   - Post ID validated as integer
// ============================================================

// dirname(__DIR__) resolves to the project root reliably on Windows
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

startSecureSession();
requireAdmin(); // Only admins may toggle posts

// Validate the CSRF token passed in the URL
if (!verifyCsrfToken($_GET['csrf'] ?? '')) {
    http_response_code(403); // Forbidden
    die('Invalid request. Please go back and try again.');
}

// Validate and sanitize the post ID
$postId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$postId) {
    redirect(BASE_URL . '/blog.php');
}

$db = getDB();

// Toggle: flip 0→1 or 1→0 using MySQL's ABS(is_published - 1)
$stmt = $db->prepare('
    UPDATE blog_posts
    SET    is_published = ABS(is_published - 1)
    WHERE  id = ?
');
$stmt->execute([$postId]);

// Redirect back to the blog listing
redirect(BASE_URL . '/blog.php?toggled=1');