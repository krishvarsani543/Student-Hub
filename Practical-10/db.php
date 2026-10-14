<?php
/**
 * Practical 10: Secure Database Connection & Schema Bootstrapper
 * Connects via PDO with secure error handling and prepared statements.
 */

$host = "localhost";
$dbname = "studenthub";
$username = "root";
$password = "";

try {
    // 1. Establish initial connection with auto-detected port
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

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // 2. Ensure students table exists with role and last_login columns
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `student_id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `mobile` VARCHAR(15) DEFAULT NULL,
            `course` VARCHAR(100) DEFAULT NULL,
            `role` VARCHAR(20) NOT NULL DEFAULT 'student',
            `last_login` DATETIME DEFAULT NULL,
            `password` VARCHAR(255) NOT NULL,
            `dob` DATE DEFAULT NULL,
            `gender` VARCHAR(20) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `country` VARCHAR(50) DEFAULT NULL,
            `state` VARCHAR(50) DEFAULT NULL,
            `city` VARCHAR(50) DEFAULT NULL,
            `skills` TEXT DEFAULT NULL,
            `terms_accepted` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Check if role and last_login exist (in case table existed from earlier practicals)
    $columns = $pdo->query("SHOW COLUMNS FROM `students`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array("role", $columns, true)) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'student' AFTER `course`");
    }
    if (!in_array("last_login", $columns, true)) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `last_login` DATETIME DEFAULT NULL AFTER `role`");
    }

    // 3. Ensure remember_tokens table exists for advanced token-based authentication
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `remember_tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `selector` VARCHAR(24) NOT NULL UNIQUE,
            `token_hash` VARCHAR(64) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`selector`),
            INDEX (`student_id`),
            CONSTRAINT `fk_remember_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Ensure seeded test users exist with secure password hashes
    // Admin user: admin@charusat.edu.in / Admin@123
    $adminCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $adminCheck->execute(["admin@charusat.edu.in"]);
    if (!$adminCheck->fetch()) {
        $adminStmt = $pdo->prepare("
            INSERT INTO students (name, email, role, password, course)
            VALUES (?, ?, ?, ?, ?)
        ");
        $adminStmt->execute([
            "Administrator",
            "admin@charusat.edu.in",
            "admin",
            password_hash("Admin@123", PASSWORD_DEFAULT),
            "Faculty of Technology"
        ]);
    } else {
        // Ensure admin has admin role and valid hash
        $pdo->prepare("UPDATE students SET role = 'admin', password = ? WHERE email = 'admin@charusat.edu.in'")
            ->execute([password_hash("Admin@123", PASSWORD_DEFAULT)]);
    }

    // Student user 1: 22dit045@charusat.edu.in / Student@123
    $studCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $studCheck->execute(["25dcs140@charusat.edu.in"]);
    if (!$studCheck->fetch()) {
        $studStmt = $pdo->prepare("
            INSERT INTO students (name, email, role, password, course)
            VALUES (?, ?, ?, ?, ?)
        ");
        $studStmt->execute([
            "Krish Varsani",
            "25dcs140@charusat.edu.in",
            "student",
            password_hash("Student@123", PASSWORD_DEFAULT),
            "B.Tech Computer Science"
        ]);
    } else {
        $pdo->prepare("UPDATE students SET role = 'student', password = ? WHERE email = '25dcs140@charusat.edu.in'")
            ->execute([password_hash("Student@123", PASSWORD_DEFAULT)]);
    }

    // Student user 2: student@charusat.edu.in / Student@123
    $stud2Check = $pdo->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $stud2Check->execute(["student@charusat.edu.in"]);
    if (!$stud2Check->fetch()) {
        $stud2Stmt = $pdo->prepare("
            INSERT INTO students (name, email, role, password, course)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stud2Stmt->execute([
            "Demo Student",
            "student@charusat.edu.in",
            "student",
            password_hash("Student@123", PASSWORD_DEFAULT),
            "B.Tech Computer Science"
        ]);
    }

} catch (PDOException $e) {
    die("Database connection error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}
