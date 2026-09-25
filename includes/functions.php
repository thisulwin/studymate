<?php

function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isGuest() {
    return !isLoggedIn();
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: /t-project/auth/login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: /t-project/index.php");
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function getTotalNotes($conn) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM notes");
    return $result->fetch_assoc()['cnt'];
}

function getTotalUsers($conn) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM users");
    return $result->fetch_assoc()['cnt'];
}

function getTotalMessages($conn) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM messages");
    return $result->fetch_assoc()['cnt'];
}

function getUnreadMessages($conn) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE is_read = 0");
    return $result->fetch_assoc()['cnt'];
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        return false;
    }
    return true;
}

function requireCsrf() {
    if (!verifyCsrf()) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}
