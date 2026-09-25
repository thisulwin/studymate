<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$success = flash('account_success');
$error = flash('account_error');

$user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user->bind_param("i", $userId);
$user->execute();
$user = $user->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'change_name') {
        $username = trim($_POST['username'] ?? '');

        if (empty($username)) {
            flash('account_error', 'Display name is required');
            redirect('account.php');
        }

        if (strlen($username) < 2) {
            flash('account_error', 'Display name must be at least 2 characters');
            redirect('account.php');
        }

        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->bind_param("si", $username, $userId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            flash('account_error', 'Display name already taken');
            redirect('account.php');
        }

        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();

        $_SESSION['username'] = $username;

        flash('account_success', 'Display name updated');
        redirect('account.php');
    }

    if ($action === 'upload_image') {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                flash('account_error', 'Only JPG, PNG, GIF, WEBP images are allowed');
                redirect('account.php');
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                flash('account_error', 'Image must be less than 2MB');
                redirect('account.php');
            }

            $imageName = 'profile_' . $userId . '_' . uniqid() . '.' . $ext;
            $imagePath = 'uploads/profiles/' . $imageName;

            if (!is_dir('uploads/profiles')) {
                mkdir('uploads/profiles', 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $imagePath)) {
                if ($user['profile_image'] && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }

                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $imagePath, $userId);
                $stmt->execute();

                $_SESSION['profile_image'] = $imagePath;

                flash('account_success', 'Profile image updated');
            } else {
                flash('account_error', 'Failed to upload image');
            }
        } else {
            flash('account_error', 'Please select an image');
        }
        redirect('account.php');
    }

    if ($action === 'remove_image') {
        if ($user['profile_image'] && file_exists($user['profile_image'])) {
            unlink($user['profile_image']);
        }
        $stmt = $conn->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $_SESSION['profile_image'] = null;

        flash('account_success', 'Profile image removed');
        redirect('account.php');
    }

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            flash('account_error', 'Current password is incorrect');
            redirect('account.php');
        }

        if (strlen($newPassword) < 6) {
            flash('account_error', 'New password must be at least 6 characters');
            redirect('account.php');
        }

        if ($newPassword !== $confirmPassword) {
            flash('account_error', 'New passwords do not match');
            redirect('account.php');
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $userId);
        $stmt->execute();

        flash('account_success', 'Password changed successfully');
        redirect('account.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?= $siteName ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-person-gear" style="color: var(--primary);"></i> My Account</h1>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="account-grid">

            <div class="account-left">
                <div class="card-box account-profile-card">
                    <div class="account-avatar">
                        <?php if ($user['profile_image'] && file_exists($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="">
                        <?php else: ?>
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <span class="account-email"><?= htmlspecialchars($user['email']) ?></span>
                    <span class="account-joined"><i class="bi bi-calendar3"></i> Member since <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                </div>

                <div class="card-box">
                    <h3><i class="bi bi-image" style="color:var(--secondary);"></i> Profile Photo</h3>

                    <form action="" method="POST" enctype="multipart/form-data" class="account-image-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_image">
                        <div class="form-group">
                            <input type="file" name="profile_image" class="form-control" accept="image/*" required>
                            <small style="color:var(--gray);font-size:0.75rem;">JPG, PNG, GIF — max 2MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload"></i> Upload New Photo</button>
                    </form>

                    <?php if ($user['profile_image']): ?>
                    <form action="" method="POST" style="margin-top:0.75rem;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove_image">
                        <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Remove profile photo?')"><i class="bi bi-trash"></i> Remove Photo</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="account-right">
                <div class="card-box">
                    <h3><i class="bi bi-person" style="color:var(--primary);"></i> Display Name</h3>

                    <form action="" method="POST" data-validate>
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_name">
                        <div class="form-group">
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required minlength="2">
                            <span class="error-text"></span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Update Name</button>
                    </form>
                </div>

                <div class="card-box">
                    <h3><i class="bi bi-lock" style="color:var(--danger);"></i> Change Password</h3>

                    <form action="" method="POST" data-validate>
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                            <span class="error-text"></span>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <span class="error-text"></span>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                            <span class="error-text"></span>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> Update Password</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
