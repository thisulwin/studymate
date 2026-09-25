<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$totalNotes = getTotalNotes($conn);
$totalUsers = getTotalUsers($conn);
$totalMessages = getTotalMessages($conn);
$unreadMessages = getUnreadMessages($conn);
$totalCategories = $conn->query("SELECT COUNT(*) as cnt FROM categories")->fetch_assoc()['cnt'];
$totalDepartments = $conn->query("SELECT COUNT(*) as cnt FROM departments")->fetch_assoc()['cnt'];
$totalSubjects = $conn->query("SELECT COUNT(*) as cnt FROM subjects")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= $siteName ?></title>
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
            <h1><i class="bi bi-speedometer2" style="color: var(--primary);"></i> Admin Dashboard</h1>
        </div>

        <div class="grid-3">
            <a href="<?= $basePath ?>admin/categories.php" class="dashboard-card text-center" style="border-top:4px solid var(--primary);text-decoration:none;">
                <h3 style="color:var(--primary);justify-content:center;"><i class="bi bi-folder"></i> Categories</h3>
                <div style="font-size:2.2rem;font-weight:800;"><?= $totalCategories ?></div>
            </a>
            <a href="<?= $basePath ?>admin/departments.php" class="dashboard-card text-center" style="border-top:4px solid var(--secondary);text-decoration:none;">
                <h3 style="color:var(--secondary);justify-content:center;"><i class="bi bi-building"></i> Departments</h3>
                <div style="font-size:2.2rem;font-weight:800;"><?= $totalDepartments ?></div>
            </a>
            <a href="<?= $basePath ?>admin/subjects.php" class="dashboard-card text-center" style="border-top:4px solid var(--accent);text-decoration:none;">
                <h3 style="color:var(--accent);justify-content:center;"><i class="bi bi-book"></i> Subjects</h3>
                <div style="font-size:2.2rem;font-weight:800;"><?= $totalSubjects ?></div>
            </a>
        </div>

        <div class="grid-3">
            <a href="<?= $basePath ?>admin/notes.php" class="dashboard-card text-center" style="border-top:4px solid var(--success);text-decoration:none;">
                <h3 style="color:var(--success);justify-content:center;"><i class="bi bi-file-earmark-pdf"></i> Notes</h3>
                <div style="font-size:2.2rem;font-weight:800;"><?= $totalNotes ?></div>
            </a>
            <a href="<?= $basePath ?>admin/messages.php" class="dashboard-card text-center" style="border-top:4px solid var(--danger);text-decoration:none;">
                <h3 style="color:var(--danger);justify-content:center;"><i class="bi bi-envelope"></i> Messages</h3>
                <div style="font-size:2.2rem;font-weight:800;"><?= $totalMessages ?></div>
                <?php if ($unreadMessages > 0): ?>
                <span class="badge badge-unread" style="margin-top:0.5rem;"><?= $unreadMessages ?> unread</span>
                <?php endif; ?>
            </a>
            <a href="<?= $basePath ?>index.php" class="dashboard-card text-center" style="border-top:4px solid var(--gray);text-decoration:none;">
                <h3 style="color:var(--gray);justify-content:center;"><i class="bi bi-house"></i> View Site</h3>
                <div style="font-size:0.85rem;color:var(--gray);margin-top:1rem;">Go to homepage</div>
            </a>
        </div>

        <div class="grid-2">
            <div class="dashboard-card">
                <h3><i class="bi bi-clock-history" style="color:var(--primary);"></i> Recent Notes</h3>
                <?php
                $recent = $conn->query("SELECT n.*, s.name as subject_name FROM notes n JOIN subjects s ON n.subject_id = s.id ORDER BY n.created_at DESC LIMIT 5");
                while ($note = $recent->fetch_assoc()):
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--light-gray);">
                    <div>
                        <strong style="font-size:0.85rem;"><?= htmlspecialchars($note['title']) ?></strong>
                        <br><span style="font-size:0.75rem;color:var(--gray);"><?= htmlspecialchars($note['subject_name']) ?></span>
                    </div>
                    <a href="<?= $basePath ?>view_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-ghost"><i class="bi bi-eye"></i></a>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="dashboard-card">
                <h3><i class="bi bi-people" style="color:var(--secondary);"></i> Recent Users</h3>
                <?php
                $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                while ($user = $users->fetch_assoc()):
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--light-gray);">
                    <div>
                        <strong style="font-size:0.85rem;"><?= htmlspecialchars($user['username']) ?></strong>
                        <br><span style="font-size:0.75rem;color:var(--gray);"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= htmlspecialchars($user['role']) ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
