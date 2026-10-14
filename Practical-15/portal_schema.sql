-- ========================================================
-- StudentHub Portal: Consolidated Database Schema (Practical-15)
-- Compatible with MySQL 5.7+ / MariaDB 10.4+ / XAMPP
-- ========================================================

CREATE DATABASE IF NOT EXISTS `studenthub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub`;

-- 1. Students / Users Table
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
    `skills` VARCHAR(255) DEFAULT '["Problem Solving","Web Technologies"]',
    `address` TEXT DEFAULT NULL,
    `terms_accepted` TINYINT(1) DEFAULT 1,
    `role` VARCHAR(20) NOT NULL DEFAULT 'student',
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `last_login` DATETIME DEFAULT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Events Table
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

-- 3. Event Registrations Table
CREATE TABLE IF NOT EXISTS `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`event_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Audit Trail / Security Logs Table
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
    `status` VARCHAR(20) DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Remember-Me Tokens (Selector + Authenticator Split Token)
CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `token_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `selector` VARCHAR(64) NOT NULL UNIQUE,
    `token_hash` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- Seed Default Administrator (Password: Admin@123)
-- ========================================================
INSERT INTO `students` (`student_id`, `name`, `email`, `mobile`, `password`, `dob`, `gender`, `course`, `role`, `status`)
VALUES (1, 'Administrator', 'admin@charusat.edu.in', '9876543210', '$2y$10$w3U6s1XzW2w6X4h9f4J.E.tYgZfQkKpG5U6c1dE8q4A9B0C1D2E3F', '1990-01-01', 'Male', 'Faculty of Technology', 'admin', 'active')
ON DUPLICATE KEY UPDATE `role` = 'admin', `status` = 'active';

-- Seed Sample Events
INSERT INTO `events` (`event_id`, `title`, `description`, `category` ,`event_date`, `event_time`, `venue`, `status`, `max_seats`)
VALUES 
(1, 'AI & Machine Learning Bootcamp 2026', 'Comprehensive 3-day deep dive into Transformer architectures, LLM fine-tuning, and modern AI pipelines.', 'Technical', '2026-11-15', '09:30:00', 'Auditorium A, CSPIT', 'open', 150),
(2, 'National Hackathon - HackVerse', 'Annual 36-hour flagship hackathon organized by StudentHub with 5 lakhs total prize pool.', 'Competition', '2026-11-20', '10:00:00', 'Central Computing Facility', 'open', 200),
(3, 'Web3 & Decentralized App Workshop', 'Hands-on practical session building full-stack smart contracts and reactive web frontends.', 'Workshop', '2026-12-05', '14:00:00', 'Lab 4, DEPSTAR', 'open', 80),
(4, 'Annual Sports Gala & Athletics Meet', 'Inter-department cricket, football, volleyball, and track & field tournaments.', 'Sports', '2026-12-18', '08:00:00', 'University Sports Ground', 'closed', 300)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);
