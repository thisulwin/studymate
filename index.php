<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM departments WHERE category_id = c.id) as dept_count FROM categories c ORDER BY c.name");
$totalNotes = getTotalNotes($conn);
$totalUsers = getTotalUsers($conn);
$catCount = $conn->query("SELECT COUNT(*) as cnt FROM categories")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $siteName ?> - University Notes Platform</title>
    <link rel="stylesheet" href="css/style.css?v=20260922191220">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<section class="hero">
    <div class="container">
        <h1>University Notes Library</h1>
        <p>Access study materials, lecture notes, and past papers across all departments. Your one-stop academic resource.</p>
        <div class="search-box">
            <form action="subjects.php" method="GET">
                <input type="text" name="search" placeholder="Search notes, subjects, or departments...">
                <button type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="stats">
            <div class="stat-item">
                <span class="number"><?= $catCount ?></span>
                <span class="label">Categories</span>
            </div>
            <div class="stat-item">
                <span class="number"><?= $totalNotes ?></span>
                <span class="label">Notes Available</span>
            </div>
            <div class="stat-item">
                <span class="number"><?= $totalUsers ?></span>
                <span class="label">Registered Users</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Browse by Category</h2>
            <p>Select a subject category to explore departments and notes</p>
        </div>
        <div class="categories-grid">
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="departments.php?category_id=<?= $cat['id'] ?>" class="category-card fade-in">
                <div class="icon">
                    <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                </div>
                <h3><?= htmlspecialchars($cat['name']) ?></h3>
                <p><?= htmlspecialchars($cat['description']) ?></p>
                <span class="count">
                    <i class="bi bi-building"></i>
                    <?= $cat['dept_count'] ?> Department<?= $cat['dept_count'] != 1 ? 's' : '' ?>
                </span>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="section" style="background: var(--white); padding: 4rem 0;">
    <div class="container">
        <div class="section-title">
            <h2>How It Works</h2>
            <p>Three simple steps to access study materials</p>
        </div>
        <div class="categories-grid">
            <div class="category-card fade-in" style="border-top: 4px solid var(--primary);">
                <div class="icon" style="background: linear-gradient(135deg, #818cf8, #4f46e5);">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h3>1. Choose Category</h3>
                <p>Browse through our organized subject categories</p>
            </div>
            <div class="category-card fade-in" style="border-top: 4px solid var(--secondary);">
                <div class="icon" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9);">
                    <i class="bi bi-search"></i>
                </div>
                <h3>2. Find Notes</h3>
                <p>Search and filter to find exactly what you need</p>
            </div>
            <div class="category-card fade-in" style="border-top: 4px solid var(--success);">
                <div class="icon" style="background: linear-gradient(135deg, #34d399, #10b981);">
                    <i class="bi bi-book-half"></i>
                </div>
                <h3>3. Download & Learn</h3>
                <p>Download PDF notes directly, save for later</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
