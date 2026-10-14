<?php
/**
 * Practical 13: RESTful PHP JSON API for Event Management
 * Features:
 * - Proper HTTP Method Handling (GET, POST, PUT, DELETE, OPTIONS)
 * - Standardized JSON Responses with HTTP Status Codes (Advanced Extension)
 * - Centralized Error & Exception Handling (Advanced Extension)
 * - Server-Side Input Validation before DB operations
 * - Multi-criteria search and category/status filtering
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle CORS Preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

/**
 * Standardized JSON API Response Helper
 */
function sendJsonResponse(bool $success, int $statusCode, string $message, $data = null, $errors = null): void
{
    http_response_code($statusCode);
    echo json_encode([
        "success"   => $success,
        "status"    => $statusCode,
        "message"   => $message,
        "data"      => $data,
        "errors"    => $errors,
        "timestamp" => date("c")
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Centralized Exception Handler
set_exception_handler(function (Throwable $e) {
    sendJsonResponse(false, 500, "Internal Server Error: " . $e->getMessage());
});

// Load Database Connection
require_once __DIR__ . "/db.php";

// Determine HTTP Request Method (supports _method override for form posts)
$method = strtoupper($_SERVER["REQUEST_METHOD"]);
if ($method === "POST" && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
}

// Parse Request Body (JSON or Form Data)
$rawInput = file_get_contents("php://input");
// Strip UTF-8 BOM if present
if ($rawInput !== false) {
    $rawInput = preg_replace('/^\xEF\xBB\xBF/', '', $rawInput);
}
$inputData = [];
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $inputData = $decoded;
    }
}
if (empty($inputData) && !empty($_POST)) {
    $inputData = $_POST;
}

// ========================================================
// 1. GET REQUEST (Read Single or List Records)
// ========================================================
if ($method === "GET") {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

    // Single Record Query
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            sendJsonResponse(true, 200, "Event retrieved successfully.", $event);
        } else {
            sendJsonResponse(false, 404, "Event with ID #{$id} not found.");
        }
    }

    // List Query with Search and Filtering
    $search = trim($_GET["search"] ?? "");
    $category = trim($_GET["category"] ?? "");
    $status = trim($_GET["status"] ?? "");

    $where = ["1=1"];
    $params = [];

    if ($search !== "") {
        $where[] = "(event_name LIKE ? OR venue LIKE ? OR description LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($category !== "") {
        $where[] = "category = ?";
        $params[] = $category;
    }

    if ($status !== "") {
        $where[] = "status = ?";
        $params[] = $status;
    }

    $sql = "SELECT * FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date ASC, event_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary Statistics for Frontend Dashboard
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count
        FROM events
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    sendJsonResponse(true, 200, "Events list retrieved successfully.", [
        "events" => $events,
        "count"  => count($events),
        "stats"  => [
            "total"  => (int)($stats["total"] ?? 0),
            "open"   => (int)($stats["open_count"] ?? 0),
            "closed" => (int)($stats["closed_count"] ?? 0),
        ]
    ]);
}

// ========================================================
// 2. POST REQUEST (Create Record)
// ========================================================
elseif ($method === "POST") {
    $eventName   = trim($inputData["event_name"] ?? "");
    $category    = trim($inputData["category"] ?? "Technical");
    $eventDate   = trim($inputData["event_date"] ?? "");
    $eventTime   = trim($inputData["event_time"] ?? "10:00:00");
    $venue       = trim($inputData["venue"] ?? "");
    $status      = trim($inputData["status"] ?? "open");
    $description = trim($inputData["description"] ?? "");

    // Server-Side Input Validation
    $errors = [];
    if ($eventName === "") {
        $errors["event_name"] = "Event title is required.";
    } elseif (mb_strlen($eventName) < 3) {
        $errors["event_name"] = "Event title must be at least 3 characters.";
    }

    $validCategories = ["Technical", "Workshop", "Cultural", "Sports", "Seminar"];
    if (!in_array($category, $validCategories, true)) {
        $errors["category"] = "Invalid category. Allowed: " . implode(", ", $validCategories);
    }

    if ($eventDate === "") {
        $errors["event_date"] = "Event date is required.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $eventDate)) {
        $errors["event_date"] = "Date must be in YYYY-MM-DD format.";
    }

    if ($venue === "") {
        $errors["venue"] = "Venue location is required.";
    }

    if (!in_array($status, ["open", "closed"], true)) {
        $status = "open";
    }

    // Return 422 Unprocessable Entity if validation fails
    if (!empty($errors)) {
        sendJsonResponse(false, 422, "Validation failed. Please correct input errors.", null, $errors);
    }

    // Insert into Database
    $stmt = $pdo->prepare("
        INSERT INTO events (event_name, description, category, event_date, event_time, venue, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$eventName, $description, $category, $eventDate, $eventTime, $venue, $status]);
    $newId = (int)$pdo->lastInsertId();

    // Fetch newly created record
    $fetchStmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
    $fetchStmt->execute([$newId]);
    $createdEvent = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    // Return 201 Created
    sendJsonResponse(true, 201, "Event '{$eventName}' created successfully!", $createdEvent);
}

// ========================================================
// 3. PUT REQUEST (Update Record)
// ========================================================
elseif ($method === "PUT") {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : (int)($inputData["event_id"] ?? 0);

    if ($id <= 0) {
        sendJsonResponse(false, 400, "Invalid or missing event ID for update.");
    }

    // Check if event exists
    $checkStmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
    $checkStmt->execute([$id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        sendJsonResponse(false, 404, "Event with ID #{$id} not found.");
    }

    $eventName   = trim($inputData["event_name"] ?? $existing["event_name"]);
    $category    = trim($inputData["category"] ?? $existing["category"]);
    $eventDate   = trim($inputData["event_date"] ?? $existing["event_date"]);
    $eventTime   = trim($inputData["event_time"] ?? $existing["event_time"]);
    $venue       = trim($inputData["venue"] ?? $existing["venue"]);
    $status      = trim($inputData["status"] ?? $existing["status"]);
    $description = trim($inputData["description"] ?? $existing["description"]);

    // Input Validation
    $errors = [];
    if ($eventName === "") {
        $errors["event_name"] = "Event title cannot be empty.";
    }
    if ($eventDate === "") {
        $errors["event_date"] = "Event date cannot be empty.";
    }
    if ($venue === "") {
        $errors["venue"] = "Venue cannot be empty.";
    }

    if (!empty($errors)) {
        sendJsonResponse(false, 422, "Validation failed.", null, $errors);
    }

    // Update Record
    $updateStmt = $pdo->prepare("
        UPDATE events
        SET event_name = ?, description = ?, category = ?, event_date = ?, event_time = ?, venue = ?, status = ?
        WHERE event_id = ?
    ");
    $updateStmt->execute([$eventName, $description, $category, $eventDate, $eventTime, $venue, $status, $id]);

    // Fetch updated record
    $fetchStmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
    $fetchStmt->execute([$id]);
    $updatedEvent = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    sendJsonResponse(true, 200, "Event '{$eventName}' updated successfully!", $updatedEvent);
}

// ========================================================
// 4. DELETE REQUEST (Delete Record)
// ========================================================
elseif ($method === "DELETE") {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : (int)($inputData["event_id"] ?? 0);

    if ($id <= 0) {
        sendJsonResponse(false, 400, "Invalid or missing event ID for deletion.");
    }

    // Check if event exists
    $checkStmt = $pdo->prepare("SELECT event_name FROM events WHERE event_id = ? LIMIT 1");
    $checkStmt->execute([$id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        sendJsonResponse(false, 404, "Event with ID #{$id} not found.");
    }

    // Delete Record
    $delStmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $delStmt->execute([$id]);

    sendJsonResponse(true, 200, "Event '{$existing['event_name']}' (ID: #{$id}) deleted successfully!", ["deleted_id" => $id]);
}

// ========================================================
// Method Not Allowed
// ========================================================
else {
    sendJsonResponse(false, 405, "Method '{$method}' not allowed on this endpoint.");
}
