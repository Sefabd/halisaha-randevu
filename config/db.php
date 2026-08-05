<?php
// config/db.php - SahaNet PRO Database Connection with 5 Kadıköy Facilities Auto-Seeding

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
        field_type VARCHAR(50) DEFAULT 'Kapalı Saha',
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

    try { $pdo->exec("ALTER TABLE field_reservations ADD COLUMN facility_id INTEGER DEFAULT 1"); } catch (PDOException $ex) {}
    try { $pdo->exec("ALTER TABLE field_reservations ADD COLUMN field_id INTEGER DEFAULT 1"); } catch (PDOException $ex) {}

    // Check facility count; if less than 5, seed all 5 Kadıköy facilities and fields
    $stmtFacCheck = $pdo->query("SELECT COUNT(*) as cnt FROM facilities");
    if ($stmtFacCheck->fetch()['cnt'] < 5) {
        $passHash = password_hash('123', PASSWORD_DEFAULT);
        $pdo->exec("DELETE FROM facilities; DELETE FROM facility_fields;");

        $facilities = [
            [
                'name' => 'Kadıköy Şampiyonlar Spor Kompleksi',
                'owner_name' => 'Mehmet Kaya',
                'username' => 'kadikoy_arena',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Caferağa Mah. Moda Cad. No:45 Kadıköy / İstanbul',
                'phone' => '0532 555 12 34',
                'open_time' => '13:00',
                'close_time' => '01:00',
                'favorite_team' => 'galatasaray'
            ],
            [
                'name' => 'Moda Park VIP Spor Tesisleri',
                'owner_name' => 'Caner Erkin',
                'username' => 'moda_park',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Moda Sahil Yolu No:18 Kadıköy / İstanbul',
                'phone' => '0533 444 55 66',
                'open_time' => '12:00',
                'close_time' => '02:00',
                'favorite_team' => 'fenerbahce'
            ],
            [
                'name' => 'Fenerbahçe Kalamış Spor Tesisleri',
                'owner_name' => 'Ali Koç',
                'username' => 'kalamis_spor',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Kalamış Marina Yanı No:5 Kadıköy / İstanbul',
                'phone' => '0535 111 22 33',
                'open_time' => '10:00',
                'close_time' => '00:00',
                'favorite_team' => 'fenerbahce'
            ],
            [
                'name' => 'Suadiye Sahil Spor Tesisleri',
                'owner_name' => 'Oğuzhan Şahin',
                'username' => 'suadiye_sahil',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Suadiye Plaj Yolu No:30 Kadıköy / İstanbul',
                'phone' => '0536 999 00 11',
                'open_time' => '14:00',
                'close_time' => '02:00',
                'favorite_team' => 'besiktas'
            ],
            [
                'name' => 'Göztepe Park Spor Kompleksi',
                'owner_name' => 'Serkan Aksoy',
                'username' => 'goztepe_park',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Bağdat Cad. Göztepe Parkı Yanı Kadıköy / İstanbul',
                'phone' => '0507 888 99 00',
                'open_time' => '13:00',
                'close_time' => '01:00',
                'favorite_team' => 'galatasaray'
            ],
            [
                'name' => 'Beşiktaş VIP Spor Kompleksi',
                'owner_name' => 'Ahmet Nur',
                'username' => 'besiktas_arena',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Beşiktaş',
                'address' => 'Abbasağa Mah. Ihlamur Cad. No:12 Beşiktaş / İstanbul',
                'phone' => '0541 222 33 44',
                'open_time' => '14:00',
                'close_time' => '02:00',
                'favorite_team' => 'besiktas'
            ],
            [
                'name' => 'Çankaya Başkent Spor Arena',
                'owner_name' => 'Burak Demir',
                'username' => 'cankaya_baskent',
                'password' => $passHash,
                'city' => 'Ankara',
                'district' => 'Çankaya',
                'address' => 'Tunalı Hilmi Cad. No:88 Çankaya / Ankara',
                'phone' => '0505 777 88 99',
                'open_time' => '12:00',
                'close_time' => '00:00',
                'favorite_team' => 'galatasaray'
            ]
        ];

        $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($facilities as $f) {
            $insFac->execute(array_values($f));
        }

        $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
        for ($i = 1; $i <= count($facilities); $i++) {
            $insFld->execute([$i, 'Saha 1', 'Kapalı Saha', 1200.00]);
            $insFld->execute([$i, 'Saha 2', 'Açık Saha', 1100.00]);
        }
    }

} catch (PDOException $sqle) {
    // Suppress schema check errors
}

return $pdo;
