<?php
// api/check_conflict.php - Real-time Conflict Validation Endpoint
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';

$field = trim($_GET['field'] ?? '');
$date = trim($_GET['date'] ?? '');
$time = trim($_GET['time'] ?? '');
$exclude_id = (int)($_GET['exclude_id'] ?? 0);

if (empty($field) || empty($date) || empty($time)) {
    echo json_encode(['status' => 'ok', 'has_conflict' => false]);
    exit;
}

try {
    $sql = "SELECT id, team_name, contact_name FROM field_reservations 
            WHERE field_name = ? AND reservation_date = ? AND reservation_time = ? AND status != 'İptal'";
    $params = [$field, $date, $time];

    if ($exclude_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode([
            'status' => 'conflict',
            'has_conflict' => true,
            'message' => "⚠️ UYARI: {$field} sahası için {$date} tarihinde ve saat {$time}'da '{$existing['team_name']}' adına dolu randevu mevcuttur!"
        ]);
    } else {
        echo json_encode([
            'status' => 'ok',
            'has_conflict' => false
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
