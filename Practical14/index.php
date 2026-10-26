<?php
session_start();
require_once __DIR__ . '/db.php';

// Access Control: Redirect to login if unauthenticated
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['user'];
$current_role = $_SESSION['role'] ?? 'student';
$message = '';

// Intermediate Extension: Toggle User Status (Admin Only)
if (isset($_GET['toggle_user']) && $current_role === 'admin') {
    $uid = (int)$_GET['toggle_user'];
    $stmt = $conn->prepare("SELECT username, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $target_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($target_user) {
        $new_status = ($target_user['status'] === 'Active') ? 'Inactive' : 'Active';
        $up = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $up->bind_param("si", $new_status, $uid);
        $up->execute();
        $up->close();

        addAudit($conn, $current_user, "Toggled status of user '{$target_user['username']}' to $new_status.");
        $message = "User '{$target_user['username']}' status changed to <strong>$new_status</strong>.";
    }
}

// 1. Dynamic Menu based on Role (RBAC)
$menu = ($current_role === 'admin')
    ? ['Dashboard' => '#top', 'Users' => '#users-sec', 'Registrations' => '#reg-sec', 'Analytics' => '#chart-sec', 'Audit Logs' => '#audit-sec']
    : ['Dashboard' => '#top', 'My Registrations' => '#reg-sec'];

// 2. SQL Aggregations for Dashboard Metrics
$user_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$student_count = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'] ?? 0;
$event_count = $conn->query("SELECT COUNT(*) AS c FROM events")->fetch_assoc()['c'] ?? 0;
$reg_count = $conn->query("SELECT COUNT(*) AS c FROM registrations")->fetch_assoc()['c'] ?? 0;

// 3. Latest Registrations Query
$latest_regs = $conn->query("SELECT * FROM registrations ORDER BY id DESC LIMIT 5");

// 4. Advanced Extension: Event Registrations by Month Analytics
$monthly_chart = $conn->query("SELECT DATE_FORMAT(registered_at, '%b %Y') as month, COUNT(*) as count FROM registrations GROUP BY month ORDER BY MIN(registered_at) ASC");
$max_monthly = 1;
$chart_data = [];
while ($m = $monthly_chart->fetch_assoc()) {
    $chart_data[] = $m;
    if ($m['count'] > $max_monthly) {
        $max_monthly = $m['count'];
    }
}

// 5. Users List (for Intermediate Extension: Toggle Status)
$users_list = $conn->query("SELECT id, username, role, status FROM users ORDER BY id ASC");

// 6. Recent Audit Logs
$audit_logs = $conn->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 14: Admin Dashboard & RBAC</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">
    <!-- Navbar with Dynamic Role-Based Menu -->
    <header class="navbar">
        <div class="nav-brand">Admin Portal</div>
        <ul class="nav-menu">
            <?php foreach ($menu as $label => $link): ?>
                <li><a href="<?= $link ?>"><?= $label ?></a></li>
            <?php endforeach; ?>
            <li>
                <span class="user-badge"><?= htmlspecialchars($current_user) ?> (<?= htmlspecialchars($current_role) ?>)</span>
            </li>
            <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
        </ul>
    </header>

    <div class="container">
        <!-- Access Restriction Alert for Non-Admin -->
        <?php if ($current_role !== 'admin'): ?>
            <div class="alert alert-danger" style="margin-top: 15px;">
                ⚠️ <strong>Access Restricted:</strong> You are signed in with the <strong><?= htmlspecialchars($current_role) ?></strong> role. Administrative actions and user moderation tools are restricted to Admin accounts.
            </div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <!-- 1. Dynamic Metric Cards (SQL Aggregations) -->
        <h2 style="margin: 20px 0 10px;">System Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="val"><?= $user_count ?></div>
                <div class="label">Total System Users</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $student_count ?></div>
                <div class="label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $event_count ?></div>
                <div class="label">Active Events</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $reg_count ?></div>
                <div class="label">Total Registrations</div>
            </div>
        </div>

        <!-- 2. Advanced Extension: Event Registrations Analytics Chart -->
        <div class="card" id="chart-sec">
            <h2>Monthly Event Registrations (Analytics Chart)</h2>
            <div class="chart-container">
                <?php foreach ($chart_data as $point): ?>
                    <?php $bar_height = max(15, round(($point['count'] / $max_monthly) * 110)); ?>
                    <div class="chart-col">
                        <div class="chart-val"><?= $point['count'] ?></div>
                        <div class="chart-bar" style="height: <?= $bar_height ?>px;"></div>
                        <div class="chart-label"><?= $point['month'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. Latest Registrations Table -->
        <div class="card" id="reg-sec">
            <h2>Latest Event Registrations</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Event Title</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reg = $latest_regs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $reg['id'] ?></td>
                            <td><?= htmlspecialchars($reg['student_name']) ?></td>
                            <td><?= htmlspecialchars($reg['event_title']) ?></td>
                            <td><?= htmlspecialchars($reg['registered_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 4. User Management with Status Toggle (Intermediate Extension - Admin Only) -->
        <?php if ($current_role === 'admin'): ?>
            <div class="card" id="users-sec">
                <h2>User Management (Active / Inactive Status Toggle)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td><?= htmlspecialchars($u['role']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($u['status']) ?>">
                                        <?= htmlspecialchars($u['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?toggle_user=<?= $u['id'] ?>" class="btn btn-warning">
                                        Toggle Status
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- 5. Audit Log Table -->
            <div class="card" id="audit-sec">
                <h2>Recent Admin Audit Logs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>User</th>
                            <th>Action Recorded</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $audit_logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><strong><?= htmlspecialchars($log['user']) ?></strong></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
