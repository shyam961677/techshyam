<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$blogs  = readJson('blogs.json') ?? [];
$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$edit   = null;

if ($action === 'new' || $action === 'edit') {
    if ($editId) {
        foreach ($blogs as $b) { if ((int)$b['id'] === $editId) { $edit = $b; break; } }
    }
}

// State changes require POST and a valid admin form token.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['_action'] ?? '', ['delete', 'toggle'], true)) {
    verifyCsrfToken();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['_action'] ?? '') === 'delete') {
        $blogs = array_values(array_filter($blogs, fn($b) => (int)$b['id'] !== $id));
        writeJson('blogs.json', $blogs);
        setFlash('success', 'Blog post deleted.');
    } else {
        foreach ($blogs as &$b) {
            if ((int)$b['id'] === $id) { $b['published'] = !($b['published'] ?? false); break; }
        }
        unset($b);
        writeJson('blogs.json', $blogs);
    }
    header('Location: ' . adminUrl('blogs'));
    exit;
}

// Save (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id      = (int)($_POST['id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $cat     = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tags    = array_map('trim', explode(',', $_POST['tags'] ?? ''));
    $pub     = !empty($_POST['published']);
    $date    = $_POST['date'] ?? date('Y-m-d');

    if ($slug === '') $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));

    // Handle image
    $image = $_POST['existing_image'] ?? '';
    $uploaded = handleImageUpload('image', 'blog');
    if ($uploaded) $image = $uploaded;

    if ($id > 0) {
        // Update
        foreach ($blogs as &$b) {
            if ((int)$b['id'] === $id) {
                $b['title']   = $title;
                $b['slug']    = $slug;
                $b['category']= $cat;
                $b['excerpt'] = $excerpt;
                $b['content'] = $content;
                $b['tags']    = $tags;
                $b['published']= $pub;
                $b['date']    = $date;
                $b['image']   = $image;
                break;
            }
        }
        unset($b);
        setFlash('success', 'Blog post updated.');
    } else {
        // New
        $blogs[] = [
            'id'       => nextId($blogs),
            'title'    => $title,
            'slug'     => $slug,
            'category' => $cat,
            'image'    => $image,
            'excerpt'  => $excerpt,
            'content'  => $content,
            'tags'     => $tags,
            'author'   => $_SESSION['admin_name'] ?? 'Admin',
            'date'     => $date,
            'published'=> $pub,
            'views'    => 0,
        ];
        setFlash('success', 'Blog post created.');
    }
    writeJson('blogs.json', $blogs);
    header('Location: ' . adminUrl('blogs'));
    exit;
}

$pageTitle  = 'Blog Management';
$activePage = 'blogs';
include __DIR__ . '/includes/layout.php';
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- Blog Form -->
<div style="margin-bottom:16px">
    <a href="<?= e(adminUrl('blogs')) ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Posts</a>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-<?= $edit ? 'pencil' : 'plus-lg' ?>"></i> <?= $edit ? 'Edit Post' : 'New Blog Post' ?></span>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="_action" value="save">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <input type="hidden" name="existing_image" value="<?= e($edit['image'] ?? '') ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required
                       value="<?= e($edit['title'] ?? '') ?>" placeholder="Post title">
            </div>
            <div class="form-group">
                <label class="form-label">Slug (URL)</label>
                <input type="text" name="slug" class="form-control"
                       value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto-generated-from-title">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control"
                       value="<?= e($edit['category'] ?? '') ?>" placeholder="PHP, Laravel, API…">
            </div>
            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control"
                       value="<?= e($edit['date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Cover Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if (!empty($edit['image'])): ?>
                    <img src="../<?= e($edit['image']) ?>" style="height:60px;margin-top:8px;border-radius:6px;object-fit:cover">
                <?php endif; ?>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Tags (comma-separated)</label>
                <input type="text" name="tags" class="form-control"
                       value="<?= e(implode(', ', $edit['tags'] ?? [])) ?>" placeholder="php, laravel, api">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="2"
                          placeholder="Short summary for listing pages"><?= e($edit['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-control" rows="10"
                          placeholder="Write your blog post here... (HTML supported)"><?= e($edit['content'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="published" value="1" <?= !empty($edit['published']) ? 'checked' : '' ?>>
                    Publish immediately
                </label>
            </div>
        </div>
        <div class="modal-footer" style="padding-top:16px;margin-top:0;border:none;justify-content:flex-start">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Post</button>
            <a href="<?= e(adminUrl('blogs')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Blog List -->
<div style="display:flex;justify-content:flex-end;margin-bottom:20px">
    <a href="<?= e(adminUrl('blogs')) ?>?action=new" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Post</a>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-journal-text"></i> All Posts (<?= count($blogs) ?>)</span>
    </div>
    <?php if (empty($blogs)): ?>
        <p style="color:var(--muted);text-align:center;padding:40px 0">No blog posts yet. <a href="<?= e(adminUrl('blogs')) ?>?action=new" style="color:var(--primary)">Create one.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Views</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach (array_reverse($blogs) as $b): ?>
            <tr>
                <td>
                    <div style="font-weight:600;font-size:0.85rem"><?= e($b['title']) ?></div>
                    <div style="font-size:0.75rem;color:var(--muted)"><?= e($b['slug']) ?></div>
                </td>
                <td><span class="badge badge-info"><?= e($b['category'] ?? '—') ?></span></td>
                <td style="color:var(--muted);font-size:0.8rem"><?= e($b['date'] ?? '—') ?></td>
                <td style="color:var(--muted)"><?= (int)($b['views'] ?? 0) ?></td>
                <td>
                    <form method="POST" action="<?= e(adminUrl('blogs')) ?>">
                        <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="toggle"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                        <button type="submit" style="border:0;background:transparent;padding:0;cursor:pointer" aria-label="Toggle publication status">
                            <?= !empty($b['published']) ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-muted">Draft</span>' ?>
                        </button>
                    </form>
                </td>
                <td>
                    <div style="display:flex;gap:4px">
                        <a href="<?= e(adminUrl('blogs')) ?>?action=edit&id=<?= (int)$b['id'] ?>" class="btn btn-secondary btn-xs" aria-label="Edit post"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= e(adminUrl('blogs')) ?>" data-confirm="Delete this post?">
                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete post"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
