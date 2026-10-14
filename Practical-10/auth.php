<?php
/**
 * Practical 10: Authentication, Session Management, RBAC & Timeout Middleware
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Enforce secure session cookie settings
    if (ini_get("session.use_cookies")) {
        $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
        session_set_cookie_params([
            "lifetime" => 0,
            "path"     => "/",
            "domain"   => "",
            "secure"   => $isSecure,
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }
    session_start();
}

// Session Timeout duration: 900 seconds (15 minutes)
// Can be customized via GET parameter ?test_timeout=1 for demonstration
$SESSION_TIMEOUT = 900;

// Check for session timeout
if (isset($_SESSION["last_activity"])) {
    $timeSinceLastActivity = time() - $_SESSION["last_activity"];
    if ($timeSinceLastActivity > $SESSION_TIMEOUT) {
        // Destroy session securely on timeout
        require_once __DIR__ . "/db.php";
        require_once __DIR__ . "/remember.php";
        clearRememberToken($pdo);

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires"  => time() - 3600,
                "path"     => $params["path"],
                "domain"   => $params["domain"],
                "secure"   => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => "Lax"
            ]);
        }
        session_destroy();

        header("Location: login.php?msg=timeout");
        exit;
    }
}

// Refresh last activity timestamp
$_SESSION["last_activity"] = time();

// Block unauthorized access if not authenticated
if (!isset($_SESSION["student_id"])) {
    header("Location: login.php?msg=unauthorized");
    exit;
}

/**
 * Role-Based Access Control (RBAC) guard
 *
 * @param string ...$allowedRoles List of roles permitted to access the page
 */
function requireRole(string ...$allowedRoles): void
{
    $currentRole = $_SESSION["role"] ?? "";

    if (!in_array($currentRole, $allowedRoles, true)) {
        // Set HTTP 403 Forbidden header
        http_response_code(403);

        // If a student tries to access admin area, redirect with clear message
        if ($currentRole === "student") {
            header("Location: student_dashboard.php?msg=access_denied");
            exit;
        }

        // Generic access denial for any other mismatched roles
        header("Location: login.php?msg=access_denied");
        exit;
    }
}
