<?php
session_start();
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['user'])) {
    addAudit($conn, $_SESSION['user'], "Logged out from StudentHub Portal.");
}

session_destroy();
header('Location: login.php');
exit;
?>
