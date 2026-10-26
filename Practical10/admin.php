<?php
require __DIR__ . '/auth.php';
requireRole('admin');
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin dashboard</title><link rel="stylesheet" href="style.css"></head>
<body><main><h1>Admin Dashboard</h1><p>Welcome, <?= h($_SESSION['username']) ?>.</p><p>Last login: <?= h((string)($_SESSION['last_login'] ?? 'See database')) ?></p><form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>"><button>Sign out</button></form></main></body></html>