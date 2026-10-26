<?php
require __DIR__ . '/auth.php';

if (isset($_SESSION['uid'])) {
    redirectByRole();
}

$message = isset($_GET['timeout']) ? 'Session expired. Please sign in again.' : '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!validCsrf()) {
        $message = 'Invalid request. Refresh and try again.';
    } else {
        $stmt = $db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $message = 'Invalid username or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf'] = bin2hex(random_bytes(32));

            $update = $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $update->bind_param('i', $_SESSION['uid']);
            $update->execute();
            $_SESSION['last_login'] = date('Y-m-d H:i:s');
            if (isset($_POST['remember'])) {
                issueRememberToken($db, $_SESSION['uid']);
            } else {
                forgetRemember($db, $_SESSION['uid']);
            }
            redirectByRole();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 10 | Sign in</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <h1>Sign in</h1>
    <?php if ($message !== ''): ?><p class="message" id="login-message"><?= h($message) ?></p><?php endif; ?>
    <?php if (isset($_GET['logout'])): ?><p class="message">You have signed out.</p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
        <label>Username<input name="username" value="<?= h($username) ?>" required autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button>Sign in</button>
    </form>
</main>
<script>
document.querySelectorAll('input[name="username"], input[name="password"]').forEach((input) => {
    input.addEventListener('input', () => document.getElementById('login-message')?.remove());
});
</script>
</body>
</html>