<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$student_count = $conn->query("SELECT COUNT(*) as c FROM students WHERE status = 'Active'")->fetch_assoc()['c'] ?? 0;
$event_count = $conn->query("SELECT COUNT(*) as c FROM events WHERE status = 'Open'")->fetch_assoc()['c'] ?? 0;

$events = [];
$res = $conn->query("SELECT id, title, category, event_date, venue, status FROM events ORDER BY event_date ASC");
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode([
    'success' => true,
    'portal' => 'StudentHub Unified API',
    'stats' => [
        'active_students' => (int)$student_count,
        'open_events'     => (int)$event_count
    ],
    'events' => $events
]);
exit;
?>
