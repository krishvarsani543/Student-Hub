<?php
/**
 * Practical 11: Get Single Student Endpoint (JSON)
 * Used by View and Edit modals to fetch student record using MySQLi prepared statements.
 */

require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid student ID."]);
    exit;
}

$stmt = $conn->prepare("SELECT student_id, name, email, mobile, course, status, role, last_login, created_at FROM students WHERE student_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["status" => "success", "data" => $row]);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Student not found."]);
}

$stmt->close();
$conn->close();
