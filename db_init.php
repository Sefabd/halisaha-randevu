<?php
// db_init.php - Veritabanı Tabloları ve Örnek Veri Yükleyici

$pdo = require __DIR__ . '/config/db.php';

echo "<h2>⚽ SahaNet PRO Veritabanı Kurulumu</h2>";

try {
    // 1. Users Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        favorite_team VARCHAR(30) DEFAULT 'galatasaray',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Facilities Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS facilities (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
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

    // 3. Facility Fields Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS facility_fields (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
        facility_id INTEGER NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_type VARCHAR(50) DEFAULT 'Kapalı Suni Çim',
        hourly_fee DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
        status VARCHAR(20) DEFAULT 'Aktif',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 4. Field Reservations Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS field_reservations (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
        facility_id INTEGER NOT NULL,
        field_id INTEGER NOT NULL,
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

    echo "<p>✅ Tüm veritabanı tabloları kontrol edildi.</p>";

    // Sample Player User
    $stmtUser = $pdo->query("SELECT COUNT(*) as cnt FROM users");
    if ($stmtUser->fetch()['cnt'] == 0) {
        $passHash = password_hash('123', PASSWORD_DEFAULT);
        $insU = $pdo->prepare("INSERT INTO users (full_name, username, password, phone, favorite_team) VALUES (?, ?, ?, ?, ?)");
        $insU->execute(['Ahmet Yılmaz (Oyuncu)', 'oyuncu1', $passHash, '0532 555 12 34', 'galatasaray']);
        echo "<p>👤 Örnek Oyuncu Hesabı Eklendi: Kullanıcı Adı: <code>oyuncu1</code> | Şifre: <code>123</code></p>";
    }

    // Sample Facility Owner
    $stmtFac = $pdo->query("SELECT COUNT(*) as cnt FROM facilities");
    if ($stmtFac->fetch()['cnt'] == 0) {
        $passHash = password_hash('123', PASSWORD_DEFAULT);
        $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insFac->execute([
            'Kadıköy Şampiyonlar Halı Saha Kompleksi',
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

        // Sahalar
        $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
        $insFld->execute([1, 'Saha 1 (Kapalı UEFA Çim)', 'Kapalı Suni Çim', 1200.00]);
        $insFld->execute([1, 'Saha 2 (Açık Hibrit Çim)', 'Açık Hibrit', 1100.00]);
        $insFld->execute([1, 'Saha 3 (VIP Pro Kamera Kayıtlı)', 'VIP Kapalı Çim', 1400.00]);

        echo "<p>🏟️ Örnek İşletmeci Hesabı Eklendi: Kullanıcı Adı: <code>kadikoy_arena</code> | Şifre: <code>123</code></p>";
    }

    echo "<p>✨ Kurulum Tamamlandı. <a href='login.php'>Giriş Ekranına Git 👉</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Kurulum hatası: " . $e->getMessage() . "</p>";
}
