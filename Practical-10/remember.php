<?php
/**
 * Practical 10: Secure Remember-Me Token Handler
 * Implements random selector + validator token pattern (split token approach).
 * Token is never stored in plain text: only its SHA-256 hash is kept in the database.
 */

function createRememberToken(PDO $pdo, int $studentId): void
{
    // Generate 12-byte selector and 32-byte cryptographic random token
    $selector = bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expiresAt = date("Y-m-d H:i:s", time() + (30 * 24 * 60 * 60)); // 30 days

    // Delete any previous remember token for this student
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE student_id = ?");
    $stmt->execute([$studentId]);

    // Insert new selector and hashed token
    $stmt = $pdo->prepare(
        "INSERT INTO remember_tokens (student_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$studentId, $selector, $tokenHash, $expiresAt]);

    // Issue HttpOnly, SameSite cookie
    $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
    setcookie("remember_me", $selector . ":" . $token, [
        "expires"  => time() + (30 * 24 * 60 * 60),
        "path"     => "/",
        "secure"   => $isSecure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function restoreRememberedSession(PDO $pdo): bool
{
    if (empty($_COOKIE["remember_me"])) {
        return false;
    }

    $cookieVal = $_COOKIE["remember_me"];
    $parts = explode(":", $cookieVal, 2);

    if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
        clearRememberToken($pdo);
        return false;
    }

    [$selector, $token] = $parts;

    // Retrieve active token by selector
    $stmt = $pdo->prepare(
        "SELECT student_id, token_hash FROM remember_tokens
         WHERE selector = ? AND expires_at > NOW() LIMIT 1"
    );
    $stmt->execute([$selector]);
    $remembered = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$remembered) {
        clearRememberToken($pdo);
        return false;
    }

    // Timing-attack safe verification of token hash
    if (!hash_equals($remembered["token_hash"], hash("sha256", $token))) {
        clearRememberToken($pdo);
        return false;
    }

    // Retrieve student profile
    $stmt = $pdo->prepare(
        "SELECT student_id, name, email, role, last_login FROM students WHERE student_id = ? LIMIT 1"
    );
    $stmt->execute([$remembered["student_id"]]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        clearRememberToken($pdo);
        return false;
    }

    // Session fixation mitigation: regenerate session ID
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true);

    $_SESSION["student_id"]    = $student["student_id"];
    $_SESSION["name"]          = $student["name"];
    $_SESSION["email"]         = $student["email"];
    $_SESSION["role"]          = $student["role"];
    $_SESSION["last_login"]    = $student["last_login"];
    $_SESSION["last_activity"] = time();

    // Re-issue a fresh token for rotation security
    createRememberToken($pdo, (int)$student["student_id"]);

    return true;
}

function clearRememberToken(PDO $pdo): void
{
    if (!empty($_COOKIE["remember_me"])) {
        $parts = explode(":", $_COOKIE["remember_me"], 2);
        if (count($parts) === 2 && ctype_xdigit($parts[0])) {
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $stmt->execute([$parts[0]]);
        }
    }

    $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
    setcookie("remember_me", "", [
        "expires"  => time() - 3600,
        "path"     => "/",
        "secure"   => $isSecure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}
