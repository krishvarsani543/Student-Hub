-- ==============================================================
-- Practical 12: Event Management CRUD with Poster Upload and File Validation
-- Database Schema and Test Data Dump
-- Database: studenthub
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- 1. Table structure for table `events`
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

-- 2. Table structure for table `event_audit_logs` (Advanced Extension)
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

-- 3. Seed Sample University Events
INSERT INTO `events` (`event_name`, `description`, `category`, `event_date`, `event_time`, `venue`, `status`, `poster`)
VALUES
('National Level Hackathon 2026', '36-hour intense hackathon bringing together top engineering minds to solve real-world problems in AI and Web3.', 'Technical', '2026-11-15', '09:00:00', 'Central Auditorium, CHARUSAT', 'open', NULL),
('AI & Machine Learning Symposium', 'Keynote sessions with industry leaders from Google and Microsoft discussing generative AI architectures.', 'Workshop', '2026-11-20', '10:30:00', 'CSPIT Seminar Hall 1', 'open', NULL),
('Spoural: Annual Cultural Fest', 'Grand cultural celebration with music, dance, theatre, fashion show, and celebrity performance nights.', 'Cultural', '2026-12-05', '17:00:00', 'University Sports Ground', 'open', NULL),
('Inter-College Basketball Championship', 'State-level basketball tournament featuring teams from over 24 colleges competing for the championship cup.', 'Sports', '2026-10-08', '08:00:00', 'Indoor Sports Complex', 'closed', NULL),
('Cloud Computing & DevOps Bootcamp', 'Hands-on workshop on Docker, Kubernetes, CI/CD pipelines and AWS cloud deployment patterns.', 'Workshop', '2026-11-01', '11:00:00', 'Lab 301, IT Department', 'open', NULL)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `category` = VALUES(`category`);
