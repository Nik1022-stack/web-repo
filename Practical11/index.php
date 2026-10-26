<?php
require_once __DIR__ . '/db.php';

$message = '';
$message_type = '';

// ==========================================
// 1. INTERMEDIATE EXTENSION: Export to CSV
// ==========================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $search = trim($_GET['search'] ?? '');
    $course = trim($_GET['course'] ?? '');

    $sql = "SELECT id, student_code, first_name, last_name, email, phone, course, status, created_at FROM students WHERE 1=1";
    $types = "";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ?)";
        $searchTerm = "%$search%";
        $types .= "ssss";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($course !== '') {
        $sql .= " AND course = ?";
        $types .= "s";
        $params[] = $course;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_list.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Student Code', 'First Name', 'Last Name', 'Email', 'Phone', 'Course', 'Status', 'Date Added']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// ==========================================
// 2. DELETE (Permanent Delete)
// ==========================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Student record deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Error deleting student: " . $conn->error;
        $message_type = "danger";
    }
    $stmt->close();
}

// ==========================================
// 3. ADVANCED EXTENSION: Soft Delete & Restore
// ==========================================
if (isset($_GET['soft_delete'])) {
    $id = (int)$_GET['soft_delete'];
    $stmt = $conn->prepare("UPDATE students SET status = 'Deleted' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Student marked as Deleted (Soft Delete).";
        $message_type = "success";
    }
    $stmt->close();
}

if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $stmt = $conn->prepare("UPDATE students SET status = 'Active' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Student restored to Active status.";
        $message_type = "success";
    }
    $stmt->close();
}

// ==========================================
// 4. INSERT (Add Student)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $code       = trim($_POST['student_code']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $course     = trim($_POST['course']);
    $status     = trim($_POST['status'] ?? 'Active');

    if ($code !== '' && $first_name !== '' && $last_name !== '' && $email !== '' && $course !== '') {
        $stmt = $conn->prepare("INSERT INTO students (student_code, first_name, last_name, email, phone, course, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $code, $first_name, $last_name, $email, $phone, $course, $status);
        if ($stmt->execute()) {
            $message = "Student '$first_name $last_name' added successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to add student: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    }
}

// ==========================================
// 5. UPDATE (Edit Student)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $id         = (int)$_POST['id'];
    $code       = trim($_POST['student_code']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $course     = trim($_POST['course']);
    $status     = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE students SET student_code = ?, first_name = ?, last_name = ?, email = ?, phone = ?, course = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $code, $first_name, $last_name, $email, $phone, $course, $status, $id);
    if ($stmt->execute()) {
        $message = "Student details updated successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to update student: " . $conn->error;
        $message_type = "danger";
    }
    $stmt->close();
}

// ==========================================
// 6. FETCH STUDENT FOR EDITING (if requested)
// ==========================================
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ==========================================
// 7. SEARCH & FILTER (SELECT Students)
// ==========================================
$search = trim($_GET['search'] ?? '');
$course = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'All');

$sql = "SELECT * FROM students WHERE 1=1";
$types = "";
$params = [];

if ($search !== '') {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $searchTerm = "%$search%";
    $types .= "ssss";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($course !== '') {
    $sql .= " AND course = ?";
    $types .= "s";
    $params[] = $course;
}

if ($status_filter !== '' && $status_filter !== 'All') {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get list of courses for dropdown
$courses_result = $conn->query("SELECT DISTINCT course FROM students ORDER BY course ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 11: Student Management CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Student Management System</h1>

        <!-- Feedback Message -->
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Student Form: Add or Edit -->
        <div class="card">
            <h2><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h2>
            <form method="POST" action="index.php">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>Student Code *</label>
                        <input type="text" name="student_code" required value="<?= htmlspecialchars($edit_student['student_code'] ?? '') ?>" placeholder="e.g. STU001">
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required value="<?= htmlspecialchars($edit_student['first_name'] ?? '') ?>" placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" required value="<?= htmlspecialchars($edit_student['last_name'] ?? '') ?>" placeholder="Last Name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($edit_student['email'] ?? '') ?>" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="text" name="phone" required value="<?= htmlspecialchars($edit_student['phone'] ?? '') ?>" placeholder="Phone number">
                    </div>
                    <div class="form-group">
                        <label>Course *</label>
                        <input type="text" name="course" required value="<?= htmlspecialchars($edit_student['course'] ?? '') ?>" placeholder="e.g. Computer Science">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Active" <?= (($edit_student['status'] ?? '') === 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= (($edit_student['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="Deleted" <?= (($edit_student['status'] ?? '') === 'Deleted') ? 'selected' : '' ?>>Deleted</option>
                        </select>
                    </div>
                </div>

                <div>
                    <?php if ($edit_student): ?>
                        <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_student" class="btn btn-success">+ Add Student</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search & Filter Card -->
        <div class="card">
            <h2>Search and Filter</h2>
            <form method="GET" action="index.php" class="filter-bar">
                <input type="text" name="search" placeholder="Search by name, email, or course..." value="<?= htmlspecialchars($search) ?>">

                <select name="course">
                    <option value="">All Courses</option>
                    <?php while ($c = $courses_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['course']) ?>" <?= ($course === $c['course']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['course']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="status">
                    <option value="All" <?= ($status_filter === 'All') ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Active" <?= ($status_filter === 'Active') ? 'selected' : '' ?>>Active Only</option>
                    <option value="Inactive" <?= ($status_filter === 'Inactive') ? 'selected' : '' ?>>Inactive Only</option>
                    <option value="Deleted" <?= ($status_filter === 'Deleted') ? 'selected' : '' ?>>Deleted Only</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">Search / Filter</button>
                <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
                <a href="index.php?export=csv<?= ($search ? '&search=' . urlencode($search) : '') ?><?= ($course ? '&course=' . urlencode($course) : '') ?>" class="btn btn-warning btn-sm" style="margin-left: auto;">Export to CSV</a>
            </form>
        </div>

        <!-- Students Records Table -->
        <div class="card">
            <h2>Student Records (<?= $students->num_rows ?> Found)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students->num_rows === 0): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #6b7280; padding: 20px;">
                                    No student records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($row['student_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['course']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($row['status']) ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <!-- Edit -->
                                        <a href="index.php?edit=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>

                                        <!-- Soft Delete / Restore -->
                                        <?php if ($row['status'] !== 'Deleted'): ?>
                                            <a href="index.php?soft_delete=<?= $row['id'] ?>" class="btn btn-warning btn-sm" onclick="return confirm('Mark this student as deleted (Soft Delete)?');">Soft Delete</a>
                                        <?php else: ?>
                                            <a href="index.php?restore=<?= $row['id'] ?>" class="btn btn-success btn-sm">Restore</a>
                                        <?php endif; ?>

                                        <!-- Permanent Delete -->
                                        <a href="index.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this student record?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
