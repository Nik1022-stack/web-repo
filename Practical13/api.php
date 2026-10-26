<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

// ========================================================
// ADVANCED EXTENSION: Centralized Error Handling & Status Codes
// ========================================================
function sendResponse(bool $success, string $message, $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

// Support both form-data and JSON request body
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true);
$requestData = is_array($jsonBody) ? array_merge($_POST, $jsonBody) : $_POST;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $requestData['action'] ?? '';

// ========================================================
// 1. GET: Fetch List, Search, or Single Event
// ========================================================
if ($method === 'GET') {
    // Fetch single event
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($event) {
            sendResponse(true, "Event fetched successfully.", $event, 200);
        } else {
            sendResponse(false, "Event not found.", null, 404);
        }
    }

    // Search or List All (with Intermediate Extension: Search)
    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');

    $sql = "SELECT * FROM events WHERE 1=1";
    $types = "";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (title LIKE ? OR venue LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $types .= "sss";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($category !== '') {
        $sql .= " AND category = ?";
        $types .= "s";
        $params[] = $category;
    }

    $sql .= " ORDER BY event_date ASC, id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();

    sendResponse(true, "Events fetched successfully.", $events, 200);
}

// ========================================================
// 2. POST: Add New Event (CREATE)
// ========================================================
if ($method === 'POST' && ($action === 'create' || empty($action))) {
    $title       = trim($requestData['title'] ?? '');
    $category    = trim($requestData['category'] ?? '');
    $event_date  = trim($requestData['event_date'] ?? '');
    $venue       = trim($requestData['venue'] ?? '');
    $status      = trim($requestData['status'] ?? 'Open');
    $description = trim($requestData['description'] ?? '');

    // Server-side input validation
    if ($title === '' || $category === '' || $event_date === '' || $venue === '') {
        sendResponse(false, "Validation Error: Title, Category, Date, and Venue are required.", null, 400);
    }

    $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, venue, status, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $category, $event_date, $venue, $status, $description);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        sendResponse(true, "Event '{$title}' created successfully!", ['id' => $newId], 201);
    } else {
        $error = $conn->error;
        $stmt->close();
        sendResponse(false, "Database insert failed: " . $error, null, 500);
    }
}

// ========================================================
// 3. POST / PUT: Update Event (UPDATE)
// ========================================================
if ($method === 'POST' && $action === 'update') {
    $id          = (int)($requestData['id'] ?? 0);
    $title       = trim($requestData['title'] ?? '');
    $category    = trim($requestData['category'] ?? '');
    $event_date  = trim($requestData['event_date'] ?? '');
    $venue       = trim($requestData['venue'] ?? '');
    $status      = trim($requestData['status'] ?? 'Open');
    $description = trim($requestData['description'] ?? '');

    if ($id <= 0) {
        sendResponse(false, "Invalid or missing Event ID for update.", null, 400);
    }

    if ($title === '' || $category === '' || $event_date === '' || $venue === '') {
        sendResponse(false, "Validation Error: All required fields must be filled.", null, 400);
    }

    $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, status = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $title, $category, $event_date, $venue, $status, $description, $id);

    if ($stmt->execute()) {
        $stmt->close();
        sendResponse(true, "Event '{$title}' updated successfully!", ['id' => $id], 200);
    } else {
        $error = $conn->error;
        $stmt->close();
        sendResponse(false, "Database update failed: " . $error, null, 500);
    }
}

// ========================================================
// 4. POST / DELETE: Delete Event (DELETE)
// ========================================================
if ($action === 'delete' || $method === 'DELETE') {
    $id = (int)($requestData['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        sendResponse(false, "Invalid or missing Event ID for deletion.", null, 400);
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        sendResponse(true, "Event #{$id} deleted successfully!", null, 200);
    } else {
        $error = $conn->error;
        $stmt->close();
        sendResponse(false, "Database deletion failed: " . $error, null, 500);
    }
}

sendResponse(false, "Invalid request method or action.", null, 405);
?>
