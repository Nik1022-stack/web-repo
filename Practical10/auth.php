<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
ini_set('session.use_strict_mode', '1');
session_name('PRACTICAL10');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function validCsrf(): bool
{
    return hash_equals(csrfToken(), (string)($_POST['csrf'] ?? ''));
}

function cookieOptions(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ];
}

function issueRememberToken(mysqli $db, int $userId): void
{
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $expires = time() + 30 * 24 * 60 * 60;
    $date = date('Y-m-d H:i:s', $expires);

    $stmt = $db->prepare('UPDATE users SET remember_selector = ?, remember_hash = ?, remember_expires = ? WHERE id = ?');
    $stmt->bind_param('sssi', $selector, $hash, $date, $userId);
    $stmt->execute();
    setcookie('remember_login', $selector . '.' . $validator, cookieOptions($expires));
}

function forgetRemember(mysqli $db, ?int $userId = null): void
{
    if ($userId !== null) {
        $stmt = $db->prepare('UPDATE users SET remember_selector = NULL, remember_hash = NULL, remember_expires = NULL WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    setcookie('remember_login', '', cookieOptions(time() - 3600));
}

function logoutUser(mysqli $db): void
{
    forgetRemember($db, isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null);
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
    ]);
    session_destroy();
}

function restoreRemember(mysqli $db): bool
{
    $parts = explode('.', (string)($_COOKIE['remember_login'] ?? ''), 2);
    if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
        return false;
    }

    $stmt = $db->prepare('SELECT id, username, role, remember_hash FROM users WHERE remember_selector = ? AND remember_expires > NOW()');
    $stmt->bind_param('s', $parts[0]);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user || !hash_equals($user['remember_hash'], hash('sha256', $parts[1]))) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    $update = $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $update->bind_param('i', $user['id']);
    $update->execute();
    $_SESSION['last_login'] = date('Y-m-d H:i:s');
    issueRememberToken($db, (int)$user['id']);
    return true;
}

function redirectByRole(): void
{
    header('Location: ' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin.php' : 'student.php'));
    exit;
}

function requireRole(string $role): void
{
    global $db;

    if (!isset($_SESSION['uid']) && !restoreRemember($db)) {
        header('Location: login.php');
        exit;
    }

    if (time() - (int)($_SESSION['last_activity'] ?? 0) > 900) {
        logoutUser($db);
        header('Location: login.php?timeout=1');
        exit;
    }

    if ($_SESSION['role'] !== $role) {
        redirectByRole();
    }

    $_SESSION['last_activity'] = time();
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}