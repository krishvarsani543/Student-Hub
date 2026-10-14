-- ==============================================================
-- Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Seed Test Events for API consumption
INSERT INTO `events` (`event_name`, `description`, `category`, `event_date`, `event_time`, `venue`, `status`)
VALUES
('National Level Hackathon 2026', '36-hour intense hackathon bringing together top minds to build AI, IoT, and Web3 solutions.', 'Technical', '2026-11-14', '09:00:00', 'Central Auditorium, CHARUSAT', 'open'),
('AI & Machine Learning Symposium', 'Interactive masterclass on transformer models, deep learning pipelines, and cloud GPU clusters.', 'Workshop', '2026-11-21', '10:30:00', 'CSPIT Seminar Hall 1', 'open'),
('Spoural: Annual Cultural Fest', 'University-wide cultural celebration featuring music, dance, theatrical acts, and fashion showcase.', 'Cultural', '2026-12-05', '17:00:00', 'University Sports Ground', 'open'),
('Inter-College Basketball Championship', 'Championship tournament featuring 20 collegiate teams competing for the annual university trophy.', 'Sports', '2026-10-10', '08:30:00', 'Indoor Sports Complex', 'closed'),
('Cloud Computing & DevOps Bootcamp', 'Hands-on architectural lab exploring Docker containerization, Kubernetes, and CI/CD pipelines.', 'Workshop', '2026-11-01', '11:00:00', 'Lab 301, IT Department', 'open'),
('Cyber Security & Ethical Hacking Seminar', 'Industry expert talk on red-teaming, zero-day threat analysis, and bug bounty methodologies.', 'Seminar', '2026-11-28', '14:00:00', 'DEPSTAR Auditorium', 'open')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `category` = VALUES(`category`);
