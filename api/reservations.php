<?php
// api/reservations.php - Multi-Tenant Reservation CRUD & Subscription Credit Deduction
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';
$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $search = trim($_GET['search'] ?? '');
        $field_id = (int)($_GET['field_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');
        $facility_id = (int)($_GET['facility_id'] ?? 0);

        // If owner is logged in, force their facility_id
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') {
            $facility_id = (int)$_SESSION['facility_id'];
        }

        $sql = "SELECT r.*, f.name as facility_name 
                FROM field_reservations r 
                LEFT JOIN facilities f ON r.facility_id = f.id 
                WHERE 1=1";
        $params = [];

        if ($facility_id > 0) {
            $sql .= " AND r.facility_id = ?";
            $params[] = $facility_id;
        }

        if ($search !== '') {
            $sql .= " AND (r.team_name LIKE ? OR r.contact_name LIKE ? OR r.phone LIKE ? OR r.username LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($field_id > 0) {
            $sql .= " AND r.field_id = ?";
            $params[] = $field_id;
        }

        if ($status !== '' && $status !== 'Tümü') {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        if ($city !== '' && $city !== 'Tüm İller') {
            $sql .= " AND r.city = ?";
            $params[] = $city;
        }

        if ($district !== '' && $district !== 'Tüm İlçeler') {
            $sql .= " AND r.district = ?";
            $params[] = $district;
        }

        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time ASC";

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
        $facility_id = (int)($_POST['facility_id'] ?? 0);
        $field_id = (int)($_POST['field_id'] ?? 0);
        $field_name = trim($_POST['field_name'] ?? '');
        $team_name = trim($_POST['team_name'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? 'Kadıköy');
        $reservation_date = trim($_POST['reservation_date'] ?? '');
        $reservation_time = trim($_POST['reservation_time'] ?? '');
        $fee = (float)($_POST['fee'] ?? 0);
        $status = trim($_POST['status'] ?? 'Onaylandı');
        $subscription_plan = trim($_POST['subscription_plan'] ?? 'Standart');
        $use_subscription_id = (int)($_POST['use_subscription_id'] ?? 0);
        $needs_player = isset($_POST['needs_player']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        // Deduct subscription credit if selected
        if ($use_subscription_id > 0) {
            $subStmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE id = ? AND remaining_matches > 0 AND status = 'Aktif'");
            $subStmt->execute([$use_subscription_id]);
            $sub = $subStmt->fetch();

            if ($sub) {
                $rem = $sub['remaining_matches'] - 1;
                $used = $sub['used_matches'] + 1;
                $subStatus = ($rem <= 0) ? 'Tamamlandı' : 'Aktif';

                $upSub = $pdo->prepare("UPDATE user_subscriptions SET remaining_matches = ?, used_matches = ?, status = ? WHERE id = ?");
                $upSub->execute([$rem, $used, $subStatus, $use_subscription_id]);

                $fee = 0.00;
                $subscription_plan = $sub['package_name'];
            }
        }

        // Owner override
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') {
            $facility_id = (int)$_SESSION['facility_id'];
        }

        // Basic Validation
        if ($facility_id <= 0 || empty($team_name) || empty($contact_name) || empty($phone) || empty($reservation_date) || empty($reservation_time)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tesis, tarih, saat ve zorunlu alanları doldurunuz.']);
            exit;
        }

        // Fetch field name if missing
        if ($field_id > 0 && empty($field_name)) {
            $fStmt = $pdo->prepare("SELECT field_name FROM facility_fields WHERE id = ?");
            $fStmt->execute([$field_id]);
            $fRow = $fStmt->fetch();
            if ($fRow) $field_name = $fRow['field_name'];
        }

        // Check Conflict for same Facility + Field + Date + Time
        $conflictSql = "SELECT id, team_name FROM field_reservations 
                        WHERE facility_id = ? AND (field_id = ? OR field_name = ?) 
                        AND reservation_date = ? AND reservation_time = ? AND status != 'İptal'";
        $conflictParams = [$facility_id, $field_id, $field_name, $reservation_date, $reservation_time];

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
                'message' => "❌ ÇAKIŞMA UYARISI: {$field_name} sahasında {$reservation_date} tarihinde ve {$reservation_time} saatinde '{$conflict['team_name']}' adına onaylı randevu mevcuttur!"
            ]);
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        if (empty($username) && isset($_SESSION['user_name'])) {
            $username = $_SESSION['user_name'];
        }

        if ($id > 0) {
            // Update
            $updateSql = "UPDATE field_reservations SET 
                facility_id = ?, field_id = ?, field_name = ?, team_name = ?, username = ?, contact_name = ?, phone = ?, city = ?, district = ?, 
                reservation_date = ?, reservation_time = ?, fee = ?, status = ?, subscription_plan = ?, subscription_id = ?, needs_player = ?, notes = ?
                WHERE id = ?";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                $facility_id, $field_id, $field_name, $team_name, $username, $contact_name, $phone, $city, $district, 
                $reservation_date, $reservation_time, $fee, $status, $subscription_plan, $use_subscription_id, $needs_player, $notes, $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Randevu başarıyla güncellendi.']);
        } else {
            // Insert
            $insertSql = "INSERT INTO field_reservations 
                (facility_id, field_id, field_name, team_name, username, contact_name, phone, city, district, reservation_date, reservation_time, fee, status, subscription_plan, subscription_id, needs_player, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                $facility_id, $field_id, $field_name, $team_name, $username, $contact_name, $phone, $city, $district, 
                $reservation_date, $reservation_time, $fee, $status, $subscription_plan, $use_subscription_id, $needs_player, $notes
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Yeni randevu başarıyla kaydoldu!']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID']);
            exit;
        }

        // Owner security check
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') {
            $stmt = $pdo->prepare("DELETE FROM field_reservations WHERE id = ? AND facility_id = ?");
            $stmt->execute([$id, $_SESSION['facility_id']]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM field_reservations WHERE id = ?");
            $stmt->execute([$id]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Randevu silindi.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
