<?php
/**
 * GripNews Blog Admin Panel
 * URL: /blog-admin
 * Features: Login, Dashboard, Create/Edit/Delete posts, TinyMCE editor
 */
session_start();
require_once __DIR__ . '/blog-config.php';

// Auto-setup database tables
blog_db_setup();

// ── Auth ──
$action = $_GET['action'] ?? 'dashboard';
$logged_in = !empty($_SESSION['blog_admin']);

if ($action === 'logout') {
    session_destroy();
    header('Location: /blog-admin');
    exit;
}

if (!$logged_in && $action !== 'login') {
    $action = 'login';
}

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $pass = $_POST['password'] ?? '';
    if (blog_admin_verify_password($pass)) {
        $_SESSION['blog_admin'] = true;
        $_SESSION['blog_admin_time'] = time();
        header('Location: /blog-admin');
        exit;
    }
    $login_error = 'Invalid password.';
}

// Handle change password POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change-password' && $logged_in) {
    $current = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!blog_admin_verify_password($current)) {
        $pw_error = 'Current password is incorrect.';
    } elseif (strlen($new_pass) < 6) {
        $pw_error = 'New password must be at least 6 characters.';
    } elseif ($new_pass !== $confirm) {
        $pw_error = 'Passwords do not match.';
    } else {
        blog_admin_change_password($new_pass);
        $pw_success = 'Password changed successfully!';
    }
}

// ── CRUD Operations ──
$db = blog_db();
$msg = '';

// Save post (create or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit']) && $logged_in) {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: blog_make_slug($title);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = trim($_POST['category'] ?? 'article');
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $author = trim($_POST['author'] ?? 'GripAi');
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : ($status === 'published' ? date('Y-m-d H:i:s') : null);

    if (empty($title)) {
        $msg = '❌ Title is required.';
    } else {
        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE posts SET title=?, slug=?, excerpt=?, content=?, category=?, status=?, author=?, published_at=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $status, $author, $published_at, $id]);
                $msg = '✅ Post updated!';
            } else {
                $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, content, category, status, author, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $status, $author, $published_at]);
                $id = $db->lastInsertId();
                $msg = '✅ Post created!';
            }
            header("Location: /blog-admin?action=edit&id={$id}&saved=1");
            exit;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = '❌ A post with that slug already exists.';
            } else {
                $msg = '❌ Error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Delete post
if ($action === 'delete' && $logged_in) {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0 && ($_GET['confirm'] ?? '') === '1') {
        $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
        header('Location: /blog-admin?deleted=1');
        exit;
    }
}

// ── Fetch data for views ──
$post = null;
if ($action === 'edit' && $logged_in) {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /blog-admin'); exit; }
}

if ($action === 'delete' && $logged_in && ($_GET['confirm'] ?? '') !== '1') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /blog-admin'); exit; }
}

if ($_GET['saved'] ?? '') $msg = '✅ Post saved successfully!';
if ($_GET['deleted'] ?? '') $msg = '✅ Post deleted.';

?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Blog Admin — GripNews</title>
<?php if (in_array($action, ['create', 'edit']) && $logged_in): ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
.ql-toolbar.ql-snow { background: var(--card); border-color: var(--border) !important; }
.ql-container.ql-snow { border-color: var(--border) !important; min-height: 400px; }
.ql-editor { color: var(--text); font-size: 0.95rem; line-height: 1.7; min-height: 400px; }
.ql-editor.ql-blank::before { color: var(--dim); }
.ql-snow .ql-stroke { stroke: var(--dim) !important; }
.ql-snow .ql-fill { fill: var(--dim) !important; }
.ql-snow .ql-picker-label { color: var(--dim) !important; }
.ql-snow .ql-picker-options { background: var(--card) !important; border-color: var(--border) !important; }
.ql-snow .ql-picker-item { color: var(--text) !important; }
.ql-snow .ql-active .ql-stroke { stroke: var(--accent) !important; }
.ql-snow .ql-active .ql-fill { fill: var(--accent) !important; }
.ql-snow .ql-active { color: var(--accent) !important; }
.ql-snow a { color: var(--accent) !important; }
</style>
<?php endif; ?>
<style>
:root {
    --bg: #0a0e17;
    --card: #111827;
    --border: rgba(255,255,255,0.08);
    --text: #e5e7eb;
    --dim: #9ca3af;
    --accent: #3b82f6;
    --accent-hover: #2563eb;
    --green: #10b981;
    --red: #ef4444;
    --yellow: #f59e0b;
    --purple: #8b5cf6;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

/* Login */
.login-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
.login-box { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; }
.login-box h1 { font-size: 1.4rem; margin-bottom: 8px; }
.login-box .subtitle { color: var(--dim); font-size: 0.85rem; margin-bottom: 24px; }
.login-box input[type="password"] { width: 100%; padding: 12px 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.95rem; margin-bottom: 16px; }
.login-box input:focus { outline: none; border-color: var(--accent); }

/* Layout */
.admin-header { background: var(--card); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.admin-header .logo { font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
.admin-header .logo span { background: var(--accent); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
.admin-header nav { display: flex; gap: 8px; align-items: center; }
.admin-header nav a { color: var(--dim); text-decoration: none; font-size: 0.85rem; padding: 6px 12px; border-radius: 6px; transition: all 0.2s; }
.admin-header nav a:hover { background: rgba(255,255,255,0.05); color: var(--text); }
.admin-header nav a.active { background: rgba(59,130,246,0.15); color: var(--accent); }

.admin-body { max-width: 1200px; margin: 0 auto; padding: 24px; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-success { background: var(--green); color: #fff; }
.btn-success:hover { background: #059669; }
.btn-danger { background: var(--red); color: #fff; }
.btn-danger:hover { background: #dc2626; }
.btn-ghost { background: rgba(255,255,255,0.05); color: var(--dim); border: 1px solid var(--border); }
.btn-ghost:hover { background: rgba(255,255,255,0.1); color: var(--text); }
.btn-sm { padding: 6px 12px; font-size: 0.8rem; }

/* Alert */
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
.alert-success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: var(--green); }
.alert-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: var(--red); }

/* Dashboard stats */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; }
.stat-card .num { font-size: 2rem; font-weight: 700; color: var(--accent); }
.stat-card .label { font-size: 0.8rem; color: var(--dim); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

/* Table */
.posts-table { width: 100%; border-collapse: collapse; background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.posts-table th { text-align: left; padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--dim); background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border); }
.posts-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
.posts-table tr:last-child td { border-bottom: none; }
.posts-table tr:hover td { background: rgba(255,255,255,0.02); }
.posts-table .title-cell { max-width: 350px; }
.posts-table .title-cell a { color: var(--text); text-decoration: none; font-weight: 500; }
.posts-table .title-cell a:hover { color: var(--accent); }
.posts-table .slug-cell { color: var(--dim); font-size: 0.8rem; font-family: monospace; }

.badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.3px; }
.badge-published { background: rgba(16,185,129,0.2); color: var(--green); }
.badge-draft { background: rgba(245,158,11,0.2); color: var(--yellow); }

.actions-cell { display: flex; gap: 6px; }

/* Form */
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--dim); }
.form-group input[type="text"],
.form-group input[type="datetime-local"],
.form-group select,
.form-group textarea { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.9rem; font-family: inherit; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--accent); }
.form-group textarea { min-height: 120px; resize: vertical; }
.form-group .hint { font-size: 0.75rem; color: var(--dim); margin-top: 4px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }

.form-actions { display: flex; gap: 12px; align-items: center; padding-top: 20px; border-top: 1px solid var(--border); }

/* Delete confirm */
.delete-box { background: var(--card); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 32px; max-width: 500px; margin: 40px auto; text-align: center; }
.delete-box h2 { color: var(--red); margin-bottom: 12px; }
.delete-box p { color: var(--dim); margin-bottom: 24px; }
.delete-box .actions { display: flex; gap: 12px; justify-content: center; }

/* Empty state */
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
.empty-state h2 { margin-bottom: 8px; }
.empty-state p { color: var(--dim); margin-bottom: 20px; }

/* Responsive */
@media (max-width: 768px) {
    .admin-header { flex-direction: column; gap: 8px; }
    .posts-table { font-size: 0.8rem; }
    .posts-table th, .posts-table td { padding: 8px 10px; }
}
</style>
</head>
<body>

<?php if ($action === 'login'): ?>
<!-- ═══ LOGIN ═══ -->
<div class="login-wrap">
    <div class="login-box">
        <h1>📰 Blog Admin</h1>
        <p class="subtitle">GripNews Content Management</p>
        <?php if (!empty($login_error)): ?>
            <div class="alert alert-error"><?= $login_error ?></div>
        <?php endif; ?>
        <form method="POST" action="/blog-admin?action=login">
            <input type="password" name="password" placeholder="Admin password" autofocus required>
            <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ═══ ADMIN HEADER ═══ -->
<header class="admin-header">
    <div class="logo">📰 GripNews <span>BLOG ADMIN</span></div>
    <nav>
        <a href="/blog-admin" class="<?= $action === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="/blog-admin?action=create" class="<?= $action === 'create' ? 'active' : '' ?>">+ New Post</a>
        <a href="/blog" target="_blank">View Blog ↗</a>
        <a href="/blog-admin?action=change-password" class="<?= $action === 'change-password' ? 'active' : '' ?>">⚙️</a>
        <a href="/blog-admin?action=logout" style="color:var(--red);">Logout</a>
    </nav>
</header>

<div class="admin-body">
<?php if ($msg): ?>
    <div class="alert <?= strpos($msg, '✅') !== false ? 'alert-success' : 'alert-error' ?>"><?= $msg ?></div>
<?php endif; ?>

<?php if ($action === 'dashboard'): ?>
<!-- ═══ DASHBOARD ═══ -->
<?php
    $total = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $published = $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
    $drafts = $db->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
    $posts = $db->query("SELECT id, title, slug, status, category, author, published_at, created_at, updated_at FROM posts ORDER BY created_at DESC LIMIT 50")->fetchAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:1.4rem;">Dashboard</h1>
    <a href="/blog-admin?action=create" class="btn btn-primary">+ New Post</a>
</div>

<div class="stats-row">
    <div class="stat-card"><div class="num"><?= $total ?></div><div class="label">Total Posts</div></div>
    <div class="stat-card"><div class="num" style="color:var(--green);"><?= $published ?></div><div class="label">Published</div></div>
    <div class="stat-card"><div class="num" style="color:var(--yellow);"><?= $drafts ?></div><div class="label">Drafts</div></div>
</div>

<?php if (empty($posts)): ?>
<div class="empty-state">
    <div class="icon">✍️</div>
    <h2>No posts yet</h2>
    <p>Create your first blog post to get started.</p>
    <a href="/blog-admin?action=create" class="btn btn-primary">+ Create Post</a>
</div>
<?php else: ?>
<table class="posts-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Category</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
        <tr>
            <td class="title-cell"><a href="/blog-admin?action=edit&id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></td>
            <td class="slug-cell"><?= htmlspecialchars($p['slug']) ?></td>
            <td><span class="badge badge-<?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span></td>
            <td style="font-size:0.8rem;color:var(--dim);"><?= htmlspecialchars($p['category']) ?></td>
            <td style="font-size:0.8rem;color:var(--dim);"><?= $p['published_at'] ? date('j M Y', strtotime($p['published_at'])) : date('j M Y', strtotime($p['created_at'])) ?></td>
            <td>
                <div class="actions-cell">
                    <a href="/blog-admin?action=edit&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <?php if ($p['status'] === 'published'): ?>
                        <a href="/blog/<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="btn btn-ghost btn-sm">View ↗</a>
                    <?php endif; ?>
                    <a href="/blog-admin?action=delete&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" style="color:var(--red);">Delete</a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
<!-- ═══ CREATE / EDIT POST ═══ -->
<?php
    $is_edit = $action === 'edit' && $post;
    $form_title = $is_edit ? htmlspecialchars($post['title']) : '';
    $form_slug = $is_edit ? htmlspecialchars($post['slug']) : '';
    $form_excerpt = $is_edit ? htmlspecialchars($post['excerpt'] ?? '') : '';
    $form_content = $is_edit ? ($post['content'] ?? '') : '';
    $form_category = $is_edit ? ($post['category'] ?? 'article') : 'article';
    $form_status = $is_edit ? ($post['status'] ?? 'draft') : 'draft';
    $form_author = $is_edit ? ($post['author'] ?? 'GripAi') : 'GripAi';
    $form_published = $is_edit && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:1.4rem;"><?= $is_edit ? 'Edit Post' : 'New Post' ?></h1>
    <a href="/blog-admin" class="btn btn-ghost">← Back</a>
</div>

<form method="POST" action="/blog-admin?action=<?= $action ?><?= $is_edit ? '&id='.$post['id'] : '' ?>">
    <?php if ($is_edit): ?><input type="hidden" name="id" value="<?= $post['id'] ?>"><?php endif; ?>

    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" value="<?= $form_title ?>" placeholder="Post title..." required id="post-title">
    </div>

    <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="<?= $form_slug ?>" placeholder="auto-generated-from-title" id="post-slug">
        <div class="hint">Leave empty to auto-generate from title. URL: /blog/your-slug</div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Category</label>
            <select name="category">
                <option value="article" <?= $form_category === 'article' ? 'selected' : '' ?>>Article</option>
                <option value="intel-digest" <?= $form_category === 'intel-digest' ? 'selected' : '' ?>>Intel Digest</option>
                <option value="daily-recap" <?= $form_category === 'daily-recap' ? 'selected' : '' ?>>Daily Recap</option>
                <option value="fix-guide" <?= $form_category === 'fix-guide' ? 'selected' : '' ?>>Fix Guide</option>
                <option value="guide" <?= $form_category === 'guide' ? 'selected' : '' ?>>Guide / Walkthrough</option>
                <option value="news" <?= $form_category === 'news' ? 'selected' : '' ?>>News</option>
                <option value="announcement" <?= $form_category === 'announcement' ? 'selected' : '' ?>>Announcement</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="draft" <?= $form_status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $form_status === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" value="<?= $form_author ?>">
        </div>
        <div class="form-group">
            <label>Publish Date</label>
            <input type="datetime-local" name="published_at" value="<?= $form_published ?>">
            <div class="hint">Leave empty to use current time when published</div>
        </div>
    </div>

    <div class="form-group">
        <label>Excerpt</label>
        <textarea name="excerpt" rows="3" placeholder="Short description for listing pages..."><?= $form_excerpt ?></textarea>
    </div>

    <div class="form-group">
        <label>Content</label>
        <div id="quill-editor"><?= $form_content ?></div>
        <textarea name="content" id="post-content" style="display:none;"><?= htmlspecialchars($form_content) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success">💾 <?= $is_edit ? 'Update Post' : 'Create Post' ?></button>
        <a href="/blog-admin" class="btn btn-ghost">Cancel</a>
        <?php if ($is_edit && $form_status === 'published'): ?>
            <a href="/blog/<?= $form_slug ?>" target="_blank" class="btn btn-ghost" style="margin-left:auto;">View Live ↗</a>
        <?php endif; ?>
    </div>
</form>

<script>
// Auto-generate slug from title
document.getElementById('post-title').addEventListener('input', function() {
    const slugField = document.getElementById('post-slug');
    if (!slugField.dataset.manual) {
        slugField.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
    }
});
document.getElementById('post-slug').addEventListener('input', function() {
    this.dataset.manual = '1';
});

// Quill editor init
if (typeof Quill !== 'undefined') {
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        },
        placeholder: 'Write your post content here...'
    });
    // Sync to hidden textarea on form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('post-content').value = quill.root.innerHTML;
    });
}
</script>

<?php elseif ($action === 'delete'): ?>
<!-- ═══ DELETE CONFIRM ═══ -->
<div class="delete-box">
    <h2>🗑️ Delete Post?</h2>
    <p>Are you sure you want to delete "<strong><?= htmlspecialchars($post['title']) ?></strong>"?<br>This cannot be undone.</p>
    <div class="actions">
        <a href="/blog-admin?action=delete&id=<?= $post['id'] ?>&confirm=1" class="btn btn-danger">Yes, Delete</a>
        <a href="/blog-admin" class="btn btn-ghost">Cancel</a>
    </div>
</div>

<?php elseif ($action === 'change-password'): ?>
<!-- ═══ CHANGE PASSWORD ═══ -->
<div style="max-width:400px;margin:40px auto;">
    <h1 style="font-size:1.4rem;margin-bottom:24px;">⚙️ Change Password</h1>
    <?php if (!empty($pw_error)): ?>
        <div class="alert alert-error"><?= $pw_error ?></div>
    <?php endif; ?>
    <?php if (!empty($pw_success)): ?>
        <div class="alert alert-success"><?= $pw_success ?></div>
    <?php endif; ?>
    <form method="POST" action="/blog-admin?action=change-password">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>

<?php endif; ?>
</div>

<?php endif; ?>
</body>
</html>
