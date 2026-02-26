<?php
// ============================================================
// blog.php — Blog Listing Page
// Shows published posts to all visitors.
// Admins see all posts (including drafts) with management links.
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();

$db = getDB();

// ── Fetch posts from DB ───────────────────────────────────────
if (isAdmin()) {
    // Admins see all posts (drafts and published)
    $stmt = $db->prepare('
        SELECT p.id, p.title, p.slug, p.excerpt, p.is_published, p.created_at,
               u.first_name, u.last_name
        FROM   blog_posts p
        JOIN   users u ON u.id = p.author_id
        ORDER  BY p.created_at DESC
    ');
    $stmt->execute();
} else {
    // Public sees only published posts
    $stmt = $db->prepare('
        SELECT p.id, p.title, p.slug, p.excerpt, p.is_published, p.created_at,
               u.first_name, u.last_name
        FROM   blog_posts p
        JOIN   users u ON u.id = p.author_id
        WHERE  p.is_published = 1
        ORDER  BY p.created_at DESC
    ');
    $stmt->execute();
}

$posts = $stmt->fetchAll(); // Get all matching rows as an array

$pageTitle = 'Blog';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Articles & Insights</span>
        <h1>The Golden Girl Blog</h1>
        <p>Practical advice and encouragement for your next chapter</p>
    </div>
</section>

<!-- ── BLOG CONTENT ──────────────────────────────────────────── -->
<section class="section">
    <div class="container">

        <!-- Admin action bar — only visible to admins -->
        <?php if (isAdmin()): ?>
            <div class="alert alert-info mb-2" style="display:flex; justify-content:space-between; align-items:center;">
                <span>👋 Admin view — you can see drafts and manage posts.</span>
                <a href="<?= BASE_URL ?>/blog-create.php" class="btn btn-warm">+ New Post</a>
            </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <!-- Empty state message when no posts exist yet -->
            <div class="text-center" style="padding:3rem 0; color:var(--text-mid);">
                <div style="font-size:3rem; margin-bottom:1rem;">✍️</div>
                <h3>No posts yet</h3>
                <p>Check back soon for articles, tips, and stories.</p>
                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/blog-create.php" class="btn btn-warm mt-2">Write the First Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <!-- Blog post card grid -->
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card">
                        <div class="blog-card-body">

                            <!-- Post date formatted nicely -->
                            <p class="post-date">
                                <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                <!-- Admin-only: show draft/published badge -->
                                <?php if (isAdmin()): ?>
                                    <span class="badge <?= $post['is_published'] ? 'badge-published' : 'badge-draft' ?>" style="margin-left:0.5rem;">
                                        <?= $post['is_published'] ? 'Published' : 'Draft' ?>
                                    </span>
                                <?php endif; ?>
                            </p>

                            <!-- Post title links to the full post -->
                            <h3>
                                <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>">
                                    <?= sanitize($post['title']) ?>
                                </a>
                            </h3>

                            <!-- Excerpt or auto-truncated title as fallback -->
                            <p>
                                <?= sanitize($post['excerpt'] ?: 'Read more about this topic...') ?>
                            </p>
                        </div>

                        <div class="blog-card-footer">
                            <!-- Author name -->
                            <small style="color:var(--text-light);">
                                By <?= sanitize($post['first_name'] . ' ' . $post['last_name']) ?>
                            </small>

                            <!-- Read more link -->
                            <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
                               style="font-size:0.88rem; font-weight:700; color:var(--teal);">
                                Read More →
                            </a>
                        </div>

                        <!-- Admin management buttons -->
                        <?php if (isAdmin()): ?>
                            <div style="padding:0.7rem 1.5rem; background:var(--warm-light); border-top:1px solid var(--border); display:flex; gap:0.8rem;">
                                <a href="<?= BASE_URL ?>/blog-create.php?edit=<?= $post['id'] ?>" style="font-size:0.82rem; color:var(--teal);">✏️ Edit</a>
                                <a href="<?= BASE_URL ?>/admin/toggle-post.php?id=<?= $post['id'] ?>&csrf=<?= generateCsrfToken() ?>"
                                   style="font-size:0.82rem; color:var(--warm-dark);"
                                   onclick="return confirm('Toggle publish status?');">
                                    <?= $post['is_published'] ? '📦 Unpublish' : '📢 Publish' ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>