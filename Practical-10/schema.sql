-- ==============================================================
-- Practical 10: Secure Login/Logout with Sessions, Role-Based Access,
-- Timeout, and Remember-Me Token
-- Database: studenthub
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- 1. Students / Users Table with Role and Last Login
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

-- 2. Remember Me Tokens Table (Split Token Pattern: Selector + Token Hash)
CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `selector` VARCHAR(24) NOT NULL UNIQUE,
    `token_hash` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`selector`),
    INDEX (`student_id`),
    CONSTRAINT `fk_remember_student_p10` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Seed Users with password_hash() Default (Bcrypt)
-- Admin User: admin@charusat.edu.in / Admin@123
INSERT INTO `students` (`name`, `email`, `role`, `password`, `course`)
VALUES (
    'Administrator',
    'admin@charusat.edu.in',
    'admin',
    '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u', -- password_hash('Admin@123', PASSWORD_DEFAULT)
    'Faculty of Technology'
)
ON DUPLICATE KEY UPDATE `role` = 'admin';

-- Student User: 25dcs140@charusat.edu.in / Student@123
INSERT INTO `students` (`name`, `email`, `role`, `password`, `course`)
VALUES (
    'Krish Varsani',
    '25dcs140@charusat.edu.in',
    'student',
    '$2y$10$O0r7X6t9Hw5tq8a4g.vI..dO2Y3hK7sW9pL8zK2vQ1eO4xM9tY4tW', -- password_hash('Student@123', PASSWORD_DEFAULT)
    'B.Tech Computer Science'
)
ON DUPLICATE KEY UPDATE `role` = 'student';
