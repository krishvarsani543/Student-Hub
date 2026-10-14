
<?php

function createRememberToken(PDO $pdo, int $studentId): void
{
    $selector = bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expiresAt = date("Y-m-d H:i:s", time() + (30 * 24 * 60 * 60));

    $stmt = $pdo->prepare(
        "DELETE FROM remember_tokens WHERE student_id = ?"
    );
    $stmt->execute([$studentId]);

    $stmt = $pdo->prepare(
        "INSERT INTO remember_tokens (student_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$studentId, $selector, $tokenHash, $expiresAt]);

    setcookie("remember_me", $selector . ":" . $token, [
        "expires" => time() + (30 * 24 * 60 * 60),
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function restoreRememberedSession(PDO $pdo): bool
{
    if (empty($_COOKIE["remember_me"])) {
        return false;
    }

    $parts = explode(":", $_COOKIE["remember_me"], 2);

    if (
        count($parts) !== 2 ||
        !ctype_xdigit($parts[0]) ||
        !ctype_xdigit($parts[1])
    ) {
        clearRememberToken($pdo);
        return false;
    }

    [$selector, $token] = $parts;

    $stmt = $pdo->prepare(
        "SELECT student_id, token_hash FROM remember_tokens
         WHERE selector = ? AND expires_at > NOW() LIMIT 1"
    );
    $stmt->execute([$selector]);
    $remembered = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$remembered ||
        !hash_equals($remembered["token_hash"], hash("sha256", $token))
    ) {
        clearRememberToken($pdo);
        return false;
    }

    $stmt = $pdo->prepare(
        "SELECT student_id, name, email, role, course FROM students WHERE student_id = ? LIMIT 1"
    );
    $stmt->execute([$remembered["student_id"]]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        clearRememberToken($pdo);
        return false;
    }

    session_regenerate_id(true);

    $_SESSION["student_id"] = $student["student_id"];
    $_SESSION["name"] = $student["name"];
    $_SESSION["email"] = $student["email"];
    $_SESSION["role"] = $student["role"];
    $_SESSION["course"] = $student["course"] ?? "B.Tech Student";

    return true;
}

function clearRememberToken(PDO $pdo): void
{
    if (!empty($_COOKIE["remember_me"])) {
        $parts = explode(":", $_COOKIE["remember_me"], 2);

        if (count($parts) === 2 && ctype_xdigit($parts[0])) {
            $stmt = $pdo->prepare(
                "DELETE FROM remember_tokens WHERE selector = ?"
            );
            $stmt->execute([$parts[0]]);
        }
    }

    setcookie("remember_me", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}