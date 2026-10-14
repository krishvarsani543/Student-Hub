<?php
/**
 * Practical 14: Authentication & Role-Based Access Control Guard
 * Manages sessions, RBAC restrictions, and dynamic role contexts.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

// Default demo session if not set (ensures initial admin access for testing)
if (!isset($_SESSION["role"])) {
    // Look up default admin user
    $adminStmt = $pdo->query("SELECT student_id, name, email, role FROM students WHERE role = 'admin' LIMIT 1");
    $adminUser = $adminStmt->fetch();

    if ($adminUser) {
        $_SESSION["user_id"]    = (int)$adminUser["student_id"];
        $_SESSION["user_name"]  = $adminUser["name"];
        $_SESSION["user_email"] = $adminUser["email"];
        $_SESSION["role"]       = "admin";
    } else {
        $_SESSION["user_id"]    = 1;
        $_SESSION["user_name"]  = "Administrator";
        $_SESSION["user_email"] = "admin@charusat.edu.in";
        $_SESSION["role"]       = "admin";
    }
}

/**
 * Enforce Admin Role (Key Question #2)
 */
function requireAdmin(): void
{
    $currentRole = $_SESSION["role"] ?? "guest";

    if ($currentRole !== "admin") {
        http_response_code(403);
        // Include styled 403 Forbidden page directly
        include __DIR__ . "/access_denied.php";
        exit;
    }
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool
{
    return ($_SESSION["role"] ?? "") === "admin";
}
