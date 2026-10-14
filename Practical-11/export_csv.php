<?php
/**
 * Practical 11: Export Students to CSV (Intermediate Extension)
 * Generates and downloads a clean CSV file of students filtered by search/course/status criteria.
 */

require_once __DIR__ . "/db.php";

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");
$status = trim($_GET["status"] ?? "");

// Build SQL query dynamically with MySQLi prepared statement
$query = "SELECT student_id, name, email, mobile, course, status, role, last_login, created_at FROM students WHERE 1=1";
$params = [];
$types  = "";

if ($search !== "") {
    $query .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($course !== "") {
    $query .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

if ($status !== "") {
    $query .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Send CSV Download Headers
$filename = "students_export_" . date("Ymd_His") . ".csv";
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

// Output UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// CSV Column Headers
fputcsv($output, ["Student ID", "Full Name", "University Email", "Mobile Number", "Enrolled Course", "Status", "Role", "Last Login", "Registered On"]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row["student_id"],
        $row["name"],
        $row["email"],
        $row["mobile"] ?? "N/A",
        $row["course"] ?? "N/A",
        ucfirst($row["status"] ?? "active"),
        ucfirst($row["role"] ?? "student"),
        $row["last_login"] ?? "Never",
        $row["created_at"] ?? "N/A"
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
exit;
