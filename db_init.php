<?php
// db_init.php - Veritabanı Otomatik Kurulum ve Türkçe Örnek Veri Yükleme Betiği

$pdo = require __DIR__ . '/config/db.php';

echo "<h2>⚽ SahaNet PRO Veritabanı Kurulumu</h2>";

try {
    // 1. Tabloyu Oluştur
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS field_reservations (
        id INTEGER PRIMARY KEY " . ($db_type === 'mysql' ? "AUTO_INCREMENT" : "AUTOINCREMENT") . ",
        team_name VARCHAR(100) NOT NULL,
        contact_name VARCHAR(100) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        city VARCHAR(50) DEFAULT 'İstanbul',
        district VARCHAR(50) DEFAULT 'Kadıköy',
        reservation_date DATE NOT NULL,
        reservation_time VARCHAR(10) NOT NULL,
        field_name VARCHAR(50) NOT NULL,
        fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(20) NOT NULL DEFAULT 'Bekliyor',
        subscription_plan VARCHAR(50) DEFAULT 'Standart',
        needs_player TINYINT DEFAULT 0,
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";

    $pdo->exec($createTableSQL);
    echo "<p>✅ <code>field_reservations</code> tablosu başarıyla kontrol edildi / oluşturuldu.</p>";

    // 2. Tablo Boşsa Örnek Türkçe Veriler Ekle
    $stmtCount = $pdo->query("SELECT COUNT(*) as cnt FROM field_reservations");
    $count = $stmtCount->fetch()['cnt'];

    if ($count == 0) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $sampleData = [
            [
                'team_name' => 'Kadıköy İdman Yurdu',
                'contact_name' => 'Ahmet Yılmaz',
                'phone' => '0532 555 12 34',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'reservation_date' => $today,
                'reservation_time' => '20:00',
                'field_name' => 'Saha 1',
                'fee' => 1200.00,
                'status' => 'Onaylandı',
                'subscription_plan' => 'Sezonluk Efsane',
                'needs_player' => 0,
                'notes' => 'VIP Saha Garantisi - Maç Kaydı İstiyor'
            ],
            [
                'team_name' => 'Karaköy Gücü',
                'contact_name' => 'Mehmet Kaya',
                'phone' => '0533 444 55 66',
                'city' => 'İstanbul',
                'district' => 'Beyoğlu',
                'reservation_date' => $today,
                'reservation_time' => '21:00',
                'field_name' => 'Saha 2',
                'fee' => 1100.00,
                'status' => 'Bekliyor',
                'subscription_plan' => 'Aylık Fix',
                'needs_player' => 1,
                'notes' => '1 Kaleci Aranıyor!'
            ],
            [
                'team_name' => 'Bosphorus FK',
                'contact_name' => 'Emre Can',
                'phone' => '0542 111 22 33',
                'city' => 'İstanbul',
                'district' => 'Beşiktaş',
                'reservation_date' => $today,
                'reservation_time' => '22:00',
                'field_name' => 'Saha 3',
                'fee' => 1000.00,
                'status' => 'Tamamlandı',
                'subscription_plan' => 'Kemik Kadro',
                'needs_player' => 0,
                'notes' => 'HD Maç kaydı yapıldı.'
            ],
            [
                'team_name' => 'Çankaya Gençlik',
                'contact_name' => 'Burak Demir',
                'phone' => '0505 777 88 99',
                'city' => 'Ankara',
                'district' => 'Çankaya',
                'reservation_date' => $tomorrow,
                'reservation_time' => '19:00',
                'field_name' => 'Saha 1',
                'fee' => 1200.00,
                'status' => 'Onaylandı',
                'subscription_plan' => 'Standart',
                'needs_player' => 1,
                'notes' => 'Stoper eksik'
            ],
            [
                'team_name' => 'Alsancak İdmanyurdu',
                'contact_name' => 'Oğuzhan Şahin',
                'phone' => '0536 999 00 11',
                'city' => 'İzmir',
                'district' => 'Konak',
                'reservation_date' => $yesterday,
                'reservation_time' => '20:00',
                'field_name' => 'Saha 2',
                'fee' => 1100.00,
                'status' => 'İptal',
                'subscription_plan' => 'Standart',
                'needs_player' => 0,
                'notes' => 'Hava muhalefeti iadesi'
            ]
        ];

        $insertSql = "INSERT INTO field_reservations 
            (team_name, contact_name, phone, city, district, reservation_date, reservation_time, field_name, fee, status, subscription_plan, needs_player, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($insertSql);
        foreach ($sampleData as $row) {
            $stmt->execute(array_values($row));
        }

        echo "<p>🎉 5 Adet Türkçe örnek randevu verisi eklendi!</p>";
    } else {
        echo "<p>ℹ️ Veritabanında hali hazırda {$count} kayıt mevcut.</p>";
    }

    echo "<p>✨ Kurulum Tamamlandı. <a href='index.php'>SahaNet PRO Ana Sayfasına Git 👉</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Kurulum hatası: " . $e->getMessage() . "</p>";
}
