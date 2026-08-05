<?php
// config/db.php - SahaNet PRO PDO Database Connection (Docker & Environment Ready)

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DB_NAME') ?: 'halisaha_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$pdo = null;
$db_type = 'sqlite'; // Default fallback

try {
    // Try connecting to MySQL container / host first
    $dsn = "mysql:host={$db_host};charset=utf8mb4";
    $pdo_test = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3
    ]);
    
    // Create database if not exists
    $pdo_test->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo_test->exec("USE `{$db_name}`");
    
    $pdo = $pdo_test;
    $db_type = 'mysql';
} catch (PDOException $e) {
    // Fallback to SQLite if MySQL is unavailable
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

return $pdo;
