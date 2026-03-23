-- database
-- Use to rebuild database completely from scratch, drop db or relevant table/s before use
-- Replace `your_database_name` below with the same value as DB_NAME in your .env file.
--
-- Constraint handling notes:
-- 1) Keep foreign key checks ON for normal setup so bad data fails fast.
-- 2) If you need to truncate/reseed related tables, temporarily disable checks:
--      SET FOREIGN_KEY_CHECKS = 0;
--      -- truncate/delete in dependency order
--      SET FOREIGN_KEY_CHECKS = 1;
-- 3) Insert parent tables before child tables (users -> bookings).
-- 4) This schema uses ON DELETE SET NULL on bookings.user_id, so deleting a user
--    keeps booking rows but clears user_id.

-- create
CREATE DATABASE IF NOT EXISTS `your_database_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `your_database_name`;

-- users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `adults` int(11) NOT NULL DEFAULT 0,
  `children` int(11) NOT NULL DEFAULT 0,
  `family_tickets` int(11) NOT NULL DEFAULT 0,
  `special_requirements` text,
  `total_cost` decimal(10,2) NOT NULL,
  `original_cost` decimal(10,2) NOT NULL,
  `discount_applied` decimal(5,2) DEFAULT 0,
  `loyalty_points_earned` int(11) DEFAULT 0,
  `booking_reference` varchar(20) NOT NULL UNIQUE,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_visit_date` (`visit_date`),
  INDEX `idx_booking_reference` (`booking_reference`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- contact
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20),
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- stats
CREATE VIEW `booking_stats` AS
SELECT 
    DATE(visit_date) as booking_date,
    COUNT(*) as total_bookings,
    SUM(adults + children) as total_visitors,
    SUM(total_cost) as total_revenue,
    AVG(total_cost) as avg_booking_value
FROM bookings 
WHERE status = 'confirmed'
GROUP BY DATE(visit_date)
ORDER BY booking_date DESC;

-- test
-- Parent records first to satisfy the bookings.user_id foreign key.
INSERT INTO `users` (`username`, `email`, `password`, `loyalty_points`, `total_points_earned`) VALUES 
('testuser', 'test@example.com', 'password123', 8, 8),
('johnsmith', 'john@example.com', 'password123', 12, 22);

-- sample
-- Child records next (bookings references users.id).
INSERT INTO `bookings` (`user_id`, `visitor_name`, `email`, `phone`, `visit_date`, `visit_time`, `adults`, `children`, `family_tickets`, `total_cost`, `original_cost`, `discount_applied`, `loyalty_points_earned`, `booking_reference`, `status`) VALUES
(1, 'Test User', 'test@example.com', '01234 567890', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', 2, 1, 0, 42.00, 42.00, 0, 3, 'RZA25ABC123', 'confirmed'),
(2, 'John Smith', 'john@example.com', '01234 567891', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '11:00:00', 0, 0, 1, 40.50, 45.00, 10, 5, 'RZA25DEF456', 'confirmed');

-- messages
INSERT INTO `contact_messages` (`first_name`, `last_name`, `email`, `subject`, `message`) VALUES
('Sarah', 'Johnson', 'sarah@example.com', 'Accessibility Question', 'Do you have wheelchair accessible paths?'),
('Mike', 'Brown', 'mike@example.com', 'Group Booking', 'I would like to book for a group of 20 people.');