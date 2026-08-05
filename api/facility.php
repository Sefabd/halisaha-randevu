<?php
// api/facility.php - Facility Public Discovery & Owner Management API with Field-Specific Closed Date/Time Ranges
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
$pdo = require __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

try {
    // 1. PUBLIC LISTING WITH SPORT TYPE & CITY/DISTRICT & FEATURE FILTERS
    if ($action === 'list_public') {
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');
        $sport_type = trim($_GET['sport_type'] ?? '');
        $req_features = trim($_GET['features'] ?? '');
        $req_feature_list = !empty($req_features) ? explode(',', $req_features) : [];

        $sql = "SELECT id, name, city, district, address, phone, open_time, close_time, open_time_weekend, close_time_weekend, closed_dates, features FROM facilities WHERE 1=1";
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

        // Attach fields to each facility
        $result = [];
        foreach ($facilities as $fac) {
            $fieldSql = "SELECT id, field_name, field_type, hourly_fee, status, features, closed_range FROM facility_fields WHERE facility_id = ?";
            $fieldParams = [$fac['id']];

            if (!empty($sport_type) && $sport_type !== 'Tümü') {
                if (mb_strpos($sport_type, 'Halı') !== false || mb_strpos($sport_type, 'Futbol') !== false) {
                    $fieldSql .= " AND (field_name LIKE '%Futbol%' OR field_type LIKE '%Futbol%' OR field_name LIKE '%Halı%' OR field_name LIKE 'Saha%')";
                } else {
                    $cleanSport = str_replace([' Sahası', ' Kortu'], '', $sport_type);
                    $fieldSql .= " AND (field_name LIKE ? OR field_type LIKE ?)";
                    $fieldParams[] = "%{$cleanSport}%";
                    $fieldParams[] = "%{$cleanSport}%";
                }
            }

            $stmtFields = $pdo->prepare($fieldSql);
            $stmtFields->execute($fieldParams);
            $fields = $stmtFields->fetchAll();

            // Decode JSON features and closed dates
            $fac['closed_dates_array'] = json_decode($fac['closed_dates'] ?? '[]', true) ?: [];
            $fac_features = json_decode($fac['features'] ?? '[]', true) ?: [];
            $fac['features_array'] = $fac_features;

            // Two-Way Feature Filter check (Camera, Water, Shower, Shoes)
            if (!empty($req_feature_list)) {
                $has_all_features = true;
                foreach ($req_feature_list as $rf) {
                    $rf = trim($rf);
                    if (empty($rf)) continue;
                    if (!in_array($rf, $fac_features)) {
                        // Check if any field has this feature
                        $field_has = false;
                        foreach ($fields as $f) {
                            $ffeats = json_decode($f['features'] ?? '[]', true) ?: [];
                            if (in_array($rf, $ffeats)) { $field_has = true; break; }
                        }
                        if (!$field_has) { $has_all_features = false; break; }
                    }
                }
                if (!$has_all_features) continue;
            }

            foreach ($fields as &$f) {
                $f['features_array'] = json_decode($f['features'] ?? '[]', true) ?: [];
                $f['closed_range_obj'] = json_decode($f['closed_range'] ?? '{}', true) ?: (object)[];
            }

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

        $facility['closed_dates_array'] = json_decode($facility['closed_dates'] ?? '[]', true) ?: [];

        $stmtFields = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ?");
        $stmtFields->execute([$facility_id]);
        $fields = $stmtFields->fetchAll();

        foreach ($fields as &$f) {
            $f['features_array'] = json_decode($f['features'] ?? '[]', true) ?: [];
            $f['closed_range_obj'] = json_decode($f['closed_range'] ?? '{}', true) ?: (object)[];
        }

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
        $open_time_weekend = trim($_POST['open_time_weekend'] ?? '09:00');
        $close_time_weekend = trim($_POST['close_time_weekend'] ?? '03:00');

        $start_date = trim($_POST['closed_start_date'] ?? '');
        $end_date = trim($_POST['closed_end_date'] ?? '');
        $closed_reason = trim($_POST['closed_reason'] ?? 'Tesis Kapalı');

        $stmt = $pdo->prepare("SELECT closed_dates FROM facilities WHERE id = ?");
        $stmt->execute([$facility_id]);
        $existing = json_decode($stmt->fetch()['closed_dates'] ?? '[]', true) ?: [];

        if (!empty($start_date)) {
            if (empty($end_date)) $end_date = $start_date;
            $existing[] = ['start' => $start_date, 'end' => $end_date, 'reason' => $closed_reason];
        }

        $closed_json = json_encode(array_values($existing), JSON_UNESCAPED_UNICODE);

        $up = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, district = ?, address = ?, phone = ?, open_time = ?, close_time = ?, open_time_weekend = ?, close_time_weekend = ?, closed_dates = ? WHERE id = ?");
        $up->execute([$name, $city, $district, $address, $phone, $open_time, $close_time, $open_time_weekend, $close_time_weekend, $closed_json, $facility_id]);

        $_SESSION['facility_name'] = $name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;

        echo json_encode(['status' => 'success', 'message' => 'Tesis profili ve kapalı tarihler güncellendi.']);
        exit;
    }

    // 3.5 REMOVE CLOSED DATE RANGE
    if ($action === 'remove_closed_date') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $start = trim($_POST['start'] ?? '');

        $stmt = $pdo->prepare("SELECT closed_dates FROM facilities WHERE id = ?");
        $stmt->execute([$facility_id]);
        $existing = json_decode($stmt->fetch()['closed_dates'] ?? '[]', true) ?: [];

        $filtered = array_filter($existing, function($item) use ($start) {
            $itemStart = is_array($item) ? ($item['start'] ?? $item['date'] ?? '') : $item;
            return $itemStart !== $start;
        });

        $closed_json = json_encode(array_values($filtered), JSON_UNESCAPED_UNICODE);

        $up = $pdo->prepare("UPDATE facilities SET closed_dates = ? WHERE id = ?");
        $up->execute([$closed_json, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Kapalı tarih kaldırıldı.']);
        exit;
    }

    // 4. SET FIELD CLOSED DATE & TIME RANGE
    if ($action === 'set_field_closed_range') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $field_id = intval($_POST['field_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Aktif');
        $start_date = trim($_POST['closed_start_date'] ?? '');
        $start_time = trim($_POST['closed_start_time'] ?? '00:00');
        $end_date = trim($_POST['closed_end_date'] ?? '');
        $end_time = trim($_POST['closed_end_time'] ?? '23:59');
        $reason = trim($_POST['closed_reason'] ?? 'Bakım / Kapalı');

        $range_obj = [];
        if ($status === 'Pasif' || !empty($start_date)) {
            $range_obj = [
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_date' => $end_date ?: $start_date,
                'end_time' => $end_time,
                'reason' => $reason
            ];
        }

        $range_json = json_encode($range_obj, JSON_UNESCAPED_UNICODE);

        $up = $pdo->prepare("UPDATE facility_fields SET status = ?, closed_range = ? WHERE id = ? AND facility_id = ?");
        $up->execute([$status, $range_json, $field_id, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Saha kapalı tarih ve saat aralığı güncellendi.']);
        exit;
    }

    // 5. SAVE FIELD
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
        $status = trim($_POST['status'] ?? 'Aktif');

        $features = $_POST['features'] ?? [];
        if (!is_array($features)) $features = [];
        $features_json = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);

        if (empty($field_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Saha adı boş olamaz']);
            exit;
        }

        if ($field_id > 0) {
            $up = $pdo->prepare("UPDATE facility_fields SET field_name = ?, field_type = ?, hourly_fee = ?, status = ?, features = ? WHERE id = ? AND facility_id = ?");
            $up->execute([$field_name, $field_type, $hourly_fee, $status, $features_json, $field_id, $facility_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee, status, features) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([$facility_id, $field_name, $field_type, $hourly_fee, $status, $features_json]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Saha kaydedildi.']);
        exit;
    }

    // 6. DELETE FIELD
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
