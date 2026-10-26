<?php
require __DIR__ . '/auth.php';
requireRole('student');
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Student dashboard</title><link rel="stylesheet" href="style.css"></head>
<body><main><h1>Student Dashboard</h1><p>Welcome, <?= h($_SESSION['username']) ?>.</p><form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>"><button>Sign out</button></form></main></body></html>