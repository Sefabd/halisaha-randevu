-- SahaNet PRO Veritabanı Şeması
-- YEBSOFT Uygulama Sınavı: field_reservations Tablosu

CREATE DATABASE IF NOT EXISTS `halisaha_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `halisaha_db`;

CREATE TABLE IF NOT EXISTS `field_reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `team_name` VARCHAR(100) NOT NULL,
    `contact_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `city` VARCHAR(50) DEFAULT 'İstanbul',
    `district` VARCHAR(50) DEFAULT 'Kadıköy',
    `reservation_date` DATE NOT NULL,
    `reservation_time` VARCHAR(10) NOT NULL,
    `field_name` VARCHAR(50) NOT NULL,
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Bekliyor',
    `subscription_plan` VARCHAR(50) DEFAULT 'Standart',
    `needs_player` TINYINT(1) DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
