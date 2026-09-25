<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $success = 'Your message has been sent successfully! We will get back to you soon.';
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?= $siteName ?></title>
    <link rel="stylesheet" href="css/style.css?v=20260922191220">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="section-title">
            <h2>Contact Us</h2>
            <p>Have a question or feedback? Send us a message.</p>
        </div>

        <div class="card-box" style="max-width:600px; margin:0 auto;">
            <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>

            <form action="" method="POST" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <span class="error-text"></span>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" class="form-control" placeholder="Write your message here..." rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea>
                    <span class="error-text"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-send"></i> Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
