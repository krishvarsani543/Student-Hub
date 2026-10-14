<?php
/**
 * StudentHub Portal Root Dispatcher
 * Intelligently routes users based on active session role or direct query parameters.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If explicitly requesting the Practical-15 integrated hub
if (isset($_GET['hub']) || isset($_GET['portal']) || isset($_GET['p15'])) {
    header("Location: Practical-15/index.php");
    exit;
}

// Role-based automatic redirection if authenticated
if (isset($_SESSION["student_id"])) {
    if (($_SESSION["role"] ?? "") === "admin") {
        header("Location: Practical-14/index.php");
        exit;
    } else {
        header("Location: Practical-2/deshboard.php");
        exit;
    }
}

// Default entry point for public visitors
header("Location: Practical-2/home.html");
exit;
