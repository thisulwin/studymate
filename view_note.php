<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$noteId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT n.*, s.name as subject_name, s.code as subject_code, 
    d.name as dept_name, d.id as dept_id, c.name as cat_name, c.id as cat_id,
    u.username as uploader_name
    FROM notes n 
    JOIN subjects s ON n.subject_id = s.id
    JOIN departments d ON s.department_id = d.id
    JOIN categories c ON d.category_id = c.id
    LEFT JOIN users u ON n.uploaded_by = u.id
    WHERE n.id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    redirect('subjects.php');
}

$isSaved = false;
if (isLoggedIn()) {
    $savedStmt = $conn->prepare("SELECT id FROM saved_notes WHERE user_id = ? AND note_id = ?");
    $savedStmt->bind_param("ii", $_SESSION['user_id'], $noteId);
    $savedStmt->execute();
    $isSaved = $savedStmt->get_result()->num_rows > 0;
}

$tagsStmt = $conn->prepare("SELECT tag FROM note_tags WHERE note_id = ? ORDER BY tag");
$tagsStmt->bind_param("i", $noteId);
$tagsStmt->execute();
$noteTags = $tagsStmt->get_result();

$comments = $conn->prepare("SELECT c.*, u.username, u.role as user_role,
    (SELECT SUM(vote_type) FROM comment_votes WHERE comment_id = c.id) as score,
    (SELECT vote_type FROM comment_votes WHERE comment_id = c.id AND user_id = ?) as my_vote
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.note_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC");
$userId = isLoggedIn() ? $_SESSION['user_id'] : 0;
$comments->bind_param("ii", $userId, $noteId);
$comments->execute();
$comments = $comments->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($note['title']) ?> - <?= $siteName ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="departments.php?category_id=<?= $note['cat_id'] ?>"><?= htmlspecialchars($note['cat_name']) ?></a>
            <i class="bi bi-chevron-right"></i>
            <a href="subjects.php?dept_id=<?= $note['dept_id'] ?>"><?= htmlspecialchars($note['dept_name']) ?></a>
            <i class="bi bi-chevron-right"></i>
            <span><?= htmlspecialchars($note['title']) ?></span>
        </div>

        <div class="note-view-card card-box">
            <div class="note-view-grid">
                <div class="thumbnail-wrapper note-view-thumb-wrap">
                    <a href="<?= htmlspecialchars($note['pdf_path']) ?>" download class="thumbnail" title="Click to download <?= htmlspecialchars($note['title']) ?>">
                        <?php if ($note['thumbnail_path'] && file_exists($note['thumbnail_path'])): ?>
                            <img src="<?= htmlspecialchars($note['thumbnail_path']) ?>" alt="<?= htmlspecialchars($note['title']) ?>">
                        <?php else: ?>
                            <i class="bi bi-file-earmark-pdf"></i>
                        <?php endif; ?>
                        <span class="thumb-download-overlay"><i class="bi bi-download"></i> Click to Download</span>
                    </a>
                    <div class="card-save-btn-wrap">
                        <?php if (isLoggedIn()): ?>
                            <?php if ($isSaved): ?>
                            <form action="<?= $basePath ?>actions/unsave_note.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <button type="submit" class="card-save-btn is-saved" title="Saved (Click to remove)">
                                    <i class="bi bi-bookmark-fill"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form action="<?= $basePath ?>actions/save_note.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <button type="submit" class="card-save-btn" title="Save note">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= $basePath ?>auth/login.php" class="card-save-btn" title="Login to save">
                                <i class="bi bi-bookmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="note-view-details">
                    <span class="subject-tag"><?= htmlspecialchars($note['subject_name']) ?> (<?= htmlspecialchars($note['subject_code']) ?>)</span>
                    <h1><?= htmlspecialchars($note['title']) ?></h1>

                    <?php if ($note['description']): ?>
                    <p class="note-view-desc"><?= nl2br(htmlspecialchars($note['description'])) ?></p>
                    <?php endif; ?>

                    <?php if ($noteTags->num_rows > 0): ?>
                    <div class="note-tags">
                        <?php while ($t = $noteTags->fetch_assoc()): ?>
                        <span class="note-tag"><?= htmlspecialchars($t['tag']) ?></span>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>

                    <div class="note-view-meta">
                        <span><i class="bi bi-building"></i> <?= htmlspecialchars($note['dept_name']) ?></span>
                        <span><i class="bi bi-person"></i> <?= htmlspecialchars($note['uploader_name'] ?? 'Faculty') ?></span>
                        <span><i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($note['created_at'])) ?></span>
                        <span><i class="bi bi-chat-dots"></i> <?= $comments->num_rows ?> comments</span>
                    </div>

                    <div class="note-view-actions">
                        <a href="<?= htmlspecialchars($note['pdf_path']) ?>" download class="btn btn-primary"><i class="bi bi-download"></i> Download PDF</a>
                        <?php if (isLoggedIn()): ?>
                            <?php if ($isSaved): ?>
                            <form action="<?= $basePath ?>actions/unsave_note.php" method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <button type="submit" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger);"><i class="bi bi-bookmark-fill"></i> Saved</button>
                            </form>
                            <?php else: ?>
                            <form action="<?= $basePath ?>actions/save_note.php" method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <button type="submit" class="btn btn-outline"><i class="bi bi-bookmark"></i> Save Note</button>
                            </form>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="<?= $basePath ?>auth/login.php" class="btn btn-outline"><i class="bi bi-bookmark"></i> Login to Save</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $flash = flash('comment'); if ($flash): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $flash ?></div>
        <?php endif; ?>

        <?php $flashErr = flash('comment_error'); if ($flashErr): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $flashErr ?></div>
        <?php endif; ?>

        <div class="comments-section" id="comments">
            <h3><i class="bi bi-chat-dots"></i> Comments (<?= $comments->num_rows ?>)</h3>

            <?php if (isLoggedIn()): ?>
            <form action="<?= $basePath ?>actions/add_comment.php" method="POST" data-validate style="margin-bottom: 1.5rem;">
                <?= csrfField() ?>
                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Write a comment..." rows="3" required></textarea>
                    <span class="error-text"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Post Comment</button>
            </form>
            <?php else: ?>
            <div class="alert alert-info" style="margin-bottom:1.5rem;">
                <i class="bi bi-info-circle"></i> <a href="<?= $basePath ?>auth/login.php">Login</a> to post a comment.
            </div>
            <?php endif; ?>

            <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="comment" id="comment-<?= $comment['id'] ?>">
                <div class="comment-header">
                    <div class="comment-avatar">
                        <?= strtoupper(substr($comment['username'] ?? 'G', 0, 1)) ?>
                    </div>
                    <div class="comment-meta">
                        <span class="name">
                            <?= htmlspecialchars($comment['username'] ?? 'Guest') ?>
                            <?php if ($comment['user_role'] === 'admin'): ?>
                                <span class="badge badge-admin" style="font-size:0.65rem;">Admin</span>
                            <?php endif; ?>
                        </span>
                        <div class="time"><?= timeAgo($comment['created_at']) ?></div>
                    </div>
                </div>
                <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                <div class="comment-actions">
                    <form action="actions/vote_comment.php" method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <input type="hidden" name="note_id" value="<?= $noteId ?>">
                        <input type="hidden" name="vote" value="1">
                        <button type="submit" class="<?= ($comment['my_vote'] ?? 0) == 1 ? 'liked' : '' ?>" <?= !isLoggedIn() ? 'onclick="event.preventDefault(); window.location=\'auth/login.php\';"' : '' ?>>
                            <i class="bi bi-hand-thumbs-up"></i> <?= max(0, $comment['score'] ?? 0) ?>
                        </button>
                    </form>
                    <form action="actions/vote_comment.php" method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <input type="hidden" name="note_id" value="<?= $noteId ?>">
                        <input type="hidden" name="vote" value="-1">
                        <button type="submit" class="<?= ($comment['my_vote'] ?? 0) == -1 ? 'disliked' : '' ?>" <?= !isLoggedIn() ? 'onclick="event.preventDefault(); window.location=\'auth/login.php\';"' : '' ?>>
                            <i class="bi bi-hand-thumbs-down"></i>
                        </button>
                    </form>
                    <?php if (isLoggedIn()): ?>
                    <button onclick="toggleReply(<?= $comment['id'] ?>)"><i class="bi bi-reply"></i> Reply</button>
                    <?php else: ?>
                    <a href="<?= $basePath ?>auth/login.php" style="font-size:0.8rem;"><i class="bi bi-reply"></i> Reply</a>
                    <?php endif; ?>
                </div>

                <?php if (isLoggedIn()): ?>
                <div class="reply-form" id="reply-<?= $comment['id'] ?>">
                    <form action="<?= $basePath ?>actions/add_comment.php" method="POST" data-validate>
                        <?= csrfField() ?>
                        <input type="hidden" name="note_id" value="<?= $noteId ?>">
                        <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                        <div class="form-group">
                            <textarea name="content" class="form-control" placeholder="Write a reply..." rows="2" required></textarea>
                            <span class="error-text"></span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Reply</button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="toggleReply(<?= $comment['id'] ?>)">Cancel</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php
                $repliesStmt = $conn->prepare("SELECT c.*, u.username, u.role as user_role,
                    (SELECT SUM(vote_type) FROM comment_votes WHERE comment_id = c.id) as score,
                    (SELECT vote_type FROM comment_votes WHERE comment_id = c.id AND user_id = ?) as my_vote
                    FROM comments c LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.parent_id = ? ORDER BY c.created_at ASC");
                $repliesStmt->bind_param("ii", $userId, $comment['id']);
                $repliesStmt->execute();
                $replies = $repliesStmt->get_result();
                if ($replies->num_rows > 0):
                ?>
                <div class="replies">
                    <?php while ($reply = $replies->fetch_assoc()): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <div class="comment-avatar" style="width:28px;height:28px;font-size:0.7rem;">
                                <?= strtoupper(substr($reply['username'] ?? 'G', 0, 1)) ?>
                            </div>
                            <div class="comment-meta">
                                <span class="name">
                                    <?= htmlspecialchars($reply['username'] ?? 'Guest') ?>
                                    <?php if ($reply['user_role'] === 'admin'): ?>
                                        <span class="badge badge-admin" style="font-size:0.6rem;">Admin</span>
                                    <?php endif; ?>
                                </span>
                                <div class="time"><?= timeAgo($reply['created_at']) ?></div>
                            </div>
                        </div>
                        <div class="comment-content" style="padding-left:2rem;"><?= nl2br(htmlspecialchars($reply['content'])) ?></div>
                        <div class="comment-actions" style="padding-left:2rem;">
                            <form action="actions/vote_comment.php" method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="comment_id" value="<?= $reply['id'] ?>">
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <input type="hidden" name="vote" value="1">
                                <button type="submit" class="<?= ($reply['my_vote'] ?? 0) == 1 ? 'liked' : '' ?>" <?= !isLoggedIn() ? 'onclick="event.preventDefault(); window.location=\'auth/login.php\';"' : '' ?>>
                                    <i class="bi bi-hand-thumbs-up"></i> <?= max(0, $reply['score'] ?? 0) ?>
                                </button>
                            </form>
                            <form action="actions/vote_comment.php" method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="comment_id" value="<?= $reply['id'] ?>">
                                <input type="hidden" name="note_id" value="<?= $noteId ?>">
                                <input type="hidden" name="vote" value="-1">
                                <button type="submit" class="<?= ($reply['my_vote'] ?? 0) == -1 ? 'disliked' : '' ?>" <?= !isLoggedIn() ? 'onclick="event.preventDefault(); window.location=\'auth/login.php\';"' : '' ?>>
                                    <i class="bi bi-hand-thumbs-down"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
