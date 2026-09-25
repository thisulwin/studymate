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
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            flash('admin_success', 'Department deleted');
        } else {
            flash('admin_error', 'Failed to delete department');
        }
        header("Location: departments.php");
        exit();
    }

    if ($action === 'create' || $action === 'update') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if (empty($name) || empty($categoryId)) {
            flash('admin_error', 'Name and category are required');
        } elseif ($action === 'update' && $id <= 0) {
            flash('admin_error', 'Invalid department');
        } else {
            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO departments (category_id, name, description) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $categoryId, $name, $description);
            } else {
                $stmt = $conn->prepare("UPDATE departments SET category_id = ?, name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("issi", $categoryId, $name, $description, $id);
            }
            if ($stmt->execute()) {
                flash('admin_success', $action === 'create' ? 'Department created successfully' : 'Department updated successfully');
            } else {
                flash('admin_error', 'Failed to save department');
            }
        }
        header("Location: departments.php");
        exit();
    }
}

$editDept = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editDept = $stmt->get_result()->fetch_assoc();
}

$departments = $conn->query("SELECT d.*, c.name as cat_name,
    (SELECT COUNT(*) FROM subjects WHERE department_id = d.id) as subject_count,
    (SELECT COUNT(*) FROM notes n JOIN subjects s ON n.subject_id = s.id WHERE s.department_id = d.id) as note_count
    FROM departments d JOIN categories c ON d.category_id = c.id ORDER BY c.name, d.name");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - Admin</title>
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
            <h1><i class="bi bi-building" style="color:var(--primary);"></i> Departments</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="grid-form-table">
            <div class="card-box">
                <h3 style="margin-bottom:1rem;"><?= $editDept ? 'Edit Department' : 'Add Department' ?></h3>
                <form action="" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editDept ? 'update' : 'create' ?>">
                    <?php if ($editDept): ?><input type="hidden" name="id" value="<?= $editDept['id'] ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editDept && $editDept['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editDept['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editDept['description'] ?? '') ?></textarea>
                    </div>
                    <?php if ($editDept): ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Department</button>
                    <a href="departments.php" class="btn btn-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Department</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-box" style="overflow-x:auto;">
                <h3 style="margin-bottom:1rem;">All Departments</h3>
                <input type="text" id="liveSearch" placeholder="Search departments..." class="form-control" style="margin-bottom:1rem; max-width:300px;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Name</th><th>Category</th><th>Subjects</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($departments->num_rows === 0): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--gray);padding:1.5rem;">No departments yet. Create one using the form.</td></tr>
                        <?php endif; ?>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                        <tr class="filterable-item">
                            <td><?= htmlspecialchars($dept['name']) ?></td>
                            <td><span class="badge badge-user"><?= htmlspecialchars($dept['cat_name']) ?></span></td>
                            <td><?= $dept['subject_count'] ?></td>
                            <td class="actions">
                                <a href="departments.php?edit=<?= $dept['id'] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php $delMsg = htmlspecialchars(json_encode("Delete \"{$dept['name']}\"?\n\nThis will also permanently delete:\n- {$dept['subject_count']} subject(s)\n- {$dept['note_count']} note(s)", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm(<?= $delMsg ?>);">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="delete_id" value="<?= $dept['id'] ?>">
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
</body>
</html>
