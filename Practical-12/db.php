<?php
/**
 * Practical 12: Database Connection & Schema Bootstrapper
 * Connects via PDO, ensures events and audit_logs tables exist, creates protected upload directories,
 * and seeds initial university events.
 */

$host = "localhost";
$dbname = "studenthub";
$username = "root";
$password = "";

try {
    $ports = [3307, 3306];
    $pdo = null;
    $connectError = null;

    foreach ($ports as $port) {
        try {
            $pdo = new PDO(
                "mysql:host=127.0.0.1;port=$port;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 2
                ]
            );
            break;
        } catch (PDOException $e) {
            $connectError = $e;
        }
    }

    if (!$pdo) {
        throw $connectError;
    }

    // 1. Ensure database exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // 2. Ensure events table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `events` (
            `event_id` INT AUTO_INCREMENT PRIMARY KEY,
            `event_name` VARCHAR(150) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'Technical',
            `event_date` DATE NOT NULL,
            `event_time` TIME DEFAULT '10:00:00',
            `venue` VARCHAR(150) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'open',
            `poster` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Check if columns exist (in case events table existed from earlier practicals with only basic columns)
    $columns = $pdo->query("SHOW COLUMNS FROM `events`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array("description", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `description` TEXT DEFAULT NULL AFTER `event_name`");
    }
    if (!in_array("category", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'Technical' AFTER `description`");
    }
    if (!in_array("event_time", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `event_time` TIME DEFAULT '10:00:00' AFTER `event_date`");
    }
    if (!in_array("status", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'open' AFTER `venue`");
    }
    if (!in_array("poster", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `poster` VARCHAR(255) DEFAULT NULL AFTER `status`");
    }
    if (!in_array("created_at", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
    if (!in_array("updated_at", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    // 3. Ensure event_audit_logs table exists (Advanced Extension)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `event_audit_logs` (
            `log_id` INT AUTO_INCREMENT PRIMARY KEY,
            `event_id` INT DEFAULT NULL,
            `action` VARCHAR(50) NOT NULL,
            `details` TEXT NOT NULL,
            `performed_by` VARCHAR(100) DEFAULT 'Admin',
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`event_id`),
            INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Ensure Uploads directory exists with Directory Protection (.htaccess) (Advanced Extension)
    $uploadDir = __DIR__ . "/uploads/posters";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Protect uploads directory against direct script execution
    $htaccessFile = $uploadDir . "/.htaccess";
    if (!file_exists($htaccessFile)) {
        $htaccessContent = "# Prevent script execution in upload directory\n" .
            "<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|phps|cgi|pl|exe|sh|py|asp|aspx)$\">\n" .
            "    Require all denied\n" .
            "</FilesMatch>\n" .
            "Options -Indexes -ExecCGI\n";
        file_put_contents($htaccessFile, $htaccessContent);
    }

    $indexHtml = $uploadDir . "/index.html";
    if (!file_exists($indexHtml)) {
        file_put_contents($indexHtml, "<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>403 Forbidden</h1></body></html>");
    }

    // 5. Seed sample events if table is empty or has fewer than 4 events
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `events`")->fetchColumn();
    if ($count < 4) {
        $seedEvents = [
            [
                "National Level Hackathon 2026",
                "36-hour intense hackathon bringing together top engineering minds to solve real-world problems in AI and Web3.",
                "Technical",
                date("Y-m-d", strtotime("+15 days")),
                "09:00:00",
                "Central Auditorium, CHARUSAT",
                "open",
                null
            ],
            [
                "AI & Machine Learning Symposium",
                "Keynote sessions with industry leaders from Google and Microsoft discussing generative AI architectures.",
                "Workshop",
                date("Y-m-d", strtotime("+25 days")),
                "10:30:00",
                "CSPIT Seminar Hall 1",
                "open",
                null
            ],
            [
                "Spoural: Annual Cultural Fest",
                "Grand cultural celebration with music, dance, theatre, fashion show, and celebrity performance nights.",
                "Cultural",
                date("Y-m-d", strtotime("+45 days")),
                "17:00:00",
                "University Sports Ground",
                "open",
                null
            ],
            [
                "Inter-College Basketball Championship",
                "State-level basketball tournament featuring teams from over 24 colleges competing for the championship cup.",
                "Sports",
                date("Y-m-d", strtotime("-5 days")),
                "08:00:00",
                "Indoor Sports Complex",
                "closed",
                null
            ],
            [
                "Cloud Computing & DevOps Bootcamp",
                "Hands-on workshop on Docker, Kubernetes, CI/CD pipelines and AWS cloud deployment patterns.",
                "Workshop",
                date("Y-m-d", strtotime("+10 days")),
                "11:00:00",
                "Lab 301, IT Department",
                "open",
                null
            ]
        ];

        $insertStmt = $pdo->prepare("
            INSERT INTO `events` (`event_name`, `description`, `category`, `event_date`, `event_time`, `venue`, `status`, `poster`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($seedEvents as $ev) {
            $insertStmt->execute($ev);
            $newId = (int)$pdo->lastInsertId();

            // Insert initial audit log for seeded record
            $logStmt = $pdo->prepare("
                INSERT INTO `event_audit_logs` (`event_id`, `action`, `details`, `performed_by`, `ip_address`)
                VALUES (?, 'CREATE', ?, 'System Seeder', '127.0.0.1')
            ");
            $logStmt->execute([$newId, "Initial seeded event: " . $ev[0]]);
        }
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}

/**
 * Helper to record an audit log entry
 */
function recordEventAudit(PDO $pdo, ?int $eventId, string $action, string $details): void
{
    try {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $stmt = $pdo->prepare("
            INSERT INTO `event_audit_logs` (`event_id`, `action`, `details`, `performed_by`, `ip_address`)
            VALUES (?, ?, ?, 'Admin', ?)
        ");
        $stmt->execute([$eventId, $action, $details, $ip]);
    } catch (Exception $e) {
        // Silently ignore audit log failures so primary action proceeds
    }
}
