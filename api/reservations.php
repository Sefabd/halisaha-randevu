<?php
// api/reservations.php - CRUD Operations with PDO Prepared Statements
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $search = trim($_GET['search'] ?? '');
        $field = trim($_GET['field'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');

        $sql = "SELECT * FROM field_reservations WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (team_name LIKE ? OR contact_name LIKE ? OR phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($field !== '' && $field !== 'Tüm Sahalar') {
            $sql .= " AND field_name = ?";
            $params[] = $field;
        }

        if ($status !== '' && $status !== 'Tümü') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if ($city !== '' && $city !== 'Tüm İller') {
            $sql .= " AND city = ?";
            $params[] = $city;
        }

        if ($district !== '' && $district !== 'Tüm İlçeler') {
            $sql .= " AND district = ?";
            $params[] = $district;
        }

        $sql .= " ORDER BY reservation_date DESC, reservation_time ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($action === 'get_one') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM field_reservations WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kayıt bulunamadı.']);
        }
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $team_name = trim($_POST['team_name'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? 'Kadıköy');
        $reservation_date = trim($_POST['reservation_date'] ?? '');
        $reservation_time = trim($_POST['reservation_time'] ?? '');
        $field_name = trim($_POST['field_name'] ?? '');
        $fee = (float)($_POST['fee'] ?? 0);
        $status = trim($_POST['status'] ?? 'Bekliyor');
        $subscription_plan = trim($_POST['subscription_plan'] ?? 'Standart');
        $needs_player = isset($_POST['needs_player']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        // Basic Validation
        if (empty($team_name) || empty($contact_name) || empty($phone) || empty($reservation_date) || empty($reservation_time) || empty($field_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurunuz.']);
            exit;
        }

        // Check Conflict (Same Field + Date + Time, excluding current ID if updating, and excluding Cancelled)
        $conflictSql = "SELECT id, team_name FROM field_reservations 
                        WHERE field_name = ? AND reservation_date = ? AND reservation_time = ? AND status != 'İptal'";
        $conflictParams = [$field_name, $reservation_date, $reservation_time];

        if ($id > 0) {
            $conflictSql .= " AND id != ?";
            $conflictParams[] = $id;
        }

        $stmtConf = $pdo->prepare($conflictSql);
        $stmtConf->execute($conflictParams);
        $conflict = $stmtConf->fetch();

        if ($conflict) {
            echo json_encode([
                'status' => 'error', 
                'message' => "❌ ÇAKIŞMA UYARISI: {$field_name} için {$reservation_date} tarihinde ve {$reservation_time} saatinde '{$conflict['team_name']}' adına onaylı/aktif bir randevu zaten mevcut!"
            ]);
            exit;
        }

        if ($id > 0) {
            // Update
            $updateSql = "UPDATE field_reservations SET 
                team_name = ?, contact_name = ?, phone = ?, city = ?, district = ?, 
                reservation_date = ?, reservation_time = ?, field_name = ?, fee = ?, 
                status = ?, subscription_plan = ?, needs_player = ?, notes = ?
                WHERE id = ?";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                $team_name, $contact_name, $phone, $city, $district, 
                $reservation_date, $reservation_time, $field_name, $fee, 
                $status, $subscription_plan, $needs_player, $notes, $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Randevu başarıyla güncellendi.']);
        } else {
            // Insert
            $insertSql = "INSERT INTO field_reservations 
                (team_name, contact_name, phone, city, district, reservation_date, reservation_time, field_name, fee, status, subscription_plan, needs_player, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                $team_name, $contact_name, $phone, $city, $district, 
                $reservation_date, $reservation_time, $field_name, $fee, 
                $status, $subscription_plan, $needs_player, $notes
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Yeni randevu başarıyla oluşturuldu.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM field_reservations WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Randevu başarıyla silindi.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
