<?php
/**
 * Practical 15: Unified Database Connection & Environment Bootstrapper
 * Supports both PDO and MySQLi with auto-detection for XAMPP MySQL ports 3307 & 3306.
 */

$dbHost = "127.0.0.1";
$dbName = "studenthub";
$dbUser = "root";
$dbPass = "";

$ports = [3307, 3306];
$pdo = null;
$mysqli = null;
$activePort = 3306;
$dbError = null;

foreach ($ports as $port) {
    try {
        $testPdo = new PDO(
            "mysql:host=$dbHost;port=$port;charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2
            ]
        );
        $pdo = $testPdo;
        $activePort = $port;
        break;
    } catch (PDOException $e) {
        $dbError = $e;
    }
}

if (!$pdo) {
    die("Database Connection Error: Unable to connect to MySQL on ports 3307 or 3306. " . ($dbError ? $dbError->getMessage() : ""));
}

// Ensure database exists and select it
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$dbName`");

// Initialize MySQLi on the detected port as well
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, $activePort);
if ($mysqli->connect_error) {
    // MySQLi fallback if needed
    $conn = null;
} else {
    $mysqli->set_charset("utf8mb4");
    $conn = $mysqli;
}

/**
 * Audit Log helper for portal events
 */
function logPortalAudit(PDO $pdo, ?int $studentId, string $action, string $details = "", string $status = "success"): void
{
    try {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (student_id, action, details, ip_address, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$studentId, $action, $details, $ip, $status]);
    } catch (Exception $e) {
        // Silently tolerate if audit logging table isn't created yet
    }
}
