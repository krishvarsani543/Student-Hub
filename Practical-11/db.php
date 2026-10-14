<?php
/**
 * Practical 11: Database Connection & Schema Setup using MySQLi
 * Connects to MySQL using MySQLi and ensures all required columns and seeded records exist.
 */

$host = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

// 1. Establish MySQLi connection with auto-detected port
$ports = [3307, 3306];
$conn = null;
$connectError = "";

foreach ($ports as $port) {
    $conn = @new mysqli("127.0.0.1", $username, $password, "", $port);
    if (!$conn->connect_error) {
        break;
    }
    $connectError = $conn->connect_error;
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $connectError);
}

// 2. Ensure database exists and select it
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);
$conn->set_charset("utf8mb4");

// 3. Ensure students table exists
$tableSql = "
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
";
$conn->query($tableSql);

// 4. Check if 'status' and 'created_at' columns exist in students table
$colResult = $conn->query("SHOW COLUMNS FROM `students` LIKE 'status'");
if ($colResult->num_rows === 0) {
    $conn->query("ALTER TABLE `students` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `course`");
}

$createdResult = $conn->query("SHOW COLUMNS FROM `students` LIKE 'created_at'");
if ($createdResult->num_rows === 0) {
    $conn->query("ALTER TABLE `students` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

// 5. Ensure at least 20 seeded student records exist (as required by Practical 11 specification)
$countRes = $conn->query("SELECT COUNT(*) AS total FROM `students`");
$rowCount = $countRes->fetch_assoc()["total"] ?? 0;

if ($rowCount < 20) {
    $defaultPassHash = password_hash("Student@123", PASSWORD_DEFAULT);

    $seedStudents = [
        ["Aarav Sharma", "22dit001@charusat.edu.in", "9876543210", "B.Tech Information Technology", "active"],
        ["Aditi Patel", "22dit002@charusat.edu.in", "9876543211", "B.Tech Information Technology", "active"],
        ["Bhavya Joshi", "22dce003@charusat.edu.in", "9876543212", "B.Tech Computer Engineering", "active"],
        ["Chirag Varma", "22dce004@charusat.edu.in", "9876543213", "B.Tech Computer Engineering", "active"],
        ["Deepika Dave", "22dcs005@charusat.edu.in", "9876543214", "B.Tech Computer Science", "active"],
        ["Devang Mehta", "22dcs006@charusat.edu.in", "9876543215", "B.Tech Computer Science", "active"],
        ["Esha Trivedi", "22dai007@charusat.edu.in", "9876543216", "B.Tech Artificial Intelligence", "active"],
        ["Farhan Sheikh", "22dai008@charusat.edu.in", "9876543217", "B.Tech Artificial Intelligence", "active"],
        ["Gaurav Solanki", "22dit009@charusat.edu.in", "9876543218", "B.Tech Information Technology", "inactive"],
        ["Heli Panchal", "22dit010@charusat.edu.in", "9876543219", "B.Tech Information Technology", "active"],
        ["Ishaan Goswami", "22dce011@charusat.edu.in", "9876543220", "B.Tech Computer Engineering", "active"],
        ["Jahnvi Makwana", "22dce012@charusat.edu.in", "9876543221", "B.Tech Computer Engineering", "deleted"],
        ["Karan Chauhan", "22dcs013@charusat.edu.in", "9876543222", "B.Tech Computer Science", "active"],
        ["Khushi Soni", "22dcs014@charusat.edu.in", "9876543223", "B.Tech Computer Science", "active"],
        ["Manav Rathod", "22dai015@charusat.edu.in", "9876543224", "B.Tech Artificial Intelligence", "active"],
        ["Nisha Parmar", "22dai016@charusat.edu.in", "9876543225", "B.Tech Artificial Intelligence", "inactive"],
        ["Omkar Bhatt", "22dit017@charusat.edu.in", "9876543226", "B.Tech Information Technology", "active"],
        ["Prachi Shah", "22dit018@charusat.edu.in", "9876543227", "B.Tech Information Technology", "active"],
        ["Rohan Raval", "22dce019@charusat.edu.in", "9876543228", "B.Tech Computer Engineering", "active"],
        ["Riya Desai", "22dce020@charusat.edu.in", "9876543229", "B.Tech Computer Engineering", "active"],
        ["Siddharth Prajapati", "22dcs021@charusat.edu.in", "9876543230", "B.Tech Computer Science", "active"],
        ["Tanvi Kothari", "22dai022@charusat.edu.in", "9876543231", "B.Tech Artificial Intelligence", "active"]
    ];

    $seedStmt = $conn->prepare("
        INSERT INTO `students` (`name`, `email`, `mobile`, `course`, `status`, `role`, `password`)
        VALUES (?, ?, ?, ?, ?, 'student', ?)
        ON DUPLICATE KEY UPDATE `course` = VALUES(`course`), `status` = VALUES(`status`)
    ");

    foreach ($seedStudents as $student) {
        $seedStmt->bind_param("ssssss", $student[0], $student[1], $student[2], $student[3], $student[4], $defaultPassHash);
        $seedStmt->execute();
    }
    $seedStmt->close();
}
