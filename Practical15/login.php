<?php
session_start();
require_once __DIR__ . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && $password === $user['password']) {
        if ($user['status'] !== 'Active') {
            $error = "Account is inactive. Contact the administrator.";
        } else {
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            addAudit($conn, $user['username'], "Logged in to StudentHub Portal ({$user['role']}).");
            header('Location: index.php');
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal: Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-box">
        <h2>StudentHub Login</h2>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Username</label>
                <input type="text" name="username" required value="admin">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Password</label>
                <input type="password" name="password" required value="admin123">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
        </form>
        <div style="margin-top: 15px; font-size: 12px; color: #6b7280; text-align: center;">
            Demo: <strong>admin</strong> / <code>admin123</code> &bull; <strong>student_aarav</strong> / <code>student123</code>
        </div>
    </div>
</body>
</html>
