<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_POST['read_id'])) {
    requireCsrf();
    $id = (int)$_POST['read_id'];
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: messages.php");
    exit();
}

if (isset($_POST['delete_id'])) {
    requireCsrf();
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: messages.php");
    exit();
}

if (isset($_POST['mark_all'])) {
    requireCsrf();
    $conn->query("UPDATE messages SET is_read = 1 WHERE is_read = 0");
    header("Location: messages.php");
    exit();
}

$messages = $conn->query("SELECT * FROM messages ORDER BY is_read ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin</title>
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
            <h1><i class="bi bi-envelope" style="color:var(--primary);"></i> Contact Messages</h1>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Mark all as read?');">
                <?= csrfField() ?>
                <input type="hidden" name="mark_all" value="1">
                <button type="submit" class="btn btn-sm btn-outline"><i class="bi bi-check-all"></i> Mark All Read</button>
            </form>
        </div>

        <div class="card-box" style="overflow-x:auto;">
            <?php if ($messages->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr><th>Status</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                    <tr style="<?= !$msg['is_read'] ? 'background:#eff6ff;' : '' ?>">
                        <td>
                            <?php if ($msg['is_read']): ?>
                            <span class="badge badge-read"><i class="bi bi-envelope-open"></i> Read</span>
                            <?php else: ?>
                            <span class="badge badge-unread"><i class="bi bi-envelope-fill"></i> New</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($msg['name']) ?></strong></td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td style="max-width:300px;"><?= nl2br(htmlspecialchars(mb_strimwidth($msg['message'], 0, 150, '...'))) ?></td>
                        <td><?= timeAgo($msg['created_at']) ?></td>
                        <td class="actions">
                            <?php if (!$msg['is_read']): ?>
                            <form method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="read_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-envelope-open"></i>
                <h3>No Messages</h3>
                <p>No contact messages yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
