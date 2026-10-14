
<?php

session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

if (!isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid security token."]);
    exit;
}

$email = trim($_POST["email"] ?? "");

if (!preg_match('/^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/', $email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter a valid CHARUSAT email."]);
    exit;
}

try {
    require_once "db.php";

    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);

    if ($stmt->fetch()) {
        echo json_encode(["status" => "duplicate", "message" => "This email is already registered. Please use another email."]);
    } else {
        echo json_encode(["status" => "available", "message" => "Email is available."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}