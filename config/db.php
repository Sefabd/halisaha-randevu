<?php
// config/db.php - SQLite PDO Database Connection
$dbPath = __DIR__ . '/../database_v2.sqlite';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
} catch (PDOException $e) {
    die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
}
