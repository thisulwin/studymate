<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$category = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$category->bind_param("i", $categoryId);
$category->execute();
$category = $category->get_result()->fetch_assoc();

if (!$category) {
    redirect('index.php');
}

$departments = $conn->prepare("SELECT d.*, (SELECT COUNT(*) FROM subjects WHERE department_id = d.id) as subject_count FROM departments d WHERE d.category_id = ? ORDER BY d.name");
$departments->bind_param("i", $categoryId);
$departments->execute();
$departments = $departments->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> - <?= $siteName ?></title>
    <link rel="stylesheet" href="css/style.css?v=20260922191220">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= htmlspecialchars($category['name']) ?></span>
        </div>

        <div class="page-header">
            <div>
                <h1><i class="bi <?= htmlspecialchars($category['icon']) ?>" style="color: var(--primary);"></i> <?= htmlspecialchars($category['name']) ?></h1>
                <p style="color: var(--gray); margin-top: 0.35rem;"><?= htmlspecialchars($category['description']) ?></p>
            </div>
        </div>

        <div class="list-grid">
            <?php while ($dept = $departments->fetch_assoc()): ?>
            <a href="<?= $basePath ?>subjects.php?dept_id=<?= $dept['id'] ?>" class="list-card filterable-item">
                <h3><i class="bi bi-building" style="color: var(--primary);"></i> <?= htmlspecialchars($dept['name']) ?></h3>
                <p><?= htmlspecialchars($dept['description']) ?></p>
                <div class="meta">
                    <span><i class="bi bi-book"></i> <?= $dept['subject_count'] ?> Subject<?= $dept['subject_count'] != 1 ? 's' : '' ?></span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>

        <?php if ($departments->num_rows === 0): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h3>No Departments Found</h3>
            <p>There are no departments in this category yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
