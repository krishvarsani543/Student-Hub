<?php
/**
 * Practical 10: Session Timeout Simulation Helper
 * Simulates 15 minutes of inactivity so students can capture the timeout screenshot effortlessly.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Rewind last_activity by 1000 seconds past the 900-second threshold
$_SESSION["last_activity"] = time() - 1000;

$redirect = (($_SESSION["role"] ?? "") === "admin") ? "admin_dashboard.php" : "student_dashboard.php";
header("Location: " . $redirect);
exit;
