<?php
// Database connection using MySQLi
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "practical12";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Helper function to log event actions (Advanced Extension: Audit Log)
function logAuditEvent($conn, $event_id, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO event_audit_log (event_id, action, details) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $event_id, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}
?>
