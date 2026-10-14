-- ==============================================================
-- Practical 11: Student Management CRUD with Search and Filter
-- Database Schema and Test Data Dump
-- Database: studenthub
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- 1. Table structure for table `students`
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

-- 2. Seeded Test Dataset (Minimum 20 student records)
INSERT INTO `students` (`name`, `email`, `mobile`, `course`, `status`, `role`, `password`)
VALUES
('Aarav Sharma', '22dit001@charusat.edu.in', '9876543210', 'B.Tech Information Technology', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Aditi Patel', '22dit002@charusat.edu.in', '9876543211', 'B.Tech Information Technology', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Bhavya Joshi', '22dce003@charusat.edu.in', '9876543212', 'B.Tech Computer Engineering', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Chirag Varma', '22dce004@charusat.edu.in', '9876543213', 'B.Tech Computer Engineering', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Deepika Dave', '22dcs005@charusat.edu.in', '9876543214', 'B.Tech Computer Science', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Devang Mehta', '22dcs006@charusat.edu.in', '9876543215', 'B.Tech Computer Science', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Esha Trivedi', '22dai007@charusat.edu.in', '9876543216', 'B.Tech Artificial Intelligence', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Farhan Sheikh', '22dai008@charusat.edu.in', '9876543217', 'B.Tech Artificial Intelligence', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Gaurav Solanki', '22dit009@charusat.edu.in', '9876543218', 'B.Tech Information Technology', 'inactive', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Heli Panchal', '22dit010@charusat.edu.in', '9876543219', 'B.Tech Information Technology', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Ishaan Goswami', '22dce011@charusat.edu.in', '9876543220', 'B.Tech Computer Engineering', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Jahnvi Makwana', '22dce012@charusat.edu.in', '9876543221', 'B.Tech Computer Engineering', 'deleted', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Karan Chauhan', '22dcs013@charusat.edu.in', '9876543222', 'B.Tech Computer Science', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Khushi Soni', '22dcs014@charusat.edu.in', '9876543223', 'B.Tech Computer Science', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Manav Rathod', '22dai015@charusat.edu.in', '9876543224', 'B.Tech Artificial Intelligence', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Nisha Parmar', '22dai016@charusat.edu.in', '9876543225', 'B.Tech Artificial Intelligence', 'inactive', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Omkar Bhatt', '22dit017@charusat.edu.in', '9876543226', 'B.Tech Information Technology', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Prachi Shah', '22dit018@charusat.edu.in', '9876543227', 'B.Tech Information Technology', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Rohan Raval', '22dce019@charusat.edu.in', '9876543228', 'B.Tech Computer Engineering', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Riya Desai', '22dce020@charusat.edu.in', '9876543229', 'B.Tech Computer Engineering', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Siddharth Prajapati', '22dcs021@charusat.edu.in', '9876543230', 'B.Tech Computer Science', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u'),
('Tanvi Kothari', '22dai022@charusat.edu.in', '9876543231', 'B.Tech Artificial Intelligence', 'active', 'student', '$2y$10$wT8v11fKqS0fFfJgI4nNne5m3eN.v5m6LwFzJz5t3oG6v6oJ.pG3u')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `course` = VALUES(`course`);
