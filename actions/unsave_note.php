<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noteId = (int)($_POST['note_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($noteId) {
        $stmt = $conn->prepare("DELETE FROM saved_notes WHERE user_id = ? AND note_id = ?");
        $stmt->bind_param("ii", $userId, $noteId);
        $stmt->execute();
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? ($basePath . 'saved_notes.php');
header("Location: $redirect");
exit();
