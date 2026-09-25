<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
requireCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $vote = (int)($_POST['vote'] ?? 0);
    $noteId = (int)($_POST['note_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($vote !== 1 && $vote !== -1) {
        header("Location: ../view_note.php?id=$noteId#comment-$commentId");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO comment_votes (comment_id, user_id, vote_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote_type = ?");
    $stmt->bind_param("iiii", $commentId, $userId, $vote, $vote);
    $stmt->execute();

    header("Location: ../view_note.php?id=$noteId#comment-$commentId");
    exit();
}
