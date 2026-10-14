<?php
/**
 * Practical 13: Database Connection & Schema Setup
 * Connects via PDO with utf8mb4 charset and prepared statements.
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
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Ensure all required columns exist (in case table was created with minimal columns)
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
    if (!in_array("created_at", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
    if (!in_array("updated_at", $columns, true)) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    // 3. Seed sample events if fewer than 5 records exist
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `events`")->fetchColumn();
    if ($count < 5) {
        $seedData = [
            [
                "National Level Hackathon 2026",
                "36-hour intense hackathon bringing together top minds to build AI, IoT, and Web3 solutions.",
                "Technical",
                date("Y-m-d", strtotime("+14 days")),
                "09:00:00",
                "Central Auditorium, CHARUSAT",
                "open"
            ],
            [
                "AI & Machine Learning Symposium",
                "Interactive masterclass on transformer models, deep learning pipelines, and cloud GPU clusters.",
                "Workshop",
                date("Y-m-d", strtotime("+21 days")),
                "10:30:00",
                "CSPIT Seminar Hall 1",
                "open"
            ],
            [
                "Spoural: Annual Cultural Fest",
                "University-wide cultural celebration featuring music, dance, theatrical acts, and fashion showcase.",
                "Cultural",
                date("Y-m-d", strtotime("+35 days")),
                "17:00:00",
                "University Sports Ground",
                "open"
            ],
            [
                "Inter-College Basketball Championship",
                "Championship tournament featuring 20 collegiate teams competing for the annual university trophy.",
                "Sports",
                date("Y-m-d", strtotime("-3 days")),
                "08:30:00",
                "Indoor Sports Complex",
                "closed"
            ],
            [
                "Cloud Computing & DevOps Bootcamp",
                "Hands-on architectural lab exploring Docker containerization, Kubernetes, and CI/CD pipelines.",
                "Workshop",
                date("Y-m-d", strtotime("+8 days")),
                "11:00:00",
                "Lab 301, IT Department",
                "open"
            ],
            [
                "Cyber Security & Ethical Hacking Seminar",
                "Industry expert talk on red-teaming, zero-day threat analysis, and bug bounty methodologies.",
                "Seminar",
                date("Y-m-d", strtotime("+28 days")),
                "14:00:00",
                "DEPSTAR Auditorium",
                "open"
            ]
        ];

        $ins = $pdo->prepare("
            INSERT INTO `events` (`event_name`, `description`, `category`, `event_date`, `event_time`, `venue`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($seedData as $row) {
            $ins->execute($row);
        }
    }

} catch (PDOException $e) {
    // If running as API call, return JSON error
    if (basename($_SERVER["SCRIPT_FILENAME"] ?? "") === "api.php") {
        http_response_code(500);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "success" => false,
            "status" => 500,
            "message" => "Database connection failure: " . $e->getMessage()
        ]);
        exit;
    }
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}
