<?php
// ============================================================
// account.php — Logged-In User Account Dashboard
// Shows the user their profile, past service requests, etc.
// Protected by requireLogin() — redirects guests to login.
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();
requireLogin(); // Redirect to login if not authenticated

$db = getDB();

// ── Fetch user's appointment / service requests ────────────────
$stmt = $db->prepare('
    SELECT id, services, notes, status, created_at
    FROM   appointments
    WHERE  user_id = ?
    ORDER  BY created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Welcome Back</span>
        <!-- Greet the user by their first name from the session -->
        <h1>Hi, <?= sanitize($_SESSION['user_first_name']) ?> ✦</h1>
        <p>Here's your Golden Girl account dashboard.</p>
    </div>
</section>

<!-- ── ACCOUNT CONTENT ────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="account-layout">

            <!-- ── Sidebar navigation ───────────────────────── -->
            <aside class="account-sidebar">
                <h4>My Account</h4>
                <ul class="account-nav">
                    <li><a href="#requests"  class="active">My Requests</a></li>
                    <li><a href="<?= BASE_URL ?>/schedule.php">Book a Service</a></li>
                    <!-- Placeholder for future profile edit page -->
                    <li><a href="#">Profile Settings</a></li>
                    <li><a href="<?= BASE_URL ?>/logout.php" style="color:#ef4444;">Sign Out</a></li>
                </ul>

                <?php if (isAdmin()): ?>
                    <!-- Extra admin links in the sidebar -->
                    <h4 style="margin-top:1.5rem;">Admin</h4>
                    <ul class="account-nav">
                        <li><a href="<?= BASE_URL ?>/blog-create.php">New Blog Post</a></li>
                        <li><a href="<?= BASE_URL ?>/blog.php">Manage Blog</a></li>
                    </ul>
                <?php endif; ?>
            </aside>

            <!-- ── Main panel ────────────────────────────────── -->
            <div>

                <!-- Service Requests section -->
                <div class="account-panel" id="requests">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                        <h2 style="margin:0;">My Service Requests</h2>
                        <a href="<?= BASE_URL ?>/schedule.php" class="btn btn-warm">+ New Request</a>
                    </div>

                    <?php if (empty($appointments)): ?>
                        <!-- Empty state -->
                        <div style="text-align:center; padding:2.5rem; color:var(--text-mid);">
                            <div style="font-size:2.5rem; margin-bottom:1rem;">📋</div>
                            <h3 style="font-size:1.1rem;">No requests yet</h3>
                            <p>When you schedule a consultation or service, it will appear here.</p>
                            <a href="<?= BASE_URL ?>/schedule.php" class="btn btn-outline mt-2">Schedule Something</a>
                        </div>
                    <?php else: ?>
                        <!-- List of appointment requests -->
                        <div class="appointments-list">
                            <?php foreach ($appointments as $appt): ?>
                                <div class="appt-item">
                                    <div>
                                        <!-- Services requested -->
                                        <div class="appt-services">
                                            <?= sanitize($appt['services']) ?>
                                        </div>
                                        <!-- User's notes if any -->
                                        <?php if ($appt['notes']): ?>
                                            <div style="font-size:0.85rem; color:var(--text-mid); margin-top:0.3rem; max-width:480px;">
                                                <?= sanitize(substr($appt['notes'], 0, 120)) ?>
                                                <?= strlen($appt['notes']) > 120 ? '…' : '' ?>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Date submitted -->
                                        <div class="appt-date">
                                            Submitted <?= date('M j, Y', strtotime($appt['created_at'])) ?>
                                        </div>
                                    </div>

                                    <!-- Status badge — CSS classes match status values in DB -->
                                    <span class="badge status-<?= sanitize($appt['status']) ?>">
                                        <?= ucfirst(sanitize($appt['status'])) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick tips panel -->
                <div class="account-panel mt-3" style="background:var(--warm-light);">
                    <h3 style="margin-bottom:0.8rem;">💛 What's Next?</h3>
                    <p style="color:var(--text-mid); font-size:0.95rem; margin:0;">
                        After submitting a service request, check your email for a confirmation
                        and next steps. If you haven't booked your calendar appointment yet,
                        <a href="<?= BASE_URL ?>/schedule.php">click here to do so now</a>.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>