<?php
// ============================================================
// includes/header.php
// Shared HTML <head> and navigation bar included on every page.
// Expects $pageTitle to be set before including this file.
// ============================================================

// Load auth helpers if not already loaded.
// dirname(__DIR__) explicitly resolves to the project root folder,
// which is more reliable than '/../' on Windows file systems.
require_once dirname(__DIR__) . '/config/auth.php';
startSecureSession(); // Initialize secure session on every page

// Default page title if none is provided by the calling page
$pageTitle = $pageTitle ?? 'Golden Girl';

// Determine the current page filename (e.g. "blog.php") so we
// can highlight the active nav link with the "active" CSS class.
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Character encoding must be declared early -->
    <meta charset="UTF-8">

    <!-- Responsive viewport for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Security: prevent MIME-type sniffing -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">

    <!-- Security: restrict what resources can be loaded (Content Security Policy) -->
    <!-- Adjust 'assets.calendly.com' etc. as needed for third-party embeds -->
    <meta http-equiv="Content-Security-Policy"
          content="default-src 'self';
                   script-src 'self' 'unsafe-inline' https://assets.calendly.com;
                   style-src  'self' 'unsafe-inline' https://assets.calendly.com https://fonts.googleapis.com;
                   font-src   'self' https://fonts.gstatic.com;
                   frame-src  https://calendly.com;
                   img-src    'self' data:;">

    <!-- SEO description -->
    <meta name="description" content="Golden Girl — Real estate services and insurance policies tailored for women starting a new chapter.">

    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Goulden Girl" />
    <link rel="manifest" href="/img/site.webmanifest" />

    <title><?= sanitize($pageTitle) ?> | Golden Girl</title>

    <!-- Google Fonts: warm and professional pairing -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Main stylesheet — BASE_URL prefixes the subfolder path for XAMPP -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<!-- ===================== NAVIGATION BAR ===================== -->
<header class="site-header">
    <div class="container nav-container">

        <!-- Site logo — links to the homepage root (no /index in the URL) -->
        <a href="<?= BASE_URL ?>/" class="logo" aria-label="Golden Girl Home">
            <span class="logo-icon">✦</span>
            <span class="logo-text">Golden Girl</span>
        </a>

        <!-- Hamburger button for mobile — toggled via JS -->
        <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <!-- Main navigation links -->
        <nav class="main-nav" id="main-nav" role="navigation" aria-label="Main menu">
            <ul>
                <!-- Home link points to the root directory, not /index.
                     Active class is applied when the current file is index.php -->
                <li>
                    <a href="<?= BASE_URL ?>/"
                       <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>
                        Home
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/blog"
                       <?= $currentPage === 'blog.php' ? 'class="active"' : '' ?>>
                        Blog
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/schedule"
                       <?= $currentPage === 'schedule.php' ? 'class="active"' : '' ?>>
                        Schedule
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/about"
                       <?= $currentPage === 'about.php' ? 'class="active"' : '' ?>>
                        About
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <!-- Show account links only when logged in -->
                    <?php if (isAdmin()): ?>
                        <!-- Extra admin link for blog management -->
                        <li><a href="<?= BASE_URL ?>/blog-create">New Post</a></li>
                    <?php endif; ?>
                    <li><a href="<?= BASE_URL ?>/account">My Account</a></li>
                    <li><a href="<?= BASE_URL ?>/logout" class="btn btn-outline-nav">Sign Out</a></li>
                <?php else: ?>
                    <!-- Guest links -->
                    <li><a href="<?= BASE_URL ?>/login">Sign In</a></li>
                    <li><a href="<?= BASE_URL ?>/register" class="btn btn-nav">Get Started</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</header>
<!-- =========================================================== -->

<!-- Page content starts here — each page fills this area -->
<main id="main-content">
