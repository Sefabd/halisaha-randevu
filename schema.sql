-- SahaNet PRO FIFA/Stadyum Konseptli İlişkisel Veritabanı Şeması

CREATE DATABASE IF NOT EXISTS `halisaha_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `halisaha_db`;

-- 1. Halı Saha Tesisleri (İşletmeciler)
CREATE TABLE IF NOT EXISTS `facilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `owner_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `district` VARCHAR(50) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `open_time` VARCHAR(10) NOT NULL DEFAULT '13:00',
    `close_time` VARCHAR(10) NOT NULL DEFAULT '01:00',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tesis Alt Sahaları
CREATE TABLE IF NOT EXISTS `facility_fields` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `facility_id` INT NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `field_type` VARCHAR(50) DEFAULT 'Kapalı Suni Çim',
    `hourly_fee` DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
    `status` VARCHAR(20) DEFAULT 'Aktif',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Randevular
CREATE TABLE IF NOT EXISTS `field_reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `facility_id` INT NOT NULL,
    `field_id` INT NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `team_name` VARCHAR(100) NOT NULL,
    `contact_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `city` VARCHAR(50) DEFAULT 'İstanbul',
    `district` VARCHAR(50) DEFAULT 'Kadıköy',
    `reservation_date` DATE NOT NULL,
    `reservation_time` VARCHAR(10) NOT NULL,
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Bekliyor',
    `subscription_plan` VARCHAR(50) DEFAULT 'Standart',
    `needs_player` TINYINT DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`field_id`) REFERENCES `facility_fields`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
