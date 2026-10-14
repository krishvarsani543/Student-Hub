<?php

$dbHost = "127.0.0.1";
$dbName = "studenthub";
$dbUser = "root";
$dbPass = "";

// Auto-detect port: try XAMPP port 3307 first, then default 3306
$ports = [3307, 3306];
$pdo = null;
$connectionError = "";

foreach ($ports as $port) {
    try {
        $pdo = new PDO(
            "mysql:host=$dbHost;port=$port;charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]
        );

        // Ensure database exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        // Ensure students table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `students` (
                `student_id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(100) NOT NULL UNIQUE,
                `mobile` VARCHAR(15) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `dob` DATE DEFAULT NULL,
                `gender` VARCHAR(20) DEFAULT NULL,
                `course` VARCHAR(100) DEFAULT NULL,
                `country` VARCHAR(50) DEFAULT NULL,
                `state` VARCHAR(50) DEFAULT NULL,
                `city` VARCHAR(50) DEFAULT NULL,
                `skills` VARCHAR(255) DEFAULT NULL,
                `address` TEXT DEFAULT NULL,
                `terms_accepted` TINYINT(1) DEFAULT 1,
                `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `role` VARCHAR(20) DEFAULT 'student',
                `last_login` DATETIME DEFAULT NULL,
                `remember_token_hash` VARCHAR(64) DEFAULT NULL,
                `remember_token_expires` DATETIME DEFAULT NULL,
                `status` VARCHAR(20) DEFAULT 'active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        break;
    } catch (PDOException $e) {
        $connectionError = $e->getMessage();
        $pdo = null;
    }
}

if (!$pdo) {
    die("Database connection failed: " . $connectionError);
}

?>