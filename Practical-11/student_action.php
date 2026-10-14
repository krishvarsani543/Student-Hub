<?php
/**
 * Practical 11: Student CRUD Action Handler
 * Handles INSERT, UPDATE, SOFT DELETE, RESTORE, and PERMANENT DELETE using MySQLi prepared statements.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$action = $_POST["action"] ?? "";

// Helper to redirect with flash message
function redirectWithMessage(string $type, string $message): void
{
    $_SESSION["flash"] = [
        "type"    => $type, // success, danger, warning, info
        "message" => $message
    ];
    header("Location: index.php");
    exit;
}

// ==========================================
// 1. CREATE (INSERT) STUDENT
// ==========================================
if ($action === "add") {
    $name   = trim($_POST["name"] ?? "");
    $email  = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $status = trim($_POST["status"] ?? "active");

    // Validation
    if ($name === "" || $email === "" || $course === "") {
        redirectWithMessage("danger", "Failed to add student: Name, Email, and Course are required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage("danger", "Failed to add student: Invalid email address format.");
    }

    if ($mobile !== "" && !preg_match("/^[0-9]{10}$/", $mobile)) {
        redirectWithMessage("danger", "Failed to add student: Mobile number must be 10 digits.");
    }

    // Check duplicate email using prepared statement
    $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        redirectWithMessage("danger", "Failed to add student: A student with email '{$email}' already exists.");
    }
    $checkStmt->close();

    // Insert new student using MySQLi prepared statement
    $defaultPassHash = password_hash("Student@123", PASSWORD_DEFAULT);
    $insertStmt = $conn->prepare("
        INSERT INTO students (name, email, mobile, course, status, role, password)
        VALUES (?, ?, ?, ?, ?, 'student', ?)
    ");
    $insertStmt->bind_param("ssssss", $name, $email, $mobile, $course, $status, $defaultPassHash);

    if ($insertStmt->execute()) {
        $newId = $insertStmt->insert_id;
        $insertStmt->close();
        redirectWithMessage("success", "Success! Student '{$name}' (ID: #{$newId}) was added successfully.");
    } else {
        $error = $insertStmt->error;
        $insertStmt->close();
        redirectWithMessage("danger", "Database error while adding student: " . $error);
    }
}

// ==========================================
// 2. UPDATE (EDIT) STUDENT
// ==========================================
elseif ($action === "edit") {
    $studentId = (int)($_POST["student_id"] ?? 0);
    $name      = trim($_POST["name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $mobile    = trim($_POST["mobile"] ?? "");
    $course    = trim($_POST["course"] ?? "");
    $status    = trim($_POST["status"] ?? "active");

    if ($studentId <= 0 || $name === "" || $email === "" || $course === "") {
        redirectWithMessage("danger", "Failed to update: Student ID, Name, Email, and Course are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage("danger", "Failed to update: Invalid email format.");
    }

    // Verify email uniqueness across other students
    $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ? LIMIT 1");
    $checkStmt->bind_param("si", $email, $studentId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        redirectWithMessage("danger", "Failed to update: Email '{$email}' is already taken by another student.");
    }
    $checkStmt->close();

    // Execute UPDATE statement
    $updateStmt = $conn->prepare("
        UPDATE students 
        SET name = ?, email = ?, mobile = ?, course = ?, status = ?
        WHERE student_id = ?
    ");
    $updateStmt->bind_param("sssssi", $name, $email, $mobile, $course, $status, $studentId);

    if ($updateStmt->execute()) {
        $updateStmt->close();
        redirectWithMessage("success", "Success! Student record for '{$name}' (ID: #{$studentId}) was updated successfully.");
    } else {
        $error = $updateStmt->error;
        $updateStmt->close();
        redirectWithMessage("danger", "Database error while updating student: " . $error);
    }
}

// ==========================================
// 3. ADVANCED EXTENSION: SOFT DELETE
// ==========================================
elseif ($action === "soft_delete") {
    $studentId = (int)($_POST["student_id"] ?? 0);

    if ($studentId <= 0) {
        redirectWithMessage("danger", "Invalid student ID for soft delete.");
    }

    // Update status to 'deleted'
    $softStmt = $conn->prepare("UPDATE students SET status = 'deleted' WHERE student_id = ?");
    $softStmt->bind_param("i", $studentId);

    if ($softStmt->execute()) {
        $softStmt->close();
        redirectWithMessage("warning", "Soft Delete Executed: Student ID #{$studentId} status set to 'deleted' (Record retained in database).");
    } else {
        $error = $softStmt->error;
        $softStmt->close();
        redirectWithMessage("danger", "Error executing soft delete: " . $error);
    }
}

// ==========================================
// 4. RESTORE SOFT-DELETED STUDENT
// ==========================================
elseif ($action === "restore") {
    $studentId = (int)($_POST["student_id"] ?? 0);

    if ($studentId <= 0) {
        redirectWithMessage("danger", "Invalid student ID for restore.");
    }

    $restoreStmt = $conn->prepare("UPDATE students SET status = 'active' WHERE student_id = ?");
    $restoreStmt->bind_param("i", $studentId);

    if ($restoreStmt->execute()) {
        $restoreStmt->close();
        redirectWithMessage("success", "Success! Student ID #{$studentId} was restored back to 'active' status.");
    } else {
        $error = $restoreStmt->error;
        $restoreStmt->close();
        redirectWithMessage("danger", "Error restoring student: " . $error);
    }
}

// ==========================================
// 5. PERMANENT DELETE (CONFIRMED)
// ==========================================
elseif ($action === "permanent_delete") {
    $studentId = (int)($_POST["student_id"] ?? 0);

    if ($studentId <= 0) {
        redirectWithMessage("danger", "Invalid student ID for permanent delete.");
    }

    $delStmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $delStmt->bind_param("i", $studentId);

    if ($delStmt->execute()) {
        $delStmt->close();
        redirectWithMessage("danger", "Permanent Delete Executed: Student ID #{$studentId} was permanently deleted from the database.");
    } else {
        $error = $delStmt->error;
        $delStmt->close();
        redirectWithMessage("danger", "Error executing permanent delete: " . $error);
    }
}

else {
    redirectWithMessage("danger", "Unknown action requested.");
}
