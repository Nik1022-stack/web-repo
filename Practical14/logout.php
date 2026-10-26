<?php
session_start();
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['user'])) {
    addAudit($conn, $_SESSION['user'], "User logged out.");
}

session_destroy();
header('Location: login.php');
exit;
?>
