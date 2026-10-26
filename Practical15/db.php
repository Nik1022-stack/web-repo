<?php
$conn = new mysqli("127.0.0.1", "root", "", "studenthub_portal");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Helper to log administrative actions
function addAudit($conn, $user, $action) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user, action) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $action);
    $stmt->execute();
    $stmt->close();
}
?>
