<?php
// ============================================================
// blog-create.php — Admin Blog Post Editor (Create & Edit)
// Restricted to admins only.
// Handles both creating new posts and editing existing ones.
// Security:
//   - requireAdmin() blocks non-admins
//   - CSRF token on form
//   - Prepared statements for all DB writes
//   - Slug auto-generated and deduplicated
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

startSecureSession();
requireAdmin(); // Redirect non-admins with 403

$db     = getDB();
$errors = [];
$success= '';
$post   = null; // Will hold existing post data if editing

// ── Check if editing an existing post ─────────────────────────
$editId = filter_var($_GET['edit'] ?? '', FILTER_VALIDATE_INT);

if ($editId) {
    // Fetch the existing post to pre-fill the form
    $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $post = $stmt->fetch();

    if (!$post) {
        redirect(BASE_URL . '/blog.php'); // Post not found — go back to blog
    }
}

// ── Handle POST (save / update) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {

        // Collect and trim inputs
        $title     = trim($_POST['title']   ?? '');
        $body      = trim($_POST['body']    ?? '');
        $excerpt   = trim($_POST['excerpt'] ?? '');
        $published = isset($_POST['is_published']) ? 1 : 0;
        $postId    = filter_var($_POST['post_id'] ?? '', FILTER_VALIDATE_INT);

        // Validate required fields
        if (empty($title)) $errors[] = 'A title is required.';
        if (empty($body))  $errors[] = 'Post content is required.';
        if (strlen($title) > 255) $errors[] = 'Title is too long (max 255 characters).';

        if (empty($errors)) {

            // ── Generate a URL-friendly slug from the title ────────
            // e.g. "My First Post!" → "my-first-post"
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

            if ($postId) {
                // EDIT: Keep the existing slug to avoid breaking URLs,
                // but allow updating if the title changed significantly
                $existingSlug = $post['slug'];

                // Check for slug conflicts with OTHER posts (not this one)
                $slugCheck = $db->prepare('SELECT id FROM blog_posts WHERE slug = ? AND id != ? LIMIT 1');
                $slugCheck->execute([$slug, $postId]);
                if ($slugCheck->fetch()) {
                    // Append the post ID to make slug unique
                    $slug = $slug . '-' . $postId;
                }

                // UPDATE existing post
                $stmt = $db->prepare('
                    UPDATE blog_posts
                    SET    title = ?, slug = ?, body = ?, excerpt = ?, is_published = ?
                    WHERE  id = ? AND author_id = ?
                ');
                $stmt->execute([$title, $slug, $body, $excerpt, $published, $postId, $_SESSION['user_id']]);
                $success = 'Post updated successfully.';

                // Refresh $post for the form
                $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
                $stmt->execute([$postId]);
                $post = $stmt->fetch();

            } else {
                // CREATE: Ensure slug uniqueness
                $slugCheck = $db->prepare('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1');
                $slugCheck->execute([$slug]);
                if ($slugCheck->fetch()) {
                    // Append a short timestamp suffix to guarantee uniqueness
                    $slug = $slug . '-' . time();
                }

                // INSERT new post
                $stmt = $db->prepare('
                    INSERT INTO blog_posts (author_id, title, slug, body, excerpt, is_published)
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$_SESSION['user_id'], $title, $slug, $body, $excerpt, $published]);

                $newId   = $db->lastInsertId(); // Get the new row's ID
                $success = 'Post created! ';

                if ($published) {
                    redirect(BASE_URL . '/blog-post.php?slug=' . urlencode($slug));
                } else {
                    // Stay on editor for drafts — load new post into form
                    redirect(BASE_URL . '/blog-create.php?edit=' . $newId . '&saved=1');
                }
            }
        }
    }
}

// Pre-populate form from $post (editing) or $_POST (failed submission)
$formTitle   = sanitize($_POST['title']   ?? ($post['title']   ?? ''));
$formBody    =          $_POST['body']    ?? ($post['body']    ?? '');
$formExcerpt = sanitize($_POST['excerpt'] ?? ($post['excerpt'] ?? ''));
$formPub     = isset($_POST['is_published']) ? (bool)$_POST['is_published'] : (bool)($post['is_published'] ?? false);
$postId      = $post['id'] ?? null;

$pageTitle = $postId ? 'Edit Post' : 'New Post';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── PAGE HERO ─────────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <span class="section-label" style="color:rgba(255,255,255,0.7);">Admin</span>
        <h1><?= $postId ? 'Edit Post' : 'New Blog Post' ?></h1>
    </div>
</section>

<!-- ── EDITOR ────────────────────────────────────────────────── -->
<section class="section">
    <div class="container" style="max-width:820px;">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $e): ?><p><?= sanitize($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" data-auto-dismiss role="alert"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <div class="form-card" style="max-width:100%;">
            <form method="POST" action="<?= BASE_URL ?>/blog-create.php" novalidate>
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Hidden field carries post ID for edits -->
                <?php if ($postId): ?>
                    <input type="hidden" name="post_id" value="<?= (int)$postId ?>">
                <?php endif; ?>

                <!-- Title -->
                <div class="form-group">
                    <label for="title">Post Title <span style="color:var(--warm);">*</span></label>
                    <input type="text"
                           id="title"
                           name="title"
                           required
                           maxlength="255"
                           value="<?= $formTitle ?>"
                           placeholder="Enter a compelling title...">
                </div>

                <!-- Excerpt / Summary -->
                <div class="form-group">
                    <label for="excerpt">Excerpt <small style="font-weight:400; color:var(--text-light);">(shown on the blog listing page)</small></label>
                    <textarea id="excerpt"
                              name="excerpt"
                              maxlength="500"
                              style="min-height:80px;"
                              placeholder="A short 1-2 sentence summary of this post..."><?= $formExcerpt ?></textarea>
                </div>

                <!-- Body / Main content -->
                <div class="form-group">
                    <label for="body">Post Content <span style="color:var(--warm);">*</span></label>
                    <!-- Basic textarea — replace with a rich-text editor (TinyMCE etc.) for production -->
                    <textarea id="body"
                              name="body"
                              class="editor-area"
                              required
                              placeholder="Write your full post here. Basic HTML is supported (e.g. <p>, <strong>, <h2>, <ul>)."><?= htmlspecialchars($formBody, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <small style="color:var(--text-light);">Basic HTML tags are supported. Tip: wrap paragraphs in &lt;p&gt; tags.</small>
                </div>

                <!-- Publish toggle -->
                <div class="form-group">
                    <label class="checkbox-item" style="cursor:pointer;">
                        <input type="checkbox"
                               name="is_published"
                               value="1"
                               <?= $formPub ? 'checked' : '' ?>>
                        <span><strong>Publish this post</strong> — make it visible to the public</span>
                    </label>
                </div>

                <!-- Action buttons -->
                <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-warm">
                        <?= $postId ? 'Save Changes' : 'Create Post' ?>
                    </button>
                    <?php if ($postId): ?>
                        <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn-outline" target="_blank">Preview →</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/blog.php" style="color:var(--text-mid); font-size:0.9rem;">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>