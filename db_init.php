<?php
// db_init.php - Tesisler, Futbol, Basketbol ve Tenis Sahaları Kurulumu

$pdo = require __DIR__ . '/config/db.php';

echo "<h2>⚽ SahaNet PRO Veritabanı & Spor Tesisleri Kurulumu</h2>";

try {
    $passHash = password_hash('123', PASSWORD_DEFAULT);

    $pdo->exec("DELETE FROM facilities; DELETE FROM facility_fields; DELETE FROM field_reservations;");

    $facilities = [
        // Kadıköy Tesisleri
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

        // Beşiktaş
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
        ]
    ];

    $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($facilities as $f) {
        $insFac->execute(array_values($f));
    }

    // Sahaları Ekle (Futbol, Basketbol ve Tenis Ayrımı)
    $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
    for ($i = 1; $i <= count($facilities); $i++) {
        $insFld->execute([$i, 'Futbol Sahası 1', 'Kapalı Futbol Sahası', 1200.00]);
        $insFld->execute([$i, 'Futbol Sahası 2', 'Açık Futbol Sahası', 1100.00]);
        $insFld->execute([$i, 'Basketbol Sahası A', 'Kapalı Basketbol Sahası', 950.00]);
        $insFld->execute([$i, 'Tenis Kortu 1', 'Açık Tenis Kortu', 850.00]);
    }

    echo "<p>🎉 Spor Tesisleri (Futbol, Basketbol, Tenis) Başarıyla Yüklendi!</p>";
    echo "<p>✨ <a href='index.php'>Ana Sayfaya Git 👉</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Kurulum hatası: " . $e->getMessage() . "</p>";
}
