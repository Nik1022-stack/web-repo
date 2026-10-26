<?php
// Database connection using MySQLi
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "practical11";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
