<?php
require_once __DIR__ . '/db.php';

$message = '';
$message_type = '';

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// ========================================================
// POSTER VALIDATION & UPLOAD FUNCTION
// Validates: File presence, upload error, file size, MIME type, extension
// Advanced Extension: Safe image renaming
// ========================================================
function validateAndUploadPoster(array $file, string $upload_dir, string &$error): string|null|false
{
    // If no file was selected
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload failed with error code: " . $file['error'];
        return false;
    }

    // 1. File Size Validation (Max 2MB)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        $fileSizeMB = round($file['size'] / (1024 * 1024), 2);
        $error = "Poster size ($fileSizeMB MB) exceeds maximum allowed size of 2MB.";
        return false;
    }

    // 2. MIME Type Validation
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_mimes, true)) {
        $error = "Invalid file type ($mime). Only JPG, PNG, and WEBP images are allowed.";
        return false;
    }

    // 3. Extension Validation
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed_exts, true)) {
        $error = "Invalid file extension (.$ext). Only JPG, PNG, and WEBP files are allowed.";
        return false;
    }

    // 4. Advanced Extension: Safe Image Renaming
    $new_filename = 'poster_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename;
    } else {
        $error = "Failed to save uploaded poster file.";
        return false;
    }
}

// ========================================================
// 1. DELETE EVENT
// ========================================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Get event details and poster to remove file
    $stmt = $conn->prepare("SELECT title, poster FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($event) {
        if (!empty($event['poster']) && file_exists($upload_dir . $event['poster'])) {
            unlink($upload_dir . $event['poster']);
        }

        $del = $conn->prepare("DELETE FROM events WHERE id = ?");
        $del->bind_param("i", $id);
        if ($del->execute()) {
            logAuditEvent($conn, $id, 'DELETED', "Event '{$event['title']}' (ID: $id) was deleted.");
            $message = "Event '{$event['title']}' deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Error deleting event: " . $conn->error;
            $message_type = "danger";
        }
        $del->close();
    }
}

// ========================================================
// 2. TOGGLE STATUS (Open / Closed)
// ========================================================
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $stmt = $conn->prepare("SELECT title, status FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($event) {
        $new_status = ($event['status'] === 'Open') ? 'Closed' : 'Open';
        $up = $conn->prepare("UPDATE events SET status = ? WHERE id = ?");
        $up->bind_param("si", $new_status, $id);
        if ($up->execute()) {
            logAuditEvent($conn, $id, 'UPDATED', "Status of '{$event['title']}' changed to $new_status.");
            $message = "Event status updated to <strong>$new_status</strong>.";
            $message_type = "success";
        }
        $up->close();
    }
}

// ========================================================
// 3. INSERT (Add Event with Poster Upload)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $event_date  = trim($_POST['event_date'] ?? '');
    $venue       = trim($_POST['venue'] ?? '');
    $status      = trim($_POST['status'] ?? 'Open');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $category === '' || $event_date === '' || $venue === '') {
        $message = "Please fill in all required fields (Title, Category, Date, Venue).";
        $message_type = "danger";
    } else {
        $upload_error = '';
        $poster_filename = validateAndUploadPoster($_FILES['poster'], $upload_dir, $upload_error);

        if ($poster_filename === false) {
            $message = $upload_error;
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, venue, poster, status, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $title, $category, $event_date, $venue, $poster_filename, $status, $description);
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                logAuditEvent($conn, $new_id, 'CREATED', "Event '$title' was created with status $status.");
                $message = "Event '<strong>$title</strong>' created successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to add event: " . $conn->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// ========================================================
// 4. UPDATE (Edit Event)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $id          = (int)$_POST['id'];
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $event_date  = trim($_POST['event_date'] ?? '');
    $venue       = trim($_POST['venue'] ?? '');
    $status      = trim($_POST['status'] ?? 'Open');
    $description = trim($_POST['description'] ?? '');
    $old_poster  = trim($_POST['old_poster'] ?? '');

    if ($title === '' || $category === '' || $event_date === '' || $venue === '') {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        $upload_error = '';
        $new_poster = validateAndUploadPoster($_FILES['poster'], $upload_dir, $upload_error);

        if ($new_poster === false) {
            $message = $upload_error;
            $message_type = "danger";
        } else {
            // Determine final poster file
            $final_poster = $old_poster;
            if ($new_poster !== null) {
                // Remove old poster file if replacement succeeded
                if (!empty($old_poster) && file_exists($upload_dir . $old_poster)) {
                    unlink($upload_dir . $old_poster);
                }
                $final_poster = $new_poster;
            }

            $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, poster = ?, status = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $title, $category, $event_date, $venue, $final_poster, $status, $description, $id);
            if ($stmt->execute()) {
                logAuditEvent($conn, $id, 'UPDATED', "Event '$title' was updated.");
                $message = "Event '<strong>$title</strong>' updated successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to update event: " . $conn->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// ========================================================
// 5. FETCH EVENT FOR EDITING (if requested)
// ========================================================
$edit_event = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_event = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ========================================================
// 6. SEARCH & FILTER (SELECT Events) - Intermediate Extension
// ========================================================
$search = trim($_GET['search'] ?? '');
$filter_category = trim($_GET['category'] ?? '');
$filter_status = trim($_GET['status'] ?? 'All');

$sql = "SELECT * FROM events WHERE 1=1";
$types = "";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR venue LIKE ? OR description LIKE ?)";
    $term = "%$search%";
    $types .= "sss";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filter_category !== '') {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $filter_category;
}

if ($filter_status !== '' && $filter_status !== 'All') {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $filter_status;
}

$sql .= " ORDER BY event_date ASC, id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();

// Get recent audit logs (Advanced Extension)
$audit_logs = $conn->query("SELECT * FROM event_audit_log ORDER BY id DESC LIMIT 5");

// Predefined categories
$categories = ['Technical', 'Workshop', 'Cultural', 'Sports', 'Seminar'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 12: Event Management CRUD & Poster Upload</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Event Management System</h1>

        <!-- Feedback Alert Messages -->
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Event Form (Add or Edit) -->
        <div class="card">
            <h2><?= $edit_event ? 'Edit Event Details' : 'Add New Event' ?></h2>
            <form method="POST" action="index.php" enctype="multipart/form-data">
                <?php if ($edit_event): ?>
                    <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                    <input type="hidden" name="old_poster" value="<?= htmlspecialchars($edit_event['poster'] ?? '') ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group" style="flex: 2 1 300px;">
                        <label>Event Title *</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($edit_event['title'] ?? '') ?>" placeholder="e.g. Annual Tech Symposium">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= (($edit_event['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Event Date *</label>
                        <input type="date" name="event_date" required value="<?= htmlspecialchars($edit_event['event_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 2 1 300px;">
                        <label>Venue / Location *</label>
                        <input type="text" name="venue" required value="<?= htmlspecialchars($edit_event['venue'] ?? '') ?>" placeholder="e.g. Main Auditorium">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Open" <?= (($edit_event['status'] ?? '') === 'Open') ? 'selected' : '' ?>>Open</option>
                            <option value="Closed" <?= (($edit_event['status'] ?? '') === 'Closed') ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 2 1 300px;">
                        <label>Event Poster (JPG, PNG, WEBP - Max 2MB)</label>
                        <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
                        <div class="file-hint">Allowed: JPG, PNG, WEBP. Maximum size: 2MB.</div>

                        <?php if ($edit_event && !empty($edit_event['poster']) && file_exists($upload_dir . $edit_event['poster'])): ?>
                            <div class="current-poster-preview">
                                <img src="uploads/<?= htmlspecialchars($edit_event['poster']) ?>" alt="Current Poster" class="poster-thumb">
                                <div>
                                    <small><strong>Current Poster:</strong> <?= htmlspecialchars($edit_event['poster']) ?></small><br>
                                    <small style="color: #6b7280;">Upload a new image to replace it, or leave blank to keep current.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" placeholder="Brief event summary and agenda..."><?= htmlspecialchars($edit_event['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div>
                    <?php if ($edit_event): ?>
                        <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_event" class="btn btn-success">+ Add Event</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search & Filter Card (Intermediate Extension) -->
        <div class="card">
            <h2>Search & Filter Events</h2>
            <form method="GET" action="index.php" class="filter-bar">
                <input type="text" name="search" placeholder="Search by title, venue, or description..." value="<?= htmlspecialchars($search) ?>">

                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($filter_category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="status">
                    <option value="All" <?= ($filter_status === 'All') ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Open" <?= ($filter_status === 'Open') ? 'selected' : '' ?>>Open Only</option>
                    <option value="Closed" <?= ($filter_status === 'Closed') ? 'selected' : '' ?>>Closed Only</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>
        </div>

        <!-- Events List Table -->
        <div class="card">
            <h2>Scheduled Events (<?= $events->num_rows ?> Found)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title & Description</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6b7280; padding: 20px;">
                                    No events found matching your search.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $events->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['poster']) && file_exists($upload_dir . $row['poster'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['poster']) ?>" target="_blank" title="View Full Poster">
                                                <img src="uploads/<?= htmlspecialchars($row['poster']) ?>" alt="Poster" class="poster-thumb">
                                            </a>
                                        <?php else: ?>
                                            <div class="poster-placeholder">No Poster</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['title']) ?></strong>
                                        <?php if (!empty($row['description'])): ?>
                                            <div style="font-size: 12px; color: #6b7280; margin-top: 3px;">
                                                <?= htmlspecialchars(mb_strimwidth($row['description'], 0, 75, '...')) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="category-tag"><?= htmlspecialchars($row['category']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['event_date']) ?></td>
                                    <td><?= htmlspecialchars($row['venue']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($row['status']) ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <!-- Edit -->
                                        <a href="index.php?edit=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>

                                        <!-- Toggle Status (Open/Closed) -->
                                        <a href="index.php?toggle_status=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Toggle Open/Closed">
                                            <?= ($row['status'] === 'Open') ? 'Close' : 'Reopen' ?>
                                        </a>

                                        <!-- Delete with Confirmation -->
                                        <a href="index.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this event and its poster?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity / Audit Log (Advanced Extension) -->
        <div class="card">
            <h2>Recent Event Activity (Audit Log)</h2>
            <ul class="audit-list">
                <?php if ($audit_logs && $audit_logs->num_rows > 0): ?>
                    <?php while ($log = $audit_logs->fetch_assoc()): ?>
                        <li class="audit-item">
                            <div>
                                <span class="audit-badge audit-<?= htmlspecialchars($log['action']) ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                                <span style="margin-left: 8px;"><?= htmlspecialchars($log['details']) ?></span>
                            </div>
                            <small style="color: #6b7280;"><?= htmlspecialchars($log['created_at']) ?></small>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li style="color: #6b7280; padding: 8px;">No activity logged yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
