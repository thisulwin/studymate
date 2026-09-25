<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username)) $errors[] = 'Username is required';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username can only contain letters, numbers and underscores';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already exists';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            flash('success', 'Registration successful! Please login.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= $siteName ?></title>
    <link rel="stylesheet" href="../css/style.css?v=20260922191220">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="auth-card card-box">
            <h2>Create Account</h2>
            <p class="subtitle">Join StudyMate to save notes and track your study progress</p>

            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
            <?php endif; ?>

            <form action="" method="POST" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Choose a username" value="<?= htmlspecialchars($username) ?>" required minlength="3">
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($email) ?>" required>
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a password (min 6 chars)" required minlength="6">
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                    <span class="error-text"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-person-plus"></i> Register</button>
            </form>
            <p class="footer-text">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
