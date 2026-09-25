<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$deptId = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$userId = isLoggedIn() ? $_SESSION['user_id'] : 0;

$department = null;
$category = null;

if ($deptId) {
    $stmt = $conn->prepare("SELECT d.*, c.id as cat_id, c.name as cat_name FROM departments d JOIN categories c ON d.category_id = c.id WHERE d.id = ?");
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $department = $stmt->get_result()->fetch_assoc();
    if ($department) {
        $category = ['id' => $department['cat_id'], 'name' => $department['cat_name']];
    }
}

$notesSql = "SELECT DISTINCT n.*, s.name as subject_name, s.code as subject_code, 
        (SELECT COUNT(*) FROM comments WHERE note_id = n.id) as comment_count,
        (SELECT GROUP_CONCAT(tag SEPARATOR ', ') FROM note_tags WHERE note_id = n.id) as tags,
        (SELECT COUNT(*) FROM saved_notes WHERE user_id = ? AND note_id = n.id) as is_saved
        FROM notes n 
        JOIN subjects s ON n.subject_id = s.id
        LEFT JOIN note_tags nt ON n.id = nt.note_id";

$params = [$userId];
$types = 'i';

if ($deptId) {
    $notesSql .= " WHERE s.department_id = ?";
    $types .= "i";
    $params[] = $deptId;
}

if ($search) {
    $notesSql .= $deptId ? " AND" : " WHERE";
    $notesSql .= " (n.title LIKE ? OR s.name LIKE ? OR s.code LIKE ? OR n.description LIKE ? OR nt.tag LIKE ?)";
    $searchTerm = "%$search%";
    $types .= "sssss";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

switch ($sortBy) {
    case 'oldest':
        $notesSql .= " ORDER BY n.created_at ASC";
        break;
    case 'title':
        $notesSql .= " ORDER BY n.title ASC";
        break;
    case 'popular':
        $notesSql .= " ORDER BY comment_count DESC";
        break;
    default:
        $notesSql .= " ORDER BY n.created_at DESC";
}

$stmt = $conn->prepare($notesSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$notes = $stmt->get_result();

$subjects = [];
if ($deptId) {
    $subStmt = $conn->prepare("SELECT * FROM subjects WHERE department_id = ? ORDER BY name");
    $subStmt->bind_param("i", $deptId);
    $subStmt->execute();
    $subjects = $subStmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $search ? "Search: " . htmlspecialchars($search) : ($department ? htmlspecialchars($department['name']) : 'All Notes') ?> - <?= $siteName ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="container">
        <?php if ($department): ?>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="departments.php?category_id=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a>
            <i class="bi bi-chevron-right"></i>
            <span><?= htmlspecialchars($department['name']) ?></span>
        </div>
        <?php else: ?>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= $search ? "Search: " . htmlspecialchars($search) : 'All Notes' ?></span>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>
                <?php if ($search): ?>
                    Search Results for "<?= htmlspecialchars($search) ?>"
                <?php elseif ($department): ?>
                    <?= htmlspecialchars($department['name']) ?>
                <?php else: ?>
                    All Notes
                <?php endif; ?>
            </h1>
            <div class="search-filter">
                <form action="" method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <?php if ($deptId): ?>
                    <input type="hidden" name="dept_id" value="<?= $deptId ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search notes..." value="<?= htmlspecialchars($search) ?>" id="liveSearch">
                    <select name="sort">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="title" <?= $sortBy === 'title' ? 'selected' : '' ?>>Title A-Z</option>
                        <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Search</button>
                </form>
            </div>
        </div>

        <?php if ($notes->num_rows > 0): ?>
        <div class="list-grid">
            <?php while ($note = $notes->fetch_assoc()): ?>
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
                        <?php if (isLoggedIn()): ?>
                            <?php if (!empty($note['is_saved'])): ?>
                            <form action="<?= $basePath ?>actions/unsave_note.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                <button type="submit" class="card-save-btn is-saved" title="Saved (Click to remove)">
                                    <i class="bi bi-bookmark-fill"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form action="<?= $basePath ?>actions/save_note.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
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
                        <span><i class="bi bi-chat-dots"></i> <?= $note['comment_count'] ?> comments</span>
                        <span><i class="bi bi-clock"></i> <?= timeAgo($note['created_at']) ?></span>
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
            <i class="bi bi-journal-text"></i>
            <h3>No Notes Found</h3>
            <p><?= $search ? "No results match your search. Try different keywords." : "No notes have been uploaded yet." ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
