<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4><i class="bi bi-journal-bookmark-fill"></i> <?= $siteName ?? 'StudyMate' ?></h4>
                <p>A public academic resource platform for university students. Access study materials, lecture notes, and past papers organized by department and subject.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= $basePath ?>index.php">Home</a></li>
                    <li><a href="<?= $basePath ?>subjects.php">All Notes</a></li>
                    <li><a href="<?= $basePath ?>saved_notes.php">Save Note</a></li>
                    <li><a href="<?= $basePath ?>contact.php">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Account</h4>
                <ul>
                    <li><a href="<?= $basePath ?>auth/login.php">Login</a></li>
                    <li><a href="<?= $basePath ?>auth/register.php">Register</a></li>
                    <li><a href="<?= $basePath ?>saved_notes.php">Save Note</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= $siteName ?? 'StudyMate' ?>. Mini Project.</p>
        </div>
    </div>
</footer>
