<?php
/**
 * Practical 15: One-Click Portal Database Setup & Seeder
 * Initializes all tables, verifies integrity, and ensures default admin & test credentials exist.
 */

require_once __DIR__ . "/db.php";

header("Content-Type: text/html; charset=UTF-8");

$results = [];

try {
    // 1. Ensure students table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `student_id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `mobile` VARCHAR(15) DEFAULT NULL,
            `password` VARCHAR(255) NOT NULL,
            `dob` DATE DEFAULT NULL,
            `gender` VARCHAR(20) DEFAULT 'Male',
            `course` VARCHAR(100) DEFAULT 'Information Technology',
            `country` VARCHAR(50) DEFAULT 'India',
            `state` VARCHAR(50) DEFAULT 'Gujarat',
            `city` VARCHAR(50) DEFAULT 'Anand',
            `skills` VARCHAR(255) DEFAULT '[\"Problem Solving\",\"Web Technologies\"]',
            `address` TEXT DEFAULT NULL,
            `terms_accepted` TINYINT(1) DEFAULT 1,
            `role` VARCHAR(20) NOT NULL DEFAULT 'student',
            `status` VARCHAR(20) NOT NULL DEFAULT 'active',
            `last_login` DATETIME DEFAULT NULL,
            `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $results[] = ["step" => "Table: students", "status" => "Ready", "success" => true];

    // 2. Ensure events table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `events` (
            `event_id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'Technical',
            `event_date` DATE NOT NULL,
            `event_time` TIME NOT NULL DEFAULT '10:00:00',
            `venue` VARCHAR(100) NOT NULL DEFAULT 'Seminar Hall 1',
            `poster_image` VARCHAR(255) DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'open',
            `max_seats` INT NOT NULL DEFAULT 100,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $results[] = ["step" => "Table: events", "status" => "Ready", "success" => true];

    // 3. Ensure registrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `registrations` (
            `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `event_id` INT NOT NULL,
            `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
            UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $results[] = ["step" => "Table: registrations", "status" => "Ready", "success" => true];

    // 4. Ensure audit_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `audit_logs` (
            `log_id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT DEFAULT NULL,
            `action` VARCHAR(100) NOT NULL,
            `details` TEXT DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
            `status` VARCHAR(20) DEFAULT 'success',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $results[] = ["step" => "Table: audit_logs", "status" => "Ready", "success" => true];

    // 5. Ensure remember_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `remember_tokens` (
            `token_id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `selector` VARCHAR(64) NOT NULL UNIQUE,
            `token_hash` VARCHAR(64) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $results[] = ["step" => "Table: remember_tokens", "status" => "Ready", "success" => true];

    // 6. Ensure default Administrator account (admin@charusat.edu.in / Admin@123)
    $adminEmail = "admin@charusat.edu.in";
    $adminPass = password_hash("Admin@123", PASSWORD_DEFAULT);
    $adminCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $adminCheck->execute([$adminEmail]);
    $adminExists = $adminCheck->fetch();

    if ($adminExists) {
        $upStmt = $pdo->prepare("UPDATE students SET password = ?, role = 'admin', status = 'active' WHERE email = ?");
        $upStmt->execute([$adminPass, $adminEmail]);
        $results[] = ["step" => "Default Admin (admin@charusat.edu.in)", "status" => "Verified & Password Updated", "success" => true];
    } else {
        $insStmt = $pdo->prepare("
            INSERT INTO students (name, email, password, role, status, course)
            VALUES ('Administrator', ?, ?, 'admin', 'active', 'Faculty of Technology')
        ");
        $insStmt->execute([$adminEmail, $adminPass]);
        $results[] = ["step" => "Default Admin (admin@charusat.edu.in)", "status" => "Created", "success" => true];
    }

    // 7. Ensure demo student Rohan Shah exists (25dce099@charusat.edu.in / Rohan@123)
    $rohanEmail = "25dce099@charusat.edu.in";
    $rohanPass = password_hash("Rohan@123", PASSWORD_DEFAULT);
    $rohanCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $rohanCheck->execute([$rohanEmail]);
    if (!$rohanCheck->fetch()) {
        $insRohan = $pdo->prepare("
            INSERT INTO students (name, email, password, mobile, dob, course, city, skills, role, status)
            VALUES ('Rohan Shah', ?, ?, '9876543210', '2004-03-21', 'B.Tech CSE', 'Vadodara', '[\"Java\",\"Python\"]', 'student', 'active')
        ");
        $insRohan->execute([$rohanEmail, $rohanPass]);
        $results[] = ["step" => "Demo Student (25dce099@charusat.edu.in)", "status" => "Created", "success" => true];
    } else {
        $results[] = ["step" => "Demo Student (25dce099@charusat.edu.in)", "status" => "Already Present", "success" => true];
    }

    // 8. Seed sample events if empty
    $evCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($evCount == 0) {
        $pdo->exec("
            INSERT INTO events (title, description, category, event_date, event_time, venue, status, max_seats) VALUES
            ('AI & Machine Learning Bootcamp 2026', 'Comprehensive 3-day deep dive into Transformer architectures, LLM fine-tuning, and modern AI pipelines.', 'Technical', '2026-11-15', '09:30:00', 'Auditorium A, CSPIT', 'open', 150),
            ('National Hackathon - HackVerse', 'Annual 36-hour flagship hackathon organized by StudentHub with 5 lakhs total prize pool.', 'Competition', '2026-11-20', '10:00:00', 'Central Computing Facility', 'open', 200),
            ('Web3 & Decentralized App Workshop', 'Hands-on practical session building full-stack smart contracts and reactive web frontends.', 'Workshop', '2026-12-05', '14:00:00', 'Lab 4, DEPSTAR', 'open', 80);
        ");
        $results[] = ["step" => "Seed Events", "status" => "3 Sample Events Seeded", "success" => true];
    } else {
        $results[] = ["step" => "Seed Events", "status" => "$evCount Events Present", "success" => true];
    }

} catch (Exception $e) {
    $results[] = ["step" => "Setup Execution", "status" => "Error: " . $e->getMessage(), "success" => false];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal Setup & Bootstrapper</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f1f5f9; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #38bdf8; margin-top: 0; font-size: 26px; border-bottom: 2px solid #334155; padding-bottom: 12px; }
        .badge-port { background: #0369a1; color: white; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px 14px; border-bottom: 1px solid #334155; }
        th { background: #0f172a; color: #94a3b8; }
        .success { color: #4ade80; font-weight: bold; }
        .fail { color: #f87171; font-weight: bold; }
        .actions { margin-top: 25px; display: flex; gap: 12px; }
        .btn { padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block; }
        .btn-primary { background: #0284c7; color: white; }
        .btn-secondary { background: #334155; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 StudentHub Portal Database Setup</h1>
        <p>Active MySQL Port: <span class="badge-port">Port <?= htmlspecialchars((string)$activePort) ?></span></p>

        <table>
            <thead>
                <tr>
                    <th>Component / Step</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $res): ?>
                <tr>
                    <td><?= htmlspecialchars($res["step"]) ?></td>
                    <td class="<?= $res["success"] ? "success" : "fail" ?>">
                        <?= $res["success"] ? "✔ " : "✖ " ?><?= htmlspecialchars($res["status"]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="index.php" class="btn btn-primary">Go to Integrated Portal</a>
            <a href="verify_portal.php" class="btn btn-secondary">Run Automated Verification</a>
        </div>
    </div>
</body>
</html>
