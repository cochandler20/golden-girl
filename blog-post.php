<?php
// ============================================================
// blog-post.php — Individual Blog Post View
// Fetches a post by its URL slug.
// Only admins can view unpublished (draft) posts.
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();

$db = getDB();

// ── Get the slug from the URL query string ─────────────────────
// filter_var sanitizes the slug before using it in the query
$slug = filter_var($_GET['slug'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($slug)) {
    // No slug provided — redirect to blog listing
    redirect(BASE_URL . '/blog.php');
}

// ── Fetch the post by slug ─────────────────────────────────────
$stmt = $db->prepare('
    SELECT p.id, p.title, p.body, p.excerpt, p.is_published, p.created_at, p.updated_at,
           u.first_name, u.last_name
    FROM   blog_posts p
    JOIN   users u ON u.id = p.author_id
    WHERE  p.slug = ?
    LIMIT  1
');
$stmt->execute([$slug]);
$post = $stmt->fetch();

// ── Access control ─────────────────────────────────────────────
if (!$post) {
    // Post doesn't exist — 404
    http_response_code(404);
    $pageTitle = 'Post Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container text-center"><h2>Post Not Found</h2><p>This post may have been removed or the link is incorrect.</p><a href="<?= BASE_URL ?>/blog.php" class="btn btn-primary mt-2">Back to Blog</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Non-admin visitors cannot see drafts
if (!$post['is_published'] && !isAdmin()) {
    http_response_code(403);
    redirect(BASE_URL . '/blog.php');
}

$pageTitle = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── POST HERO ──────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container" style="max-width:800px; text-align:left;">
        <!-- Draft notice for admins -->
        <?php if (!$post['is_published']): ?>
            <span class="badge badge-draft" style="margin-bottom:1rem; display:inline-block;">⚠️ Draft — not visible to the public</span>
        <?php endif; ?>
        <h1 style="margin-bottom:0.6rem;"><?= sanitize($post['title']) ?></h1>
        <p style="color:rgba(255,255,255,0.7);">
            By <?= sanitize($post['first_name'] . ' ' . $post['last_name']) ?>
            &middot;
            <?= date('F j, Y', strtotime($post['created_at'])) ?>
        </p>
    </div>
</section>

<!-- ── POST BODY ──────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="post-body">

            <!-- Admin toolbar -->
            <?php if (isAdmin()): ?>
                <div style="display:flex; gap:1rem; margin-bottom:2rem; padding:1rem 1.2rem; background:var(--warm-light); border-radius:var(--radius); border:1px solid var(--border);">
                    <a href="<?= BASE_URL ?>/blog-create.php?edit=<?= $post['id'] ?>" class="btn btn-outline" style="font-size:0.88rem;">✏️ Edit Post</a>
                    <a href="<?= BASE_URL ?>/admin/toggle-post.php?id=<?= $post['id'] ?>&csrf=<?= generateCsrfToken() ?>"
                       class="btn btn-outline" style="font-size:0.88rem;"
                       onclick="return confirm('Toggle publish status?');">
                        <?= $post['is_published'] ? '📦 Unpublish' : '📢 Publish' ?>
                    </a>
                    <a href="<?= BASE_URL ?>/blog.php" style="font-size:0.88rem; align-self:center; color:var(--text-mid);">← Back to Blog</a>
                </div>
            <?php endif; ?>

            <!-- Post content —  stored HTML rendered here.
                 If admin-only content is entered, keep it basic.
                 For production, consider a sanitization library (HTMLPurifier). -->
            <div class="post-content">
                <?= $post['body'] ?>
                <!-- Note: $post['body'] is NOT escaped here because it is admin-entered HTML.
                     Only admins can create posts, so we trust the source.
                     If user-generated content is ever added, use HTMLPurifier to sanitize. -->
            </div>

            <!-- Bottom nav -->
            <div style="margin-top:3rem; padding-top:1.5rem; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                <a href="<?= BASE_URL ?>/blog.php" style="color:var(--teal); font-weight:700;">← Back to Blog</a>
                <a href="<?= BASE_URL ?>/schedule.php" class="btn btn-warm">Book a Consultation</a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>