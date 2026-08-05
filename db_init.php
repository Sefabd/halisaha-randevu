<?php
// db_init.php - Database Schema Rebuilder with Features, Maintenance, Weekend Hours & Closed Range
$pdo = require __DIR__ . '/config/db.php';

try {
    // 1. Drop existing tables for fresh schema
    $pdo->exec("DROP TABLE IF EXISTS field_reservations");
    $pdo->exec("DROP TABLE IF EXISTS facility_fields");
    $pdo->exec("DROP TABLE IF EXISTS facilities");
    $pdo->exec("DROP TABLE IF EXISTS users");

    // 2. Users Table
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        phone TEXT NOT NULL,
        favorite_team TEXT DEFAULT 'neutral',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Facilities Table
    $pdo->exec("CREATE TABLE facilities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        owner_name TEXT NOT NULL,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        city TEXT NOT NULL,
        district TEXT NOT NULL,
        address TEXT NOT NULL,
        phone TEXT NOT NULL,
        open_time TEXT DEFAULT '13:00',
        close_time TEXT DEFAULT '01:00',
        open_time_weekend TEXT DEFAULT '09:00',
        close_time_weekend TEXT DEFAULT '03:00',
        closed_dates TEXT DEFAULT '[]',
        features TEXT DEFAULT '[\"HD Kamera Kaydı\", \"Ücretsiz Su & İkram\", \"Soyunma Odası & Duş\"]',
        favorite_team TEXT DEFAULT 'neutral',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Facility Fields Table (With closed_range)
    $pdo->exec("CREATE TABLE facility_fields (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        facility_id INTEGER NOT NULL,
        field_name TEXT NOT NULL,
        field_type TEXT NOT NULL,
        hourly_fee REAL DEFAULT 1200.00,
        status TEXT DEFAULT 'Aktif',
        features TEXT DEFAULT '[\"HD Kamera Kaydı\", \"Ücretsiz Su & İkram\", \"Soyunma Odası & Duş\"]',
        closed_range TEXT DEFAULT '{}',
        FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
    )");

    // 5. Field Reservations Table (With city, district, needs_player, notes)
    $pdo->exec("CREATE TABLE field_reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        facility_id INTEGER NOT NULL,
        field_id INTEGER NOT NULL,
        field_name TEXT NOT NULL,
        team_name TEXT NOT NULL,
        contact_name TEXT NOT NULL,
        phone TEXT NOT NULL,
        city TEXT DEFAULT 'İstanbul',
        district TEXT DEFAULT 'Kadıköy',
        reservation_date DATE NOT NULL,
        reservation_time VARCHAR(10) NOT NULL,
        fee REAL DEFAULT 1200.00,
        status TEXT DEFAULT 'Onaylandı',
        subscription_plan TEXT DEFAULT 'Standart',
        needs_player INTEGER DEFAULT 0,
        notes TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed Demo Users
    $defaultPass = password_hash('123', PASSWORD_DEFAULT);

    // Player 1
    $pdo->prepare("INSERT INTO users (full_name, username, password, phone, favorite_team) VALUES (?, ?, ?, ?, ?)")
        ->execute(['AHMET YILMAZ', 'oyuncu1', $defaultPass, '0532 111 22 33', 'galatasaray']);

    // Owner 1
    $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, open_time_weekend, close_time_weekend, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute(['Kadıköy Şampiyonlar Spor Kompleksi', 'MEHMET KAYA', 'kadikoy_arena', $defaultPass, 'İstanbul', 'Kadıköy', 'Caferağa Mah. Moda Cad. No:45 Kadıköy / İstanbul', '0532 555 12 34', '08:00', '03:00', '08:00', '03:00', 'fenerbahce']);
    $fac1_id = $pdo->lastInsertId();

    // Fields for Fac 1 (Futbol, Basketbol, Tenis)
    $insField = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee, status, features) VALUES (?, ?, ?, ?, ?, ?)");
    $defaultFeats = json_encode(["HD Kamera Kaydı", "Ücretsiz Su & İkram", "Soyunma Odası & Duş", "Krampon / Ayakkabı Kiralama"]);
    
    $insField->execute([$fac1_id, 'Saha 1', 'Kapalı Futbol Sahası', 1200.00, 'Aktif', $defaultFeats]);
    $insField->execute([$fac1_id, 'Saha 2', 'Açık Futbol Sahası', 1100.00, 'Aktif', $defaultFeats]);
    $insField->execute([$fac1_id, 'Basketbol Sahası', 'Kapalı Basketbol Sahası', 1300.00, 'Aktif', $defaultFeats]);
    $insField->execute([$fac1_id, 'Tenis Kortu 1', 'Açık Tenis Kortu', 1400.00, 'Aktif', $defaultFeats]);

    // Owner 2
    $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute(['Moda Park VIP Spor Tesisleri', 'CAN YILMAZ', 'moda_vip', $defaultPass, 'İstanbul', 'Kadıköy', 'Moda Sahil Yolu No:18 Kadıköy / İstanbul', '0533 444 55 66', '12:00', '02:00', 'besiktas']);
    $fac2_id = $pdo->lastInsertId();

    $insField->execute([$fac2_id, 'VIP Arena 1', 'Kapalı Futbol Sahası', 1400.00, 'Aktif', $defaultFeats]);
    $insField->execute([$fac2_id, 'VIP Arena 2', 'Açık Futbol Sahası', 1300.00, 'Aktif', $defaultFeats]);

    // Demo Reservations for Today
    $today = date('Y-m-d');
    $insRes = $pdo->prepare("INSERT INTO field_reservations (facility_id, field_id, field_name, team_name, contact_name, phone, city, district, reservation_date, reservation_time, fee, status, subscription_plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $insRes->execute([$fac1_id, 1, 'Saha 1', 'Moda Gençlik', 'KAPTAN ALİ', '05321112233', 'İstanbul', 'Kadıköy', $today, '11:00', 1200.00, 'Onaylandı', 'Standart']);
    $insRes->execute([$fac1_id, 1, 'Saha 1', 'Fenerbahçe Veteran', 'SERKAN HOCA', '05352223344', 'İstanbul', 'Kadıköy', $today, '12:00', 1200.00, 'Onaylandı', 'Aylık Fix']);
    $insRes->execute([$fac1_id, 2, 'Saha 2', 'Kadıköy Gücü', 'CANER ERTEKİN', '05334445566', 'İstanbul', 'Kadıköy', $today, '14:00', 1100.00, 'Onaylandı', 'Standart']);

    echo "<h2>⚽ SahaNet PRO Veritabanı & Gelişmiş Tesis Kurulumu</h2>";
    echo "<p>🎉 Spor Tesisleri, Özellikler ve Gelişmiş Çalışma Saatleri Başarıyla Yüklendi!</p>";
    echo "<p>✨ <a href='index.php'>Ana Sayfaya Git 👉</a></p>";

} catch (PDOException $e) {
    die("Veritabanı Kurulum Hatası: " . $e->getMessage());
}
