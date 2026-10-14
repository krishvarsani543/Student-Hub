<?php
/**
 * Practical 14: Database Connection, Bootstrapper & Audit Logger
 * Manages tables for users/students, events, registrations, and audit_logs.
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

    // 2. Ensure students/users table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `student_id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `mobile` VARCHAR(15) DEFAULT NULL,
            `course` VARCHAR(100) DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'active',
            `role` VARCHAR(20) NOT NULL DEFAULT 'student',
            `last_login` DATETIME DEFAULT NULL,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Ensure status column exists
    $cols = $pdo->query("SHOW COLUMNS FROM `students`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array("status", $cols, true)) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `course`");
    }

    // 3. Ensure events table exists
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
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Ensure registrations table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `registrations` (
            `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `event_id` INT NOT NULL,
            `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`student_id`),
            INDEX (`event_id`),
            CONSTRAINT `fk_reg_student_p14` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
            CONSTRAINT `fk_reg_event_p14` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 5. Ensure audit_logs table exists (Core Practical 14 Requirement)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `audit_logs` (
            `log_id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT DEFAULT NULL,
            `user_email` VARCHAR(100) DEFAULT NULL,
            `action` VARCHAR(50) NOT NULL,
            `details` TEXT NOT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`action`),
            INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 6. Ensure default Admin user exists
    $checkAdmin = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $checkAdmin->execute(["admin@charusat.edu.in"]);
    if (!$checkAdmin->fetch()) {
        $insAdmin = $pdo->prepare("
            INSERT INTO students (name, email, role, status, password, course)
            VALUES (?, ?, 'admin', 'active', ?, 'Faculty of Technology')
        ");
        $insAdmin->execute([
            "Administrator",
            "admin@charusat.edu.in",
            password_hash("Admin@123", PASSWORD_DEFAULT)
        ]);
    }

    // 7. Seed Sample Registrations across months for Analytics Chart (Advanced Extension)
    $regCount = (int)$pdo->query("SELECT COUNT(*) FROM `registrations`")->fetchColumn();
    if ($regCount < 10) {
        // Fetch existing students and events
        $studIds = $pdo->query("SELECT student_id FROM students WHERE role = 'student' LIMIT 15")->fetchAll(PDO::FETCH_COLUMN);
        $eventIds = $pdo->query("SELECT event_id FROM events LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($studIds) && !empty($eventIds)) {
            $sampleDates = [
                "2026-05-12 10:20:00",
                "2026-05-24 14:15:00",
                "2026-06-05 09:40:00",
                "2026-06-18 16:30:00",
                "2026-07-02 11:10:00",
                "2026-07-19 15:55:00",
                "2026-08-11 10:00:00",
                "2026-08-27 13:25:00",
                "2026-09-04 09:15:00",
                "2026-09-22 17:45:00",
                "2026-10-02 12:30:00",
                "2026-10-10 14:00:00",
                "2026-10-12 11:45:00"
            ];

            $insReg = $pdo->prepare("
                INSERT IGNORE INTO `registrations` (`student_id`, `event_id`, `registration_date`)
                VALUES (?, ?, ?)
            ");

            $idx = 0;
            foreach ($sampleDates as $dateStr) {
                $sId = $studIds[$idx % count($studIds)];
                $eId = $eventIds[$idx % count($eventIds)];
                $insReg->execute([$sId, $eId, $dateStr]);
                $idx++;
            }
        }
    }

    // 8. Seed Initial Audit Logs if empty
    $auditCount = (int)$pdo->query("SELECT COUNT(*) FROM `audit_logs`")->fetchColumn();
    if ($auditCount < 3) {
        $initLogs = [
            ["LOGIN", "Admin user logged into the portal.", "admin@charusat.edu.in", "127.0.0.1", date("Y-m-d H:i:s", strtotime("-2 hours"))],
            ["STATUS_TOGGLE", "Changed student account status to active.", "admin@charusat.edu.in", "127.0.0.1", date("Y-m-d H:i:s", strtotime("-1 hour"))],
            ["EVENT_CREATE", "Moderated and published new university hackathon.", "admin@charusat.edu.in", "127.0.0.1", date("Y-m-d H:i:s", strtotime("-30 minutes"))]
        ];

        $insLog = $pdo->prepare("
            INSERT INTO `audit_logs` (`action`, `details`, `user_email`, `ip_address`, `created_at`)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($initLogs as $lg) {
            $insLog->execute($lg);
        }
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}

/**
 * Record an entry into the audit_logs table
 */
function recordAuditLog(PDO $pdo, ?int $userId, ?string $userEmail, string $action, string $details): void
{
    try {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $stmt = $pdo->prepare("
            INSERT INTO `audit_logs` (`user_id`, `user_email`, `action`, `details`, `ip_address`, `created_at`)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $userEmail, $action, $details, $ip]);
    } catch (Exception $e) {
        // Silently continue so main action proceeds
    }
}
