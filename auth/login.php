<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? null;
            $_SESSION['profile_image'] = $user['profile_image'];

            $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit();
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $siteName ?></title>
    <link rel="stylesheet" href="../css/style.css?v=20260922191220">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="auth-card card-box">
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to access and save notes</p>

            <?php $success = flash('success'); if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
            <?php endif; ?>

            <form action="" method="POST" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($email) ?>" required>
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <span class="error-text"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-box-arrow-in-right"></i> Login</button>
            </form>
            <p class="footer-text">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
