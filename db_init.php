<?php
// db_init.php - Veritabanı Otomatik Kurulum ve Tesis / Saha Örnek Veri Yükleyici

$pdo = require __DIR__ . '/config/db.php';

echo "<h2>⚽ SahaNet PRO Veritabanı & Tesis Kurulumu</h2>";

try {
    // 1. Tesisler Tablosu
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
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Sahalar Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS facility_fields (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
        facility_id INTEGER NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_type VARCHAR(50) DEFAULT 'Kapalı Suni Çim',
        hourly_fee DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
        status VARCHAR(20) DEFAULT 'Aktif',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 3. Randevular Tablosu
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

    echo "<p>✅ Tablolar kontrol edildi.</p>";

    // Tesis Kontrolü
    $stmtCount = $pdo->query("SELECT COUNT(*) as cnt FROM facilities");
    $count = $stmtCount->fetch()['cnt'];

    if ($count == 0) {
        // Sample Password: '123'
        $passHash = password_hash('123', PASSWORD_DEFAULT);

        // 1. Tesisler
        $facilities = [
            [
                'name' => 'Kadıköy Şampiyonlar Halı Saha Kompleksi',
                'owner_name' => 'Ahmet Yılmaz',
                'username' => 'kadikoy_arena',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Caferağa Mah. Moda Cad. No:45 Kadıköy / İstanbul',
                'phone' => '0532 555 12 34',
                'open_time' => '13:00',
                'close_time' => '01:00'
            ],
            [
                'name' => 'Beşiktaş VIP Arena Kompleksi',
                'owner_name' => 'Mehmet Kaya',
                'username' => 'besiktas_vip',
                'password' => $passHash,
                'city' => 'İstanbul',
                'district' => 'Beşiktaş',
                'address' => 'Abbasağa Mah. Ihlamur Cad. No:12 Beşiktaş / İstanbul',
                'phone' => '0533 444 55 66',
                'open_time' => '14:00',
                'close_time' => '02:00'
            ],
            [
                'name' => 'Çankaya Başkent Halı Saha',
                'owner_name' => 'Burak Demir',
                'username' => 'cankaya_baskent',
                'password' => $passHash,
                'city' => 'Ankara',
                'district' => 'Çankaya',
                'address' => 'Tunalı Hilmi Cad. No:88 Çankaya / Ankara',
                'phone' => '0505 777 88 99',
                'open_time' => '12:00',
                'close_time' => '00:00'
            ]
        ];

        $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($facilities as $f) {
            $insFac->execute(array_values($f));
        }

        // 2. Sahalar (Tesis 1 için 3 saha, Tesis 2 için 2 saha)
        $fields = [
            // Kadıköy Arena Sahaları (Facility ID 1)
            ['facility_id' => 1, 'field_name' => 'Saha 1 (Kapalı UEFA Çim)', 'field_type' => 'Kapalı Suni Çim', 'hourly_fee' => 1200.00],
            ['facility_id' => 1, 'field_name' => 'Saha 2 (Açık Hibrit Çim)', 'field_type' => 'Açık Hibrit', 'hourly_fee' => 1100.00],
            ['facility_id' => 1, 'field_name' => 'Saha 3 (VIP Pro Kamera Kayıtlı)', 'field_type' => 'VIP Kapalı Çim', 'hourly_fee' => 1400.00],

            // Beşiktaş VIP Sahaları (Facility ID 2)
            ['facility_id' => 2, 'field_name' => 'Saha 1 (Kara Kartal Çim)', 'field_type' => 'Kapalı Çim', 'hourly_fee' => 1300.00],
            ['facility_id' => 2, 'field_name' => 'Saha 2 (Bosphorus Açık)', 'field_type' => 'Açık Çim', 'hourly_fee' => 1200.00],

            // Çankaya Sahaları (Facility ID 3)
            ['facility_id' => 3, 'field_name' => 'Saha 1 (Başkent Çim)', 'field_type' => 'Kapalı Çim', 'hourly_fee' => 1000.00],
        ];

        $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
        foreach ($fields as $fld) {
            $insFld->execute(array_values($fld));
        }

        // 3. Örnek Randevular
        $today = date('Y-m-d');
        $sampleRes = [
            [
                'facility_id' => 1,
                'field_id' => 1,
                'field_name' => 'Saha 1 (Kapalı UEFA Çim)',
                'team_name' => 'Kadıköy İdman Yurdu',
                'contact_name' => 'Ahmet Yılmaz',
                'phone' => '0532 555 12 34',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'reservation_date' => $today,
                'reservation_time' => '20:00',
                'fee' => 1200.00,
                'status' => 'Onaylandı',
                'subscription_plan' => 'Sezonluk Efsane',
                'needs_player' => 0,
                'notes' => 'VIP Saha Garantisi'
            ],
            [
                'facility_id' => 1,
                'field_id' => 2,
                'field_name' => 'Saha 2 (Açık Hibrit Çim)',
                'team_name' => 'Karaköy Gücü',
                'contact_name' => 'Mehmet Kaya',
                'phone' => '0533 444 55 66',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'reservation_date' => $today,
                'reservation_time' => '21:00',
                'fee' => 1100.00,
                'status' => 'Bekliyor',
                'subscription_plan' => 'Aylık Fix',
                'needs_player' => 1,
                'notes' => '1 Kaleci Aranıyor'
            ]
        ];

        $insRes = $pdo->prepare("INSERT INTO field_reservations 
            (facility_id, field_id, field_name, team_name, contact_name, phone, city, district, reservation_date, reservation_time, fee, status, subscription_plan, needs_player, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleRes as $sr) {
            $insRes->execute(array_values($sr));
        }

        echo "<p>🎉 Tesisler, Sahalar ve Örnek Randevu verileri başarıyla oluşturuldu!</p>";
        echo "<p>🔑 <strong>Varsayılan İşletmeci Giriş Bilgisi:</strong> Kullanıcı Adı: <code>kadikoy_arena</code> | Şifre: <code>123</code></p>";
    } else {
        echo "<p>ℹ️ Veritabanında hali hazırda {$count} adet tesis mevcut.</p>";
    }

    echo "<p>✨ <a href='login.php'>Giriş Ekranına Git 👉</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Kurulum hatası: " . $e->getMessage() . "</p>";
}
