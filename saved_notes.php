<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$catId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest_saved';

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

$sql = "SELECT DISTINCT n.*, s.name as subject_name, s.code as subject_code,
        d.name as dept_name, c.name as cat_name, c.id as cat_id,
        sn.saved_at,
        (SELECT COUNT(*) FROM comments WHERE note_id = n.id) as comment_count,
        (SELECT GROUP_CONCAT(tag SEPARATOR ', ') FROM note_tags WHERE note_id = n.id) as tags
        FROM saved_notes sn
        JOIN notes n ON sn.note_id = n.id
        JOIN subjects s ON n.subject_id = s.id
        JOIN departments d ON s.department_id = d.id
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN note_tags nt ON n.id = nt.note_id
        WHERE sn.user_id = ?";

$params = [$userId];
$types = 'i';

if ($catId) {
    $sql .= " AND c.id = ?";
    $types .= "i";
    $params[] = $catId;
}

if ($search) {
    $sql .= " AND (n.title LIKE ? OR s.name LIKE ? OR s.code LIKE ? OR n.description LIKE ? OR nt.tag LIKE ?)";
    $searchTerm = "%$search%";
    $types .= "sssss";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

switch ($sortBy) {
    case 'oldest_saved':
        $sql .= " ORDER BY sn.saved_at ASC";
        break;
    case 'title':
        $sql .= " ORDER BY n.title ASC";
        break;
    case 'newest_note':
        $sql .= " ORDER BY n.created_at DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY comment_count DESC";
        break;
    default:
        $sql .= " ORDER BY sn.saved_at DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$savedNotes = $stmt->get_result();

$totalUserSaved = $conn->prepare("SELECT COUNT(*) as cnt FROM saved_notes WHERE user_id = ?");
$totalUserSaved->bind_param("i", $userId);
$totalUserSaved->execute();
$totalCount = $totalUserSaved->get_result()->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Notes - <?= $siteName ?></title>
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
            <span>Save Note</span>
        </div>

        <div class="saved-notes-header">
            <div>
                <h1><i class="bi bi-bookmark-heart" style="color:var(--primary);"></i> Saved Notes</h1>
                <p style="color:var(--gray);margin-top:0.25rem;">You have <?= $totalCount ?> saved study <?= $totalCount === 1 ? 'material' : 'materials' ?></p>
            </div>
            <a href="subjects.php" class="btn btn-outline btn-sm"><i class="bi bi-search"></i> Browse All Notes</a>
        </div>

        <form action="" method="GET" class="saved-filter-bar">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search saved notes..." value="<?= htmlspecialchars($search) ?>" id="liveSearch">
            </div>

            <select name="category_id" class="form-control" style="max-width:220px;">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="sort" class="form-control" style="max-width:180px;">
                <option value="newest_saved" <?= $sortBy === 'newest_saved' ? 'selected' : '' ?>>Recently Saved</option>
                <option value="oldest_saved" <?= $sortBy === 'oldest_saved' ? 'selected' : '' ?>>Oldest Saved</option>
                <option value="title" <?= $sortBy === 'title' ? 'selected' : '' ?>>Title A-Z</option>
                <option value="newest_note" <?= $sortBy === 'newest_note' ? 'selected' : '' ?>>Newest Upload</option>
                <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Discussed</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Filter</button>
            <?php if ($search || $catId || $sortBy !== 'newest_saved'): ?>
            <a href="saved_notes.php" class="btn btn-ghost btn-sm">Reset</a>
            <?php endif; ?>
        </form>

        <?php if ($savedNotes->num_rows > 0): ?>
        <div class="list-grid">
            <?php while ($note = $savedNotes->fetch_assoc()): ?>
            <div class="note-card filterable-item fade-in">
                <div class="thumbnail-wrapper">
                    <a href="<?= htmlspecialchars($note['pdf_path']) ?>" download class="thumbnail" title="Click to download <?= htmlspecialchars($note['title']) ?>">
                        <?php if ($note['thumbnail_path'] && file_exists($note['thumbnail_path'])): ?>
                            <img src="<?= htmlspecialchars($note['thumbnail_path']) ?>" alt="<?= htmlspecialchars($note['title']) ?>">
                        <?php else: ?>
                            <i class="bi bi-file-earmark-pdf"></i>
                        <?php endif; ?>
                        <span class="thumb-download-overlay"><i class="bi bi-download"></i> Click to Download</span>
                    </a>
                    <div class="card-save-btn-wrap">
                        <form action="<?= $basePath ?>actions/unsave_note.php" method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                            <button type="submit" class="card-save-btn is-saved" title="Remove from saved">
                                <i class="bi bi-bookmark-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="info">
                    <span class="subject-tag"><?= htmlspecialchars($note['subject_name']) ?> <?= htmlspecialchars($note['subject_code']) ?></span>
                    <h3><a href="<?= $basePath ?>view_note.php?id=<?= $note['id'] ?>"><?= htmlspecialchars($note['title']) ?></a></h3>
                    <p><?= htmlspecialchars($note['description'] ?? '') ?></p>
                    <?php if (!empty($note['tags'])): ?>
                    <div class="note-tags">
                        <?php foreach (explode(', ', $note['tags']) as $tag): ?>
                        <span class="note-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="meta">
                        <span><i class="bi bi-bookmark-check"></i> Saved <?= timeAgo($note['saved_at']) ?></span>
                        <span><i class="bi bi-chat-dots"></i> <?= $note['comment_count'] ?></span>
                    </div>
                    <div class="actions" style="margin-top: 0.75rem;">
                        <a href="<?= htmlspecialchars($note['pdf_path']) ?>" download class="btn btn-primary btn-sm"><i class="bi bi-download"></i> Download</a>
                        <a href="<?= $basePath ?>view_note.php?id=<?= $note['id'] ?>" class="btn btn-outline btn-sm"><i class="bi bi-chat-dots"></i> Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-bookmark-x"></i>
            <h3><?= $totalCount === 0 ? "No Saved Notes Yet" : "No Matching Saved Notes" ?></h3>
            <p><?= $totalCount === 0 ? "You haven't bookmarked any notes yet. Browse the library and click the bookmark button on any note card." : "No saved notes match your search and filter criteria. Try clearing your filters." ?></p>
            <a href="subjects.php" class="btn btn-primary" style="margin-top:1rem;"><i class="bi bi-search"></i> Browse Notes</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
