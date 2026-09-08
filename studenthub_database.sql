-- ========================================================
-- StudentHub Portal Normalized Database Schema
-- Subject: ITUE203 Web Development Frameworks (Practical 8)
-- Student: Malkesh Patel (Roll No: 25DCS089)
-- Institute: FTE CHARUSAT
-- ========================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- 1. Students Table
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(15) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year_level` VARCHAR(20) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `role` ENUM('student', 'admin') DEFAULT 'student',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. User Roles Table (Role-Based Access Control)
CREATE TABLE IF NOT EXISTS `user_roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  FOREIGN KEY (`user_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Events Table
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `event_date` DATE NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `poster_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Registrations Table
CREATE TABLE IF NOT EXISTS `registrations` (
  `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Confirmed',
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Remember-Me Tokens Table
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `token_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `selector` CHAR(24) NOT NULL UNIQUE,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Dashboard Audit Logs Table
CREATE TABLE IF NOT EXISTS `dashboard_audit_log` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `action` VARCHAR(150) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- Seed Initial Accounts and Academic Data
-- ========================================================

-- Password for both accounts is: Password@123
-- Hash generated via password_hash('Password@123', PASSWORD_BCRYPT)
-- $2y$10$77nJ9H2.gXv.5Y5yH5f1Aea0aG/H0X7OqE/uJ81tqP6hD.Rk79v1G

INSERT INTO `students` (`student_id`, `full_name`, `username`, `email`, `mobile`, `password_hash`, `course`, `year_level`, `gender`, `role`)
VALUES
  (1, 'Malkesh Patel', 'malkesh089', 'malkesh.patel@studenthub.edu', '9876543210', '$2y$10$77nJ9H2.gXv.5Y5yH5f1Aea0aG/H0X7OqE/uJ81tqP6hD.Rk79v1G', 'B.Tech CSE', '2nd Year', 'Male', 'student'),
  (2, 'Administrator', 'admin', 'admin@studenthub.edu', '9123456789', '$2y$10$77nJ9H2.gXv.5Y5yH5f1Aea0aG/H0X7OqE/uJ81tqP6hD.Rk79v1G', 'Administration', 'Staff', 'Other', 'admin'),
  (3, 'Diya Patel', 'diya004', 'diya.patel@studenthub.edu', '9898012345', '$2y$10$77nJ9H2.gXv.5Y5yH5f1Aea0aG/H0X7OqE/uJ81tqP6hD.Rk79v1G', 'B.Tech CSE', '2nd Year', 'Female', 'student'),
  (4, 'Aarav Sharma', 'aarav001', 'aarav.sharma@studenthub.edu', '9825098765', '$2y$10$77nJ9H2.gXv.5Y5yH5f1Aea0aG/H0X7OqE/uJ81tqP6hD.Rk79v1G', 'B.Tech CSE', '2nd Year', 'Male', 'student')
ON DUPLICATE KEY UPDATE `student_id`=`student_id`;

INSERT INTO `user_roles` (`user_id`, `role`)
VALUES
  (1, 'student'),
  (2, 'admin'),
  (3, 'student'),
  (4, 'student')
ON DUPLICATE KEY UPDATE `user_id`=`user_id`;

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `location`, `category`)
VALUES
  (1, 'TechFest 2026 Hackathon', 'Annual 24-hour coding hackathon focusing on Web Frameworks and AI.', '2026-11-15', 'Main Auditorium', 'Technical'),
  (2, 'Spandan Cultural Gala', 'Annual student festival with music, dance, and theatrical plays.', '2026-12-02', 'Open Air Theater', 'Cultural'),
  (3, 'Docker & Kubernetes Bootcamp', 'Hands-on containerization and cloud orchestration workshop.', '2026-11-22', 'Lab 3 (FTE Block)', 'Workshop'),
  (4, 'Inter-College Cricket Tournament', 'Annual championship matches between engineering departments.', '2026-12-10', 'CHARUSAT Sports Complex', 'Sports')
ON DUPLICATE KEY UPDATE `event_id`=`event_id`;

INSERT INTO `registrations` (`student_id`, `event_id`, `status`)
VALUES
  (1, 1, 'Confirmed'),
  (1, 3, 'Confirmed'),
  (3, 1, 'Confirmed'),
  (4, 2, 'Pending')
ON DUPLICATE KEY UPDATE `student_id`=`student_id`;
