<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noteId = (int)($_POST['note_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $userId = $_SESSION['user_id'];

    if (empty($content)) {
        flash('comment_error', 'Comment cannot be empty');
        header("Location: ../view_note.php?id=$noteId");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO comments (note_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $noteId, $userId, $parentId, $content);
    $stmt->execute();

    flash('comment', 'Comment posted successfully!');
}

header("Location: ../view_note.php?id=$noteId#comments");
exit();
