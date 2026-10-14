
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$timeout = 900;

if (isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"] > $timeout)) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}