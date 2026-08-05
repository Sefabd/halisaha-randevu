<?php
// config/db.php - SahaNet PRO Database Connection with Auto Migration Check

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DB_NAME') ?: 'halisaha_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$pdo = null;
$db_type = 'sqlite';

try {
    // Try MySQL connection
    $dsn = "mysql:host={$db_host};charset=utf8mb4";
    $pdo_test = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3
    ]);
    
    $pdo_test->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo_test->exec("USE `{$db_name}`");
    
    $pdo = $pdo_test;
    $db_type = 'mysql';
} catch (PDOException $e) {
    // Fallback to SQLite
    try {
        $sqlite_file = __DIR__ . '/../database.sqlite';
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db_type = 'sqlite';
    } catch (PDOException $sqle) {
        die("Veritabanı bağlantı hatası: " . $sqle->getMessage());
    }
}

// Ensure all tables and columns exist on every request to prevent 1146 and 1054 SQL errors
try {
    $autoIncrement = ($db_type === 'mysql') ? "AUTO_INCREMENT" : "AUTOINCREMENT";

    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY {$autoIncrement},
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        favorite_team VARCHAR(30) DEFAULT 'galatasaray',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Facilities
    $pdo->exec("CREATE TABLE IF NOT EXISTS facilities (
        id INTEGER PRIMARY KEY {$autoIncrement},
        name VARCHAR(150) NOT NULL,
        owner_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        city VARCHAR(50) NOT NULL,
        district VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(30) NOT NULL,
        open_time VARCHAR(10) NOT NULL DEFAULT '13:00',
        close_time VARCHAR(10) NOT NULL DEFAULT '01:00',
        favorite_team VARCHAR(30) DEFAULT 'galatasaray',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 3. Facility Fields
    $pdo->exec("CREATE TABLE IF NOT EXISTS facility_fields (
        id INTEGER PRIMARY KEY {$autoIncrement},
        facility_id INTEGER NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_type VARCHAR(50) DEFAULT 'Kapalı Suni Çim',
        hourly_fee DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
        status VARCHAR(20) DEFAULT 'Aktif',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 4. Field Reservations
    $pdo->exec("CREATE TABLE IF NOT EXISTS field_reservations (
        id INTEGER PRIMARY KEY {$autoIncrement},
        facility_id INTEGER NOT NULL DEFAULT 1,
        field_id INTEGER NOT NULL DEFAULT 1,
        field_name VARCHAR(100) NOT NULL,
        team_name VARCHAR(100) NOT NULL,
        contact_name VARCHAR(100) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        city VARCHAR(50) DEFAULT 'İstanbul',
        district VARCHAR(50) DEFAULT 'Kadıköy',
        reservation_date DATE NOT NULL,
        reservation_time VARCHAR(10) NOT NULL,
        fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(20) NOT NULL DEFAULT 'Bekliyor',
        subscription_plan VARCHAR(50) DEFAULT 'Standart',
        needs_player TINYINT DEFAULT 0,
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // SQL MIGRATION FIX: Add facility_id and field_id if missing in old SQLite/MySQL table
    try { $pdo->exec("ALTER TABLE field_reservations ADD COLUMN facility_id INTEGER DEFAULT 1"); } catch (PDOException $ex) {}
    try { $pdo->exec("ALTER TABLE field_reservations ADD COLUMN field_id INTEGER DEFAULT 1"); } catch (PDOException $ex) {}

    // Check if facilities is empty; if so, seed default demo accounts
    $stmtFacCheck = $pdo->query("SELECT COUNT(*) as cnt FROM facilities");
    if ($stmtFacCheck->fetch()['cnt'] == 0) {
        $passHash = password_hash('123', PASSWORD_DEFAULT);

        // Demo Facility
        $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insFac->execute([
            'Kadıköy Şampiyonlar Spor Kompleksi',
            'Mehmet Kaya',
            'kadikoy_arena',
            $passHash,
            'İstanbul',
            'Kadıköy',
            'Caferağa Mah. Moda Cad. No:45 Kadıköy / İstanbul',
            '0532 555 12 34',
            '13:00',
            '01:00',
            'galatasaray'
        ]);

        // Demo Fields
        $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
        $insFld->execute([1, 'Saha 1', 'Kapalı Saha', 1200.00]);
        $insFld->execute([1, 'Saha 2', 'Açık Saha', 1100.00]);

        // Demo Player
        $insU = $pdo->prepare("INSERT INTO users (full_name, username, password, phone, favorite_team) VALUES (?, ?, ?, ?, ?)");
        $insU->execute(['Ahmet Yılmaz (Oyuncu)', 'oyuncu1', $passHash, '0532 555 12 34', 'galatasaray']);
    }

} catch (PDOException $sqle) {
    // Suppress schema check errors if tables already exist
}

return $pdo;
