<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "practical13";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
