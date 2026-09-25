<nav class="navbar">
    <div class="container">
        <a href="<?= $basePath ?>index.php" class="navbar-brand">
            <i class="bi bi-journal-bookmark-fill"></i>
            <?= $siteName ?? 'StudyMate' ?>
        </a>
        <button class="hamburger" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <ul class="nav-links">
            <li><a href="<?= $basePath ?>index.php">Home</a></li>
            <li><a href="<?= $basePath ?>subjects.php">All Notes</a></li>
            <li><a href="<?= $basePath ?>saved_notes.php">Save Note</a></li>
            <li><a href="<?= $basePath ?>contact.php">Contact</a></li>
            <?php if (isLoggedIn()): ?>
            <li class="profile-menu-wrap">
                <button class="profile-toggle" id="profileToggle">
                    <?php if (($_SESSION['profile_image'] ?? null) && file_exists($_SESSION['profile_image'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="" class="profile-avatar">
                    <?php else: ?>
                    <span class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                    <?php endif; ?>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="profile-dropdown">
                    <div class="dropdown-header">
                        <?php if (($_SESSION['profile_image'] ?? null) && file_exists($_SESSION['profile_image'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="" class="profile-avatar-lg">
                        <?php else: ?>
                        <span class="profile-avatar-lg"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div>
                            <div class="dropdown-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
                            <div class="dropdown-email"><?= htmlspecialchars($_SESSION['email']) ?></div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $basePath ?>saved_notes.php" class="dropdown-item"><i class="bi bi-bookmark-heart"></i> Save Note</a>
                    <a href="<?= $basePath ?>account.php" class="dropdown-item"><i class="bi bi-person-gear"></i> Account Settings</a>
                    <?php if (isAdmin()): ?>
                    <a href="<?= $basePath ?>admin/index.php" class="dropdown-item"><i class="bi bi-speedometer2"></i> Admin Panel</a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $basePath ?>auth/logout.php" class="dropdown-item dropdown-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </li>
            <?php else: ?>
            <li><a href="<?= $basePath ?>auth/login.php">Login</a></li>
            <li><a href="<?= $basePath ?>auth/register.php" class="btn-nav"><i class="bi bi-person-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
