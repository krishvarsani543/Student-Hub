<?php
/**
 * Practical 10: Secure Logout Handler
 * Completely destroys session, clears cookies, and revokes remember-me tokens.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/remember.php";

// 1. Clear database remember token and cookie
clearRememberToken($pdo);

// 2. Unset all session variables
$_SESSION = [];

// 3. Invalidate session cookie in user's browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
    setcookie(session_name(), "", [
        "expires"  => time() - 3600,
        "path"     => $params["path"],
        "domain"   => $params["domain"],
        "secure"   => $isSecure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

// 4. Destroy the server-side session
session_destroy();

// 5. Redirect back to login with success message
header("Location: login.php?msg=logout");
exit;
