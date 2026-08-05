<?php
// api/stats.php - Real-time Dashboard Analytics & Hourly Matrix API
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';

try {
    $today = date('Y-m-d');

    // 1. Total Reservations
    $stmtTotal = $pdo->query("SELECT COUNT(*) as cnt FROM field_reservations");
    $totalCount = $stmtTotal->fetch()['cnt'];

    // 2. Today's Reservations
    $stmtToday = $pdo->prepare("SELECT COUNT(*) as cnt FROM field_reservations WHERE reservation_date = ?");
    $stmtToday->execute([$today]);
    $todayCount = $stmtToday->fetch()['cnt'];

    // 3. Approved Reservations
    $stmtApproved = $pdo->query("SELECT COUNT(*) as cnt FROM field_reservations WHERE status = 'Onaylandı'");
    $approvedCount = $stmtApproved->fetch()['cnt'];

    // 4. Daily Income (Today's Approved or Completed Fee Sum)
    $stmtIncome = $pdo->prepare("SELECT SUM(fee) as income FROM field_reservations 
                                WHERE reservation_date = ? AND status IN ('Onaylandı', 'Tamamlandı')");
    $stmtIncome->execute([$today]);
    $dailyIncome = (float)($stmtIncome->fetch()['income'] ?? 0);

    // 5. Hourly Matrix Data for Today
    $stmtMatrix = $pdo->prepare("SELECT field_name, reservation_time, team_name, status 
                                FROM field_reservations 
                                WHERE reservation_date = ? AND status != 'İptal'");
    $stmtMatrix->execute([$today]);
    $matrixRows = $stmtMatrix->fetchAll();

    $matrixMap = [];
    foreach ($matrixRows as $r) {
        $matrixMap[$r['field_name']][$r['reservation_time']] = $r['team_name'];
    }

    echo json_encode([
        'status' => 'success',
        'metrics' => [
            'total' => $totalCount,
            'today' => $todayCount,
            'approved' => $approvedCount,
            'daily_income' => number_format($dailyIncome, 2, ',', '.') . ' TL'
        ],
        'matrix' => $matrixMap,
        'today_date' => date('d.m.Y')
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
