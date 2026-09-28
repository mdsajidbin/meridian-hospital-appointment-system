-- ============================================================
-- Meridian Hospital Chattogram — Appointment Booking System
-- Database Schema (MySQL / InnoDB)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- users : shared login table for patients, admins, staff
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('patient','admin','staff') NOT NULL DEFAULT 'patient',
  `status` ENUM('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- admins : extra profile info for role=admin/staff users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `permission_level` ENUM('super_admin','admin','staff') NOT NULL DEFAULT 'admin',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- patients : extra profile info for role=patient users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `age` INT DEFAULT NULL,
  `gender` ENUM('Male','Female','Other') DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `medical_notes` TEXT DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- categories : main specialty + sub-specialty (self-referencing)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `icon` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- doctors
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `sub_category_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(255) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `signature_path` VARCHAR(255) DEFAULT NULL,
  `degree` TEXT DEFAULT NULL,
  `higher_degree` TEXT DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `bmdc_number` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `work_place` VARCHAR(255) DEFAULT NULL,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `specialist_in` VARCHAR(255) DEFAULT NULL,
  `experience_years` INT DEFAULT 0,
  `fee` DECIMAL(10,2) DEFAULT 0,
  `about` TEXT DEFAULT NULL,
  `priority` INT DEFAULT 0,
  `status` ENUM('approved','pending','hold','banned') NOT NULL DEFAULT 'pending',
  `editing_status` ENUM('locked','unlocked') NOT NULL DEFAULT 'unlocked',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`sub_category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- doctor_availability
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `doctor_availability`;
CREATE TABLE `doctor_availability` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT UNSIGNED NOT NULL,
  `day_of_week` ENUM('Sat','Sun','Mon','Tue','Wed','Thu','Fri') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `consultation_duration` INT NOT NULL DEFAULT 10,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- appointments
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `serial_number` INT DEFAULT NULL,
  `doctor_id` INT UNSIGNED NOT NULL,
  `patient_id` INT UNSIGNED NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` VARCHAR(20) NOT NULL,
  `status` ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `patient_problem` TEXT DEFAULT NULL,
  `patient_name` VARCHAR(150) DEFAULT NULL,
  `patient_age` INT DEFAULT NULL,
  `patient_gender` VARCHAR(20) DEFAULT NULL,
  `patient_address` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_slot` (`doctor_id`, `appointment_date`, `appointment_time`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Seed: default admin account
-- Email: Mohammadsajid1114@gmail.com   Password: Meridian@2026
-- (Change this password immediately after first login in production.)
-- ------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`, `status`) VALUES
('Sajid (Super Admin)', 'Mohammadsajid1114@gmail.com', '$2y$12$184ac/uu5VUWGRv330n7DORu1XJJeB.2xQbO0we3YKHLW5/HCMcJu', '01622295857', 'admin', 'active');

INSERT INTO `admins` (`user_id`, `permission_level`)
SELECT `id`, 'super_admin' FROM `users` WHERE `email` = 'Mohammadsajid1114@gmail.com';

-- ------------------------------------------------------------
-- Seed: top-level specialty categories (shown on homepage "Find by Specialty")
-- ------------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `parent_id`, `icon`) VALUES
(1, 'General Physician', NULL, 'General_physician.svg'),
(2, 'Gynecologist', NULL, 'Gynecologist.svg'),
(3, 'Dermatologist', NULL, 'Dermatologist.svg'),
(4, 'Pediatricians', NULL, 'Pediatricians.svg'),
(5, 'Neurologist', NULL, 'Neurologist.svg'),
(6, 'Gastroenterologist', NULL, 'Gastroenterologist.svg'),
(7, 'Specialist', NULL, 'General_physician.svg');
