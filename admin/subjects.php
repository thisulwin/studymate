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
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            flash('admin_success', 'Subject deleted');
        } else {
            flash('admin_error', 'Failed to delete subject');
        }
        header("Location: subjects.php");
        exit();
    }

    if ($action === 'create' || $action === 'update') {
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if (empty($name) || empty($departmentId)) {
            flash('admin_error', 'Name and department are required');
        } elseif ($action === 'update' && $id <= 0) {
            flash('admin_error', 'Invalid subject');
        } else {
            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO subjects (department_id, name, code) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $departmentId, $name, $code);
            } else {
                $stmt = $conn->prepare("UPDATE subjects SET department_id = ?, name = ?, code = ? WHERE id = ?");
                $stmt->bind_param("issi", $departmentId, $name, $code, $id);
            }
            if ($stmt->execute()) {
                flash('admin_success', $action === 'create' ? 'Subject created successfully' : 'Subject updated successfully');
            } else {
                flash('admin_error', 'Failed to save subject');
            }
        }
        header("Location: subjects.php");
        exit();
    }
}

$editSub = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editSub = $stmt->get_result()->fetch_assoc();
}

$subjects = $conn->query("SELECT s.*, d.name as dept_name, c.name as cat_name, (SELECT COUNT(*) FROM notes WHERE subject_id = s.id) as note_count FROM subjects s JOIN departments d ON s.department_id = d.id JOIN categories c ON d.category_id = c.id ORDER BY c.name, d.name, s.name");
$departments = $conn->query("SELECT d.*, c.name as cat_name FROM departments d JOIN categories c ON d.category_id = c.id ORDER BY c.name, d.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin</title>
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
            <h1><i class="bi bi-book" style="color:var(--primary);"></i> Subjects</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="grid-form-table">
            <div class="card-box">
                <h3 style="margin-bottom:1rem;"><?= $editSub ? 'Edit Subject' : 'Add Subject' ?></h3>
                <form action="" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editSub ? 'update' : 'create' ?>">
                    <?php if ($editSub): ?><input type="hidden" name="id" value="<?= $editSub['id'] ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?= $dept['id'] ?>" <?= ($editSub && $editSub['department_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['cat_name'] . ' - ' . $dept['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editSub['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Subject Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ICT2209" value="<?= htmlspecialchars($editSub['code'] ?? '') ?>">
                    </div>
                    <?php if ($editSub): ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Subject</button>
                    <a href="subjects.php" class="btn btn-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Subject</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-box" style="overflow-x:auto;">
                <h3 style="margin-bottom:1rem;">All Subjects</h3>
                <input type="text" id="liveSearch" placeholder="Search subjects..." class="form-control" style="margin-bottom:1rem; max-width:300px;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Code</th><th>Name</th><th>Department</th><th>Notes</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($subjects->num_rows === 0): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--gray);padding:1.5rem;">No subjects yet. Create one using the form.</td></tr>
                        <?php endif; ?>
                        <?php while ($sub = $subjects->fetch_assoc()): ?>
                        <tr class="filterable-item">
                            <td><strong><?= htmlspecialchars($sub['code']) ?></strong></td>
                            <td><?= htmlspecialchars($sub['name']) ?></td>
                            <td><?= htmlspecialchars($sub['dept_name']) ?></td>
                            <td><?= $sub['note_count'] ?></td>
                            <td class="actions">
                                <a href="subjects.php?edit=<?= $sub['id'] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php $delMsg = htmlspecialchars(json_encode("Delete \"{$sub['name']}\"?\n\nThis will also permanently delete {$sub['note_count']} note(s).", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm(<?= $delMsg ?>);">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="delete_id" value="<?= $sub['id'] ?>">
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
