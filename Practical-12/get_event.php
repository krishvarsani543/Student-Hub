<?php
/**
 * Practical 12: Get Event Details Endpoint (JSON)
 */

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid event ID."]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($event) {
    echo json_encode(["status" => "success", "data" => $event]);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Event not found."]);
}
