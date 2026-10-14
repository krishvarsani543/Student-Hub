-- ==============================================================
-- Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log
-- Database Schema and Test Data Dump
-- Database: studenthub
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- 1. Table structure for table `students` / `users`
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

-- 2. Table structure for table `events`
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

-- 3. Table structure for table `registrations`
CREATE TABLE IF NOT EXISTS `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`student_id`),
    INDEX (`event_id`),
    CONSTRAINT `fk_reg_student_p14_dump` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reg_event_p14_dump` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table structure for table `audit_logs` (Core Requirement)
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

-- 5. Seed Admin Account
INSERT INTO `students` (`name`, `email`, `role`, `status`, `password`, `course`)
VALUES (
    'Administrator',
    'admin@charusat.edu.in',
    'admin',
    'active',
    '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u', -- Admin@123
    'Faculty of Technology'
)
ON DUPLICATE KEY UPDATE `role` = 'admin', `status` = 'active';

-- 6. Seed Sample Audit Logs
INSERT INTO `audit_logs` (`action`, `details`, `user_email`, `ip_address`, `created_at`)
VALUES
('LOGIN', 'Admin user logged into the portal.', 'admin@charusat.edu.in', '127.0.0.1', '2026-10-13 01:30:00'),
('STATUS_TOGGLE', 'Changed student account status to active.', 'admin@charusat.edu.in', '127.0.0.1', '2026-10-13 02:15:00'),
('EVENT_CREATE', 'Moderated and published new university hackathon.', 'admin@charusat.edu.in', '127.0.0.1', '2026-10-13 02:45:00');
