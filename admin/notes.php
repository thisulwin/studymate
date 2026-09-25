<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$success = flash('admin_success');
$error = flash('admin_error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $noteAction = $_POST['action'] ?? 'upload';

    if ($noteAction === 'delete') {
        $id = (int)($_POST['delete_id'] ?? 0);

        $stmt = $conn->prepare("SELECT pdf_path, thumbnail_path FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $note = $stmt->get_result()->fetch_assoc();

        if ($note) {
            $pdfFull = '../' . $note['pdf_path'];
            if (file_exists($pdfFull)) unlink($pdfFull);

            if ($note['thumbnail_path']) {
                $thumbFull = '../' . $note['thumbnail_path'];
                if (file_exists($thumbFull)) unlink($thumbFull);
            }

            $stmt2 = $conn->prepare("DELETE FROM notes WHERE id = ?");
            $stmt2->bind_param("i", $id);
            if ($stmt2->execute() && $stmt2->affected_rows > 0) {
                flash('admin_success', 'Note deleted');
            } else {
                flash('admin_error', 'Failed to delete note');
            }
        } else {
            flash('admin_error', 'Note not found');
        }

        header("Location: notes.php");
        exit();
    }

    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($subjectId)) {
        flash('admin_error', 'Title and subject are required');
        header("Location: notes.php");
        exit();
    }

    if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        flash('admin_error', 'Please upload a valid PDF file');
        header("Location: notes.php");
        exit();
    }

    $file = $_FILES['pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        flash('admin_error', 'Only PDF files are allowed');
        header("Location: notes.php");
        exit();
    }

    if ($file['size'] > 50 * 1024 * 1024) {
        flash('admin_error', 'File size must be less than 50MB');
        header("Location: notes.php");
        exit();
    }

    $pdfName = uniqid('note_') . '.pdf';
    $pdfPath = '../uploads/pdfs/' . $pdfName;

    if (move_uploaded_file($file['tmp_name'], $pdfPath)) {
        $thumbRelative = null;

        $thumbnailDataUrl = $_POST['thumbnail_data'] ?? '';
        if (!empty($thumbnailDataUrl) && preg_match('/^data:image\/(\w+);base64,/', $thumbnailDataUrl, $matches)) {
            $thumbName = 'thumb_' . $pdfName . '.' . $matches[1];
            $thumbPath = '../uploads/thumbnails/' . $thumbName;
            $thumbRelative = 'uploads/thumbnails/' . $thumbName;

            $imageData = base64_decode(substr($thumbnailDataUrl, strpos($thumbnailDataUrl, ',') + 1));
            file_put_contents($thumbPath, $imageData);
        }

        $pdfRelative = 'uploads/pdfs/' . $pdfName;
        $uploadedBy = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO notes (subject_id, title, description, pdf_path, thumbnail_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $subjectId, $title, $description, $pdfRelative, $thumbRelative, $uploadedBy);

        if ($stmt->execute()) {
            $noteId = $conn->insert_id;

            $tagsInput = trim($_POST['tags'] ?? '');
            if (!empty($tagsInput)) {
                $tags = array_unique(array_map('trim', explode(',', $tagsInput)));
                $tagStmt = $conn->prepare("INSERT INTO note_tags (note_id, tag) VALUES (?, ?)");
                foreach ($tags as $tag) {
                    $tag = mb_strtolower($tag);
                    if (strlen($tag) >= 2 && strlen($tag) <= 50) {
                        $tagStmt->bind_param("is", $noteId, $tag);
                        $tagStmt->execute();
                    }
                }
            }

            flash('admin_success', 'Note uploaded successfully');
        } else {
            flash('admin_error', 'Failed to save note to database');
        }
    } else {
        flash('admin_error', 'Failed to upload file');
    }

    header("Location: notes.php");
    exit();
}

$notes = $conn->query("SELECT n.*, s.name as subject_name, s.code as subject_code, d.name as dept_name, u.username as uploader,
    (SELECT GROUP_CONCAT(nt.tag SEPARATOR ', ') FROM note_tags nt WHERE nt.note_id = n.id) as tags
    FROM notes n JOIN subjects s ON n.subject_id = s.id JOIN departments d ON s.department_id = d.id LEFT JOIN users u ON n.uploaded_by = u.id ORDER BY n.created_at DESC");

$subjects = $conn->query("SELECT s.*, d.name as dept_name, c.name as cat_name FROM subjects s JOIN departments d ON s.department_id = d.id JOIN categories c ON d.category_id = c.id ORDER BY c.name, d.name, s.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes - Admin</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
</head>
<body>

<?php include '../includes/admin_nav.php'; ?>

<div class="admin-main">
<div class="page-wrapper">
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-file-earmark-pdf" style="color:var(--primary);"></i> Notes</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="card-box" style="margin-bottom:2rem;">
            <h3 style="margin-bottom:1rem;">Upload New Note</h3>
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="upload">
                <div class="grid-form-table-wide">
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">Select Subject</option>
                            <?php while ($sub = $subjects->fetch_assoc()): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['cat_name'] . ' > ' . $sub['dept_name'] . ' > ' . $sub['name'] . ' (' . $sub['code'] . ')') ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Note title">
                    </div>
                    <div class="form-group">
                        <label>PDF File (max 50MB)</label>
                        <input type="file" name="pdf" id="pdfUpload" class="form-control" accept=".pdf" required>
                        <input type="hidden" name="thumbnail_data" id="thumbnailData">
                        <div id="thumbPreview" style="margin-top:0.5rem;display:none;">
                            <small style="color:var(--gray);">Auto-extracted thumbnail:</small><br>
                            <img id="thumbImg" src="" alt="Thumbnail preview" style="max-width:120px;max-height:160px;border-radius:6px;border:1px solid var(--light-gray);margin-top:0.25rem;">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the note"></textarea>
                </div>
                <div class="form-group">
                    <label>Search Tags (makes notes easier to find)</label>
                    <div class="tag-input-wrapper" id="tagInputWrapper">
                        <div class="tag-chips" id="tagChips"></div>
                        <input type="text" id="tagInput" class="tag-input-field" placeholder="Type a tag and press Enter">
                    </div>
                    <input type="hidden" name="tags" id="tagsHidden">
                    <small style="color:var(--gray);font-size:0.8rem;">Type a tag and press Enter. Click X to remove. Tags help students find notes through search.</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Note</button>
            </form>
        </div>

        <div class="card-box" style="overflow-x:auto;">
            <h3 style="margin-bottom:1rem;">All Notes</h3>
            <input type="text" id="liveSearch" placeholder="Search notes..." class="form-control" style="margin-bottom:1rem; max-width:300px;">
            <table class="admin-table">
                <thead>
                    <tr><th>Title</th><th>Tags</th><th>Subject</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($note = $notes->fetch_assoc()): ?>
                    <tr class="filterable-item">
                        <td><strong><?= htmlspecialchars($note['title']) ?></strong></td>
                        <td>
                            <?php if ($note['tags']): ?>
                            <div class="note-tags">
                                <?php foreach (explode(', ', $note['tags']) as $tag): ?>
                                <span class="note-tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span style="color:var(--gray);font-size:0.8rem;">No tags</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($note['subject_name']) ?> (<?= htmlspecialchars($note['subject_code']) ?>)</td>
                        <td><?= htmlspecialchars($note['dept_name']) ?></td>
                        <td><?= htmlspecialchars($note['uploader'] ?? 'N/A') ?></td>
                        <td><?= timeAgo($note['created_at']) ?></td>
                        <td class="actions">
                            <a href="<?= $basePath ?>view_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-ghost" target="_blank"><i class="bi bi-eye"></i></a>
                            <?php $delMsg = htmlspecialchars(json_encode("Delete \"{$note['title']}\"?\n\nThis will also remove its file, comments and saved copies.", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm(<?= $delMsg ?>);">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_id" value="<?= $note['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script src="../js/script.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

var pdfUpload = document.getElementById('pdfUpload');
var thumbPreview = document.getElementById('thumbPreview');
var thumbImg = document.getElementById('thumbImg');
var thumbnailData = document.getElementById('thumbnailData');

if (pdfUpload) {
    pdfUpload.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file || file.type !== 'application/pdf') return;

        thumbPreview.style.display = 'none';
        thumbnailData.value = '';

        var reader = new FileReader();
        reader.onload = function(ev) {
            var typedarray = new Uint8Array(ev.target.result);
            pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({ scale: scale });
                    var canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    var ctx = canvas.getContext('2d');

                    page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                        var dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                        thumbImg.src = dataUrl;
                        thumbPreview.style.display = 'block';
                        thumbnailData.value = dataUrl;
                    });
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });
}
</script>
</body>
</html>
