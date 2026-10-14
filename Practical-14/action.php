<?php
/**
 * Practical 14: Action Handler
 * Implements Intermediate Extension: active/inactive user status toggle with audit logging.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

requireAdmin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$action = $_POST["action"] ?? "";

function setFlash(string $type, string $message): void
{
    $_SESSION["flash"] = [
        "type"    => $type,
        "message" => $message
    ];
    header("Location: index.php");
    exit;
}

// ========================================================
// INTERMEDIATE EXTENSION: TOGGLE USER STATUS
// ========================================================
if ($action === "toggle_status") {
    $studentId = (int)($_POST["student_id"] ?? 0);

    if ($studentId <= 0) {
        setFlash("danger", "Invalid student ID provided for status toggle.");
    }

    // Fetch existing student
    $stmt = $pdo->prepare("SELECT name, email, status FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        setFlash("danger", "Student not found in database.");
    }

    $oldStatus = $student["status"];
    $newStatus = ($oldStatus === "active") ? "inactive" : "active";

    // Update in database
    $upd = $pdo->prepare("UPDATE students SET status = ? WHERE student_id = ?");
    $upd->execute([$newStatus, $studentId]);

    // Record Audit Log (Key Question #4)
    $auditMsg = "Admin toggled student #{$studentId} ('{$student['name']}') status from {$oldStatus} to {$newStatus}.";
    recordAuditLog(
        $pdo,
        $_SESSION["user_id"] ?? 1,
        $_SESSION["user_email"] ?? "admin@charusat.edu.in",
        "STATUS_TOGGLE",
        $auditMsg
    );

    setFlash("success", "Success! Student '{$student['name']}' status changed to " . strtoupper($newStatus) . ".");
}

// Clear Audit Logs Action
elseif ($action === "clear_audit_logs") {
    $pdo->exec("TRUNCATE TABLE `audit_logs`");

    recordAuditLog(
        $pdo,
        $_SESSION["user_id"] ?? 1,
        $_SESSION["user_email"] ?? "admin@charusat.edu.in",
        "AUDIT_PURGE",
        "Admin purged old security audit logs."
    );

    setFlash("warning", "Security audit logs have been reset.");
}

else {
    setFlash("danger", "Unknown action requested.");
}
