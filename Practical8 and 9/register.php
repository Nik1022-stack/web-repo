<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';
$fullName = '';
$username = '';
$email = '';

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function hasLength(string $value, int $minimum, int $maximum): bool
{
    $length = preg_match_all('/./us', $value);
    return $length !== false && $length >= $minimum && $length <= $maximum;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = strtolower(trim((string)($_POST['username'] ?? '')));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $message = 'Your session token is invalid. Refresh the page and try again.';
        $messageType = 'error';
    } elseif (!hasLength($fullName, 1, 120)) {
        $message = 'Enter a name with no more than 120 characters.';
        $messageType = 'error';
    } elseif (!preg_match('/\A[a-z0-9_]{3,30}\z/', $username)) {
        $message = 'Username must be 3-30 characters and use only letters, numbers, or underscores.';
        $messageType = 'error';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter a valid email address.';
        $messageType = 'error';
    } elseif (!hasLength($password, 8, 128)) {
        $message = 'Password must be between 8 and 128 characters.';
        $messageType = 'error';
    } elseif (!hash_equals($password, $confirmPassword)) {
        $message = 'The password confirmation does not match.';
        $messageType = 'error';
    } else {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $mysqli = new mysqli('127.0.0.1', 'root', '', 'studenthub');
            $mysqli->set_charset('utf8mb4');

            $check = $mysqli->prepare(
                'SELECT user_id FROM app_users WHERE username = ? OR email = ? LIMIT 1'
            );
            $check->bind_param('ss', $username, $email);
            $check->execute();
            $duplicate = $check->get_result()->num_rows > 0;
            $check->close();

            if ($duplicate) {
                $message = 'That email address or username is already registered.';
                $messageType = 'error';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $mysqli->begin_transaction();
                try {
                    $insert = $mysqli->prepare(
                        'INSERT INTO app_users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)'
                    );
                    $insert->bind_param('ssss', $fullName, $username, $email, $passwordHash);
                    $insert->execute();
                    $userId = $mysqli->insert_id;
                    $insert->close();

                    $audit = $mysqli->prepare(
                        "INSERT INTO registration_audit_log (user_id, action) VALUES (?, 'user_registered')"
                    );
                    $audit->bind_param('i', $userId);
                    $audit->execute();
                    $audit->close();
                    $mysqli->commit();

                    $message = 'Your account has been created successfully.';
                    $messageType = 'success';
                    $fullName = '';
                    $username = '';
                    $email = '';
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } catch (mysqli_sql_exception $e) {
                    $mysqli->rollback();
                    if ((int)$e->getCode() === 1062) {
                        $message = 'That email address or username is already registered.';
                        $messageType = 'error';
                    } else {
                        throw $e;
                    }
                }
            }

            $mysqli->close();
        } catch (mysqli_sql_exception $e) {
            $message = 'Registration is temporarily unavailable. Check the database setup and try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Create account</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172b36;
            --muted: #52656e;
            --line: #c8d4d5;
            --paper: #fffefa;
            --canvas: #e7f0ed;
            --accent: #14685e;
            --accent-dark: #0c5048;
            --error: #9d312d;
            --success: #176347;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 32px 18px;
            display: grid;
            place-items: center;
            color: var(--ink);
            background-color: var(--canvas);
            background-image: radial-gradient(#9ab8b0 0.7px, transparent 0.7px);
            background-size: 18px 18px;
            font-family: Georgia, 'Times New Roman', serif;
        }
        main {
            width: min(100%, 510px);
            padding: clamp(24px, 6vw, 46px);
            background: var(--paper);
            border: 1px solid var(--line);
            border-top: 5px solid var(--accent);
            box-shadow: 12px 12px 0 rgba(23, 43, 54, 0.08);
            animation: arrive 450ms ease-out both;
        }
        .eyebrow {
            margin: 0 0 10px;
            color: var(--accent);
            font: 700 0.76rem/1.4 Arial, sans-serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        h1 { margin: 0; font-size: clamp(2rem, 8vw, 2.65rem); line-height: 1.05; }
        .intro { margin: 12px 0 26px; color: var(--muted); font: 1rem/1.5 Arial, sans-serif; }
        form { display: grid; gap: 16px; }
        label { display: grid; gap: 7px; font: 700 0.9rem/1.3 Arial, sans-serif; }
        input {
            width: 100%;
            min-height: 46px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 2px;
            background: #fff;
            color: var(--ink);
            font: 1rem Arial, sans-serif;
        }
        input:focus { outline: 3px solid rgba(20, 104, 94, 0.2); border-color: var(--accent); }
        button {
            min-height: 48px;
            margin-top: 4px;
            border: 0;
            border-radius: 2px;
            background: var(--accent);
            color: #fff;
            cursor: pointer;
            font: 700 1rem Arial, sans-serif;
        }
        button:hover { background: var(--accent-dark); }
        .message { margin: 0 0 18px; padding: 12px; font: 0.95rem/1.45 Arial, sans-serif; }
        .message.error { color: var(--error); background: #fff0ed; }
        .message.success { color: var(--success); background: #e8f5eb; }
        .back { display: inline-block; margin-top: 20px; color: var(--accent-dark); font: 0.9rem Arial, sans-serif; }
        @keyframes arrive { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; } }
    </style>
</head>
<body>
    <main>
        <p class="eyebrow">StudentHub / Account access</p>
        <h1>Create your account</h1>
        <p class="intro">Register with a unique username and email address.</p>

        <?php if ($message !== ''): ?>
            <p class="message <?= escapeHtml($messageType) ?>" role="status"><?= escapeHtml($message) ?></p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">
            <label for="full_name">Full name
                <input id="full_name" name="full_name" type="text" maxlength="120" autocomplete="name" required value="<?= escapeHtml($fullName) ?>">
            </label>
            <label for="username">Username
                <input id="username" name="username" type="text" minlength="3" maxlength="30" pattern="[A-Za-z0-9_]{3,30}" autocomplete="username" required value="<?= escapeHtml($username) ?>">
            </label>
            <label for="email">Email address
                <input id="email" name="email" type="email" maxlength="254" autocomplete="email" required value="<?= escapeHtml($email) ?>">
            </label>
            <label for="password">Password
                <input id="password" name="password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
            </label>
            <label for="confirm_password">Confirm password
                <input id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required>
            </label>
            <button type="submit">Create account</button>
        </form>
        <a class="back" href="index.php">Back to StudentHub database demo</a>
    </main>
    <script>
        const passwordField = document.getElementById('password');
        const confirmationField = document.getElementById('confirm_password');
        const validateConfirmation = () => {
            confirmationField.setCustomValidity(
                confirmationField.value === passwordField.value ? '' : 'Passwords do not match.'
            );
        };
        passwordField.addEventListener('input', validateConfirmation);
        confirmationField.addEventListener('input', validateConfirmation);
    </script>
</body>
</html>