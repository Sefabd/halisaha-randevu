<?php
// api/facility.php - Facility & Field Management Controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';
$action = $_REQUEST['action'] ?? '';

try {
    // List Facilities for Players by City & District
    if ($action === 'list_public') {
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');

        $sql = "SELECT id, name, owner_name, city, district, address, phone, open_time, close_time FROM facilities WHERE 1=1";
        $params = [];

        if (!empty($city)) {
            $sql .= " AND city = ?";
            $params[] = $city;
        }
        if (!empty($district)) {
            $sql .= " AND district = ?";
            $params[] = $district;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $facilities = $stmt->fetchAll();

        // Attach fields to each facility
        foreach ($facilities as &$fac) {
            $fStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ? AND status = 'Aktif'");
            $fStmt->execute([$fac['id']]);
            $fac['fields'] = $fStmt->fetchAll();
        }

        echo json_encode(['status' => 'success', 'data' => $facilities]);
        exit;
    }

    // Get Facility details & Fields for Owner Panel
    if ($action === 'get_owner_facility') {
        $facility_id = $_SESSION['facility_id'] ?? 0;
        if ($facility_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Oturum açılmamış.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        $stmt->execute([$facility_id]);
        $facility = $stmt->fetch();

        $fStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ?");
        $fStmt->execute([$facility_id]);
        $fields = $fStmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'facility' => $facility,
            'fields' => $fields
        ]);
        exit;
    }

    // Update Facility Profile Info (City, District, Address, Phone, Open/Close Time)
    if ($action === 'update_profile') {
        $facility_id = $_SESSION['facility_id'] ?? 0;
        if ($facility_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $open_time = trim($_POST['open_time'] ?? '13:00');
        $close_time = trim($_POST['close_time'] ?? '01:00');

        $stmt = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, district = ?, address = ?, phone = ?, open_time = ?, close_time = ? WHERE id = ?");
        $stmt->execute([$name, $city, $district, $address, $phone, $open_time, $close_time, $facility_id]);

        $_SESSION['facility_name'] = $name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;

        echo json_encode(['status' => 'success', 'message' => 'İşletme bilgileri ve çalışma saatleri başarıyla güncellendi!']);
        exit;
    }

    // Add or Edit Sub-field for Facility
    if ($action === 'save_field') {
        $facility_id = $_SESSION['facility_id'] ?? 0;
        if ($facility_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
            exit;
        }

        $field_id = (int)($_POST['field_id'] ?? 0);
        $field_name = trim($_POST['field_name'] ?? '');
        $field_type = trim($_POST['field_type'] ?? 'Kapalı Suni Çim');
        $hourly_fee = (float)($_POST['hourly_fee'] ?? 1200.00);

        if (empty($field_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Saha adı boş olamaz.']);
            exit;
        }

        if ($field_id > 0) {
            $stmt = $pdo->prepare("UPDATE facility_fields SET field_name = ?, field_type = ?, hourly_fee = ? WHERE id = ? AND facility_id = ?");
            $stmt->execute([$field_name, $field_type, $hourly_fee, $field_id, $facility_id]);
            echo json_encode(['status' => 'success', 'message' => 'Saha başarıyla güncellendi.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, ?, ?, ?)");
            $stmt->execute([$facility_id, $field_name, $field_type, $hourly_fee]);
            echo json_encode(['status' => 'success', 'message' => 'Yeni saha eklendi.']);
        }
        exit;
    }

    // Delete Field
    if ($action === 'delete_field') {
        $facility_id = $_SESSION['facility_id'] ?? 0;
        $field_id = (int)($_POST['field_id'] ?? 0);

        if ($facility_id <= 0 || $field_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz talep.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM facility_fields WHERE id = ? AND facility_id = ?");
        $stmt->execute([$field_id, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Saha silindi.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
