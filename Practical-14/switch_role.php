<?php
/**
 * Practical 14: Role Switcher Tool for Viva & Lab Testing
 * Allows instant toggling between Admin and Student role sessions to demonstrate RBAC & Dynamic Menus.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$targetRole = $_GET["role"] ?? "admin";

if ($targetRole === "student") {
    $_SESSION["user_id"]    = 8;
    $_SESSION["role"]       = "student";
    $_SESSION["user_name"]  = "Krish Varsani (Student)";
    $_SESSION["user_email"] = "25dcs140@charusat.edu.in";
    header("Location: student_view.php");
    exit;
} else {
    $_SESSION["user_id"]    = 1;
    $_SESSION["role"]       = "admin";
    $_SESSION["user_name"]  = "Administrator";
    $_SESSION["user_email"] = "admin@charusat.edu.in";
    header("Location: index.php");
    exit;
}
