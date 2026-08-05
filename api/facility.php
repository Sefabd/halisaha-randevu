<?php
// api/facility.php - Facility Public Discovery & Owner Management API with Sport Type Filtering
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
$pdo = require __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

try {
    // 1. PUBLIC LISTING WITH SPORT TYPE & CITY/DISTRICT FILTERS
    if ($action === 'list_public') {
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');
        $sport_type = trim($_GET['sport_type'] ?? '');

        $sql = "SELECT id, name, city, district, address, phone, open_time, close_time FROM facilities WHERE 1=1";
        $params = [];

        if (!empty($city)) {
            $sql .= " AND city = ?";
            $params[] = $city;
        }
        if (!empty($district)) {
            $sql .= " AND district = ?";
            $params[] = $district;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $facilities = $stmt->fetchAll();

        // Attach fields to each facility with optional sport_type filtering
        $result = [];
        foreach ($facilities as $fac) {
            $fieldSql = "SELECT id, field_name, field_type, hourly_fee, status FROM facility_fields WHERE facility_id = ? AND status = 'Aktif'";
            $fieldParams = [$fac['id']];

            if (!empty($sport_type) && $sport_type !== 'Tümü') {
                $cleanSport = str_replace([' Sahası', ' Kortu'], '', $sport_type);
                $fieldSql .= " AND (field_name LIKE ? OR field_type LIKE ?)";
                $fieldParams[] = "%{$cleanSport}%";
                $fieldParams[] = "%{$cleanSport}%";
            }

            $stmtFields = $pdo->prepare($fieldSql);
            $stmtFields->execute($fieldParams);
            $fields = $stmtFields->fetchAll();

            // Only include facilities that have matching fields when filtering by sport_type
            if (!empty($sport_type) && $sport_type !== 'Tümü' && count($fields) === 0) {
                continue;
            }

            $fac['fields'] = $fields;
            $result[] = $fac;
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
        exit;
    }

    // 2. GET OWNER FACILITY
    if ($action === 'get_owner_facility') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        $stmt->execute([$facility_id]);
        $facility = $stmt->fetch();

        $stmtFields = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ?");
        $stmtFields->execute([$facility_id]);
        $fields = $stmtFields->fetchAll();

        echo json_encode(['status' => 'success', 'facility' => $facility, 'fields' => $fields]);
        exit;
    }

    // 3. UPDATE OWNER FACILITY PROFILE
    if ($action === 'update_profile') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $open_time = trim($_POST['open_time'] ?? '13:00');
        $close_time = trim($_POST['close_time'] ?? '01:00');

        $up = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, district = ?, address = ?, phone = ?, open_time = ?, close_time = ? WHERE id = ?");
        $up->execute([$name, $city, $district, $address, $phone, $open_time, $close_time, $facility_id]);

        $_SESSION['facility_name'] = $name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;

        echo json_encode(['status' => 'success', 'message' => 'Tesis profili güncellendi.']);
        exit;
    }

    // 4. SAVE (ADD/EDIT) FIELD
    if ($action === 'save_field') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $field_id = intval($_POST['field_id'] ?? 0);
        $field_name = trim($_POST['field_name'] ?? '');
        $field_type = trim($_POST['field_type'] ?? 'Kapalı Saha');
        $hourly_fee = floatval($_POST['hourly_fee'] ?? 1200.00);

        if (empty($field_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Saha adı boş olamaz']);
            exit;
        }

        if ($field_id > 0) {
            $up = $pdo->prepare("UPDATE facility_fields SET field_name = ?, field_type = ?, hourly_fee = ? WHERE id = ? AND facility_id = ?");
            $up->execute([$field_name, $field_type, $hourly_fee, $field_id, $facility_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
            $ins->execute([$facility_id, $field_name, $field_type, $hourly_fee]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Saha kaydedildi.']);
        exit;
    }

    // 5. DELETE FIELD
    if ($action === 'delete_field') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $field_id = intval($_POST['field_id'] ?? 0);

        $del = $pdo->prepare("DELETE FROM facility_fields WHERE id = ? AND facility_id = ?");
        $del->execute([$field_id, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Saha silindi.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
