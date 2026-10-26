<?php
require __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validCsrf()) {
    http_response_code(400);
    exit('Invalid request.');
}
logoutUser($db);
header('Location: login.php?logout=1');
exit;