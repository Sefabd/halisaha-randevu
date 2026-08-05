<?php
// db_init.php - 5 İl ve Tüm İlçeler İçin Örnek Tesis Yükleme Betiği

$pdo = require __DIR__ . '/config/db.php';

echo "<h2>⚽ SahaNet PRO Veritabanı & 5 İl Tesis Kurulumu</h2>";

try {
    $passHash = password_hash('123', PASSWORD_DEFAULT);

    // Delete existing sample facilities to refresh with 5 cities
    $pdo->exec("DELETE FROM facilities; DELETE FROM facility_fields; DELETE FROM field_reservations;");

    $facilities = [
        // İstanbul
        [
            'name' => 'Kadıköy Şampiyonlar Halı Saha Kompleksi',
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
            'name' => 'Beşiktaş VIP Spor Kompleksi',
            'owner_name' => 'Caner Erkin',
            'username' => 'besiktas_vip',
            'password' => $passHash,
            'city' => 'İstanbul',
            'district' => 'Beşiktaş',
            'address' => 'Abbasağa Mah. Ihlamur Cad. No:12 Beşiktaş / İstanbul',
            'phone' => '0533 444 55 66',
            'open_time' => '14:00',
            'close_time' => '02:00',
            'favorite_team' => 'besiktas'
        ],

        // Ankara
        [
            'name' => 'Çankaya Başkent Halı Saha Arena',
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
        ],

        // İzmir
        [
            'name' => 'Alsancak Ege Halı Saha Tesisleri',
            'owner_name' => 'Oğuzhan Şahin',
            'username' => 'alsancak_ege',
            'password' => $passHash,
            'city' => 'İzmir',
            'district' => 'Alsancak',
            'address' => 'Kıbrıs Şehitleri Cad. No:102 Alsancak / İzmir',
            'phone' => '0536 999 00 11',
            'open_time' => '13:00',
            'close_time' => '01:00',
            'favorite_team' => 'fenerbahce'
        ],

        // Bursa
        [
            'name' => 'Nilüfer Timsah Spor Kompleksi',
            'owner_name' => 'Emre Karaca',
            'username' => 'nilufer_timsah',
            'password' => $passHash,
            'city' => 'Bursa',
            'district' => 'Nilüfer',
            'address' => 'Fatih Sultan Mehmet Bulvarı No:15 Nilüfer / Bursa',
            'phone' => '0541 222 33 44',
            'open_time' => '10:00',
            'close_time' => '00:00',
            'favorite_team' => 'galatasaray'
        ],

        // Antalya
        [
            'name' => 'Muratpaşa Akdeniz Halı Saha Arena',
            'owner_name' => 'Serkan Aksoy',
            'username' => 'muratpasa_akdeniz',
            'password' => $passHash,
            'city' => 'Antalya',
            'district' => 'Muratpaşa',
            'address' => 'Lara Cad. No:50 Muratpaşa / Antalya',
            'phone' => '0507 888 99 00',
            'open_time' => '14:00',
            'close_time' => '02:00',
            'favorite_team' => 'trabzonspor'
        ]
    ];

    $insFac = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($facilities as $f) {
        $insFac->execute(array_values($f));
    }

    // Sahaları Ekle
    $insFld = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
    for ($i = 1; $i <= count($facilities); $i++) {
        $insFld->execute([$i, 'Saha 1 (Kapalı Suni Çim)', 'Kapalı Suni Çim', 1200.00]);
        $insFld->execute([$i, 'Saha 2 (Açık Hibrit)', 'Açık Hibrit', 1100.00]);
    }

    echo "<p>🎉 5 Farklı Şehirde Örnek Tesisler ve Sahalar Başarıyla Yüklendi!</p>";
    echo "<p>✨ <a href='index.php'>Ana Sayfaya Git 👉</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Kurulum hatası: " . $e->getMessage() . "</p>";
}
