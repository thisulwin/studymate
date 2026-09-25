<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$success = flash('admin_success');
$error = flash('admin_error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['delete_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            flash('admin_success', 'Category deleted');
        } else {
            flash('admin_error', 'Failed to delete category');
        }
        header("Location: categories.php");
        exit();
    }

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-book');
        $id = (int)($_POST['id'] ?? 0);

        if (empty($name)) {
            flash('admin_error', 'Category name is required');
        } elseif ($action === 'update' && $id <= 0) {
            flash('admin_error', 'Invalid category');
        } else {
            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $description, $icon);
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $description, $icon, $id);
            }
            if ($stmt->execute()) {
                flash('admin_success', $action === 'create' ? 'Category created successfully' : 'Category updated successfully');
            } else {
                flash('admin_error', 'Failed to save category');
            }
        }
        header("Location: categories.php");
        exit();
    }
}

$editCat = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editCat = $stmt->get_result()->fetch_assoc();
}

$categories = $conn->query("SELECT c.*,
    (SELECT COUNT(*) FROM departments WHERE category_id = c.id) as dept_count,
    (SELECT COUNT(*) FROM subjects s JOIN departments d ON s.department_id = d.id WHERE d.category_id = c.id) as subject_count,
    (SELECT COUNT(*) FROM notes n JOIN subjects s ON n.subject_id = s.id JOIN departments d ON s.department_id = d.id WHERE d.category_id = c.id) as note_count
    FROM categories c ORDER BY c.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/admin_nav.php'; ?>

<div class="admin-main">
<div class="page-wrapper">
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-folder" style="color:var(--primary);"></i> Categories</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="grid-2">
            <div class="card-box">
                <h3 style="margin-bottom:1rem;"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h3>
                <form action="" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editCat ? 'update' : 'create' ?>">
                    <?php if ($editCat): ?><input type="hidden" name="id" value="<?= $editCat['id'] ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editCat['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <input type="hidden" name="icon" id="iconInput" value="<?= htmlspecialchars($editCat['icon'] ?? 'bi-book') ?>">
                        <div class="icon-picker">
                            <button type="button" class="icon-picker-toggle" id="iconToggle">
                                <span class="icon-picker-preview"><i class="bi <?= htmlspecialchars($editCat['icon'] ?? 'bi-book') ?>" id="iconPreview"></i></span>
                                <span class="icon-picker-name" id="iconName"><?= htmlspecialchars($editCat['icon'] ?? 'bi-book') ?></span>
                                <i class="bi bi-chevron-down icon-picker-caret"></i>
                            </button>
                            <div class="icon-picker-panel" id="iconPanel" hidden>
                                <input type="search" id="iconSearch" class="form-control icon-picker-search" placeholder="Search icons..." autocomplete="off">
                                <div class="icon-picker-grid" id="iconGrid"></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($editCat): ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Category</button>
                    <a href="categories.php" class="btn btn-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Category</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-box">
                <h3 style="margin-bottom:1rem;">All Categories</h3>
                <input type="text" id="liveSearch" placeholder="Search categories..." class="form-control" style="margin-bottom:1rem;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Name</th><th>Depts</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($categories->num_rows === 0): ?>
                        <tr><td colspan="3" style="text-align:center;color:var(--gray);padding:1.5rem;">No categories yet. Create one using the form.</td></tr>
                        <?php endif; ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr class="filterable-item">
                            <td>
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>" style="color:var(--primary);"></i>
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
                            <td><?= $cat['dept_count'] ?></td>
                            <td class="actions">
                                <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php $delMsg = htmlspecialchars(json_encode("Delete \"{$cat['name']}\"?\n\nThis will also permanently delete:\n- {$cat['dept_count']} department(s)\n- {$cat['subject_count']} subject(s)\n- {$cat['note_count']} note(s)", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm(<?= $delMsg ?>);">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script src="../js/script.js"></script>
<script>
(function() {
    var icons = ['bi-book','bi-book-half','bi-bookmark','bi-journal','bi-journal-bookmark-fill','bi-journal-text','bi-newspaper','bi-mortarboard','bi-mortarboard-fill','bi-backpack','bi-award','bi-patch-check','bi-laptop','bi-pc-display','bi-phone','bi-tablet','bi-cpu','bi-gpu-card','bi-motherboard','bi-code-slash','bi-terminal','bi-braces','bi-bug','bi-git','bi-database','bi-server','bi-hdd-network','bi-cloud','bi-wifi','bi-diagram-3','bi-qr-code','bi-robot','bi-calculator','bi-percent','bi-graph-up','bi-bar-chart','bi-pie-chart','bi-table','bi-gear','bi-tools','bi-wrench','bi-hammer','bi-rulers','bi-compass','bi-lightbulb','bi-lightning','bi-magnet','bi-speedometer2','bi-activity','bi-atom','bi-globe','bi-globe2','bi-map','bi-geo-alt','bi-flag','bi-bank','bi-building','bi-shop','bi-briefcase','bi-cash-coin','bi-piggy-bank','bi-wallet2','bi-cart','bi-palette','bi-brush','bi-pen','bi-pencil','bi-vector-pen','bi-music-note-beamed','bi-camera','bi-camera-video','bi-image','bi-film','bi-mic','bi-translate','bi-chat-dots','bi-envelope','bi-heart-pulse','bi-capsule','bi-shield','bi-key','bi-lock','bi-trophy','bi-star','bi-tree','bi-sun','bi-droplet','bi-thermometer'];
    var grid = document.getElementById('iconGrid');
    var panel = document.getElementById('iconPanel');
    var toggle = document.getElementById('iconToggle');
    var search = document.getElementById('iconSearch');
    var input = document.getElementById('iconInput');
    var preview = document.getElementById('iconPreview');
    var nameEl = document.getElementById('iconName');

    function render(filter) {
        grid.innerHTML = '';
        var q = (filter || '').toLowerCase().replace(/^bi-?/, '');
        icons.forEach(function(cls) {
            if (q && cls.toLowerCase().indexOf(q) === -1) return;
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'icon-picker-option' + (cls === input.value ? ' selected' : '');
            b.title = cls;
            b.innerHTML = '<i class="bi ' + cls + '"></i>';
            b.addEventListener('click', function() {
                input.value = cls;
                preview.className = 'bi ' + cls;
                nameEl.textContent = cls;
                grid.querySelectorAll('.selected').forEach(function(el) { el.classList.remove('selected'); });
                b.classList.add('selected');
                panel.hidden = true;
            });
            grid.appendChild(b);
        });
        if (!grid.children.length) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--gray);font-size:0.8rem;padding:0.5rem;">No icons match</div>';
        }
    }

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.hidden = !panel.hidden;
        if (!panel.hidden) { render(''); search.value = ''; search.focus(); }
    });
    document.addEventListener('click', function(e) {
        if (!panel.hidden && !e.target.closest('.icon-picker')) panel.hidden = true;
    });
    search.addEventListener('input', function() { render(search.value); });
    render('');
})();
</script>
</body>
</html>
