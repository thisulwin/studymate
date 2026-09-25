<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?= $basePath ?>admin/index.php" class="sidebar-brand">
            <i class="bi bi-journal-bookmark-fill"></i> Admin Panel
        </a>
        <button class="sidebar-close" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <ul class="sidebar-links">
        <li><a href="<?= $basePath ?>admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li><a href="<?= $basePath ?>admin/categories.php"><i class="bi bi-folder"></i> Categories</a></li>
        <li><a href="<?= $basePath ?>admin/departments.php"><i class="bi bi-building"></i> Departments</a></li>
        <li><a href="<?= $basePath ?>admin/subjects.php"><i class="bi bi-book"></i> Subjects</a></li>
        <li><a href="<?= $basePath ?>admin/notes.php"><i class="bi bi-file-earmark-pdf"></i> Notes</a></li>
        <li><a href="<?= $basePath ?>admin/messages.php"><i class="bi bi-envelope"></i> Messages</a></li>
        <li class="sidebar-divider"></li>
        <li><a href="<?= $basePath ?>index.php"><i class="bi bi-house"></i> View Site</a></li>
        <li><a href="<?= $basePath ?>auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</div>
<button class="sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
    <i class="bi bi-list"></i>
</button>
