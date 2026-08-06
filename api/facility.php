<?php
// api/facility.php - Facility, Field, Maintenance, Weekend Hours, Features & Subscription Management API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. PUBLIC LIST FACILITIES WITH DYNAMIC FEATURE FILTERING
    if ($action === 'list_public') {
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');
        $sport_type = trim($_GET['sport_type'] ?? '');
        $features_req = trim($_GET['features'] ?? '');

        $sql = "SELECT * FROM facilities WHERE 1=1";
        $params = [];

        if (!empty($city)) {
            $sql .= " AND city = ?";
            $params[] = $city;
        }

        if (!empty($district) && $district !== 'Tüm İlçeler') {
            $sql .= " AND district = ?";
            $params[] = $district;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $facilities = $stmt->fetchAll();

        $req_features_arr = [];
        if (!empty($features_req)) {
            $req_features_arr = array_filter(explode(',', $features_req));
        }

        $result = [];
        foreach ($facilities as $fac) {
            $fieldsStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ?");
            $fieldsStmt->execute([$fac['id']]);
            $fields = $fieldsStmt->fetchAll();

            foreach ($fields as &$f) {
                $f['closed_range_obj'] = json_decode($f['closed_range'] ?? '{}', true) ?: (object)[];
            }
            unset($f);

            if (!empty($sport_type) && $sport_type !== 'Tümü') {
                $hasSport = false;
                $st = mb_strtolower($sport_type, 'UTF-8');

                foreach ($fields as $f) {
                    $ft = mb_strtolower($f['field_type'] . ' ' . $f['field_name'], 'UTF-8');

                    if (str_contains($st, 'halı') || str_contains($st, 'futbol')) {
                        if (str_contains($ft, 'futbol') || str_contains($ft, 'saha') || str_contains($ft, 'halı')) {
                            $hasSport = true;
                            break;
                        }
                    } elseif (str_contains($st, 'basketbol')) {
                        if (str_contains($ft, 'basketbol')) {
                            $hasSport = true;
                            break;
                        }
                    } elseif (str_contains($st, 'tenis')) {
                        if (str_contains($ft, 'tenis')) {
                            $hasSport = true;
                            break;
                        }
                    } elseif (str_contains($st, 'voleybol')) {
                        if (str_contains($ft, 'voleybol')) {
                            $hasSport = true;
                            break;
                        }
                    } else {
                        if (str_contains($ft, $st)) {
                            $hasSport = true;
                            break;
                        }
                    }
                }
                if (!$hasSport) continue;
            }

            $fac_features = json_decode($fac['features'] ?? '[]', true) ?: ["HD Kamera Kaydı", "Ücretsiz Su & İkram", "Soyunma Odası & Duş"];

            if (!empty($req_features_arr)) {
                $hasAllFeatures = true;
                foreach ($req_features_arr as $req) {
                    if (!in_array($req, $fac_features)) {
                        $hasAllFeatures = false;
                        break;
                    }
                }
                if (!$hasAllFeatures) continue;
            }

            $fac['closed_dates_array'] = json_decode($fac['closed_dates'] ?? '[]', true) ?: [];
            $fac['features_array'] = $fac_features;
            $fac['fields'] = $fields;
            $result[] = $fac;
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
        exit;
    }

    // 2. GET LOGGED-IN OWNER FACILITY DETAILS
    if ($action === 'get_owner_facility') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        $stmt->execute([$facility_id]);
        $fac = $stmt->fetch();

        $fieldsStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE facility_id = ?");
        $fieldsStmt->execute([$facility_id]);
        $fields = $fieldsStmt->fetchAll();

        foreach ($fields as &$f) {
            $f['closed_range_obj'] = json_decode($f['closed_range'] ?? '{}', true) ?: (object)[];
        }
        unset($f);

        $fac['closed_dates_array'] = json_decode($fac['closed_dates'] ?? '[]', true) ?: [];
        $fac['features_array'] = json_decode($fac['features'] ?? '[]', true) ?: ["HD Kamera Kaydı", "Ücretsiz Su & İkram", "Soyunma Odası & Duş"];

        echo json_encode(['status' => 'success', 'facility' => $fac, 'fields' => $fields]);
        exit;
    }

    // 3. UPDATE OWNER FACILITY PROFILE & FEATURES & WORK HOURS
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

        $features = $_POST['features'] ?? [];
        $features_json = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);

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

        $up = $pdo->prepare("UPDATE facilities SET name = ?, city = ?, district = ?, address = ?, phone = ?, open_time = ?, close_time = ?, open_time_weekend = ?, close_time_weekend = ?, closed_dates = ?, features = ? WHERE id = ?");
        $up->execute([$name, $city, $district, $address, $phone, $open_time, $close_time, $open_time_weekend, $close_time_weekend, $closed_json, $features_json, $facility_id]);

        $_SESSION['facility_name'] = $name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;

        echo json_encode(['status' => 'success', 'message' => 'Tesis profili, çalışma saatleri ve imkanları güncellendi.']);
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

    // 4. SET FIELD CLOSED DATE & TIME RANGE (RE-OPENING BUG FIX: IF AKTİF CLEAR RANGE OBJ)
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

        $range_obj = (object)[];
        if ($status === 'Pasif') {
            if (!empty($start_date)) {
                $range_obj = [
                    'start_date' => $start_date,
                    'start_time' => $start_time,
                    'end_date' => $end_date ?: $start_date,
                    'end_time' => $end_time,
                    'reason' => $reason
                ];
            }
        }

        $range_json = json_encode($range_obj, JSON_UNESCAPED_UNICODE);

        $up = $pdo->prepare("UPDATE facility_fields SET status = ?, closed_range = ? WHERE id = ? AND facility_id = ?");
        $up->execute([$status, $range_json, $field_id, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Saha durumu ve kapalı tarih/saat aralığı güncellendi.']);
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

        if (empty($field_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Saha adı boş olamaz']);
            exit;
        }

        if ($field_id > 0) {
            $up = $pdo->prepare("UPDATE facility_fields SET field_name = ?, field_type = ?, hourly_fee = ?, status = ? WHERE id = ? AND facility_id = ?");
            $up->execute([$field_name, $field_type, $hourly_fee, $status, $field_id, $facility_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee, status) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$facility_id, $field_name, $field_type, $hourly_fee, $status]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Saha kaydedildi']);
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

        echo json_encode(['status' => 'success', 'message' => 'Saha silindi']);
        exit;
    }

    // 7. BUY USER SUBSCRIPTION (Aylık 4 Maç, 3 Aylık 12 Maç, 6 Aylık 24 Maç - Flexible or Periodic)
    if ($action === 'buy_subscription') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'player') {
            echo json_encode(['status' => 'error', 'message' => 'Abonman almak için oyuncu girişi yapmalısınız.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_phone = $_SESSION['user_phone'] ?? trim($_POST['phone'] ?? '');

        $facility_id = intval($_POST['facility_id'] ?? 0);
        $field_id = intval($_POST['field_id'] ?? 0);
        $package_type = trim($_POST['package_type'] ?? '1_month'); // 1_month, 3_months, 6_months
        $booking_mode = trim($_POST['booking_mode'] ?? 'flexible'); // periodic, flexible
        $preferred_day = trim($_POST['preferred_day'] ?? 'Çarşamba');
        $preferred_time = trim($_POST['preferred_time'] ?? '20:00');
        $team_name = trim($_POST['team_name'] ?? 'Abonman Takımı');

        $facStmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        $facStmt->execute([$facility_id]);
        $fac = $facStmt->fetch();

        if (!$fac) {
            echo json_encode(['status' => 'error', 'message' => 'Tesis bulunamadı.']);
            exit;
        }

        $fieldStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE id = ? AND facility_id = ?");
        $fieldStmt->execute([$field_id, $facility_id]);
        $field = $fieldStmt->fetch();

        $fieldName = $field ? $field['field_name'] : 'Tüm Sahalar';
        $hourlyFee = $field ? floatval($field['hourly_fee']) : 1200.00;

        $totalMatches = 4;
        $discountRate = 10;
        $packageName = 'Aylık Paket (4 Maç - %10 İndirim)';

        if ($package_type === '3_months') {
            $totalMatches = 12;
            $discountRate = 15;
            $packageName = '3 Aylık Paket (12 Maç - %15 İndirim)';
        } elseif ($package_type === '6_months') {
            $totalMatches = 24;
            $discountRate = 20;
            $packageName = '6 Aylık Paket (24 Maç - %20 İndirim VIP)';
        }

        $totalPrice = ($hourlyFee * $totalMatches) * (1 - ($discountRate / 100));

        $usedMatches = 0;
        $remainingMatches = $totalMatches;

        if ($booking_mode === 'periodic') {
            $usedMatches = $totalMatches;
            $remainingMatches = 0;
        }

        $insSub = $pdo->prepare("INSERT INTO user_subscriptions (user_id, user_name, user_phone, facility_id, facility_name, field_id, field_name, package_name, period_type, total_matches, used_matches, remaining_matches, discount_rate, total_price, booking_mode, preferred_day, preferred_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insSub->execute([$user_id, $user_name, $user_phone, $facility_id, $fac['name'], $field_id, $fieldName, $packageName, $package_type, $totalMatches, $usedMatches, $remainingMatches, $discountRate, $totalPrice, $booking_mode, $preferred_day, $preferred_time, 'Aktif']);
        $subId = $pdo->lastInsertId();

        // If Periodic Booking selected, auto-book the next 4, 12, or 24 weekly slots!
        if ($booking_mode === 'periodic' && $field_id > 0) {
            $turkishDays = ['Pazar' => 0, 'Pazartesi' => 1, 'Salı' => 2, 'Çarşamba' => 3, 'Perşembe' => 4, 'Cuma' => 5, 'Cumartesi' => 6];
            $targetDayNum = $turkishDays[$preferred_day] ?? 3;

            $insRes = $pdo->prepare("INSERT INTO field_reservations (facility_id, field_id, field_name, team_name, contact_name, phone, city, district, reservation_date, reservation_time, fee, status, subscription_plan, subscription_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $perMatchFee = $totalPrice / $totalMatches;

            $currentDate = new DateTime();
            $bookedCount = 0;

            while ($bookedCount < $totalMatches) {
                if ((int)$currentDate->format('w') === $targetDayNum && $currentDate->format('Y-m-d') >= date('Y-m-d')) {
                    $dateStr = $currentDate->format('Y-m-d');
                    
                    // Check if slot already booked
                    $chk = $pdo->prepare("SELECT id FROM field_reservations WHERE field_id = ? AND reservation_date = ? AND reservation_time = ? AND status != 'İptal'");
                    $chk->execute([$field_id, $dateStr, $preferred_time]);

                    if (!$chk->fetch()) {
                        $insRes->execute([$facility_id, $field_id, $fieldName, $team_name, $user_name, $user_phone, $fac['city'], $fac['district'], $dateStr, $preferred_time, $perMatchFee, 'Onaylandı', $packageName, $subId]);
                        $bookedCount++;
                    }
                }
                $currentDate->modify('+1 day');
            }
        }

        echo json_encode(['status' => 'success', 'message' => "🎉 Tebrikler! {$packageName} abonmanlığınız başarıyla tanımlandı."]);
        exit;
    }

    // 8. LIST USER SUBSCRIPTIONS (For Player Profile)
    if ($action === 'list_user_subscriptions') {
        if (!isset($_SESSION['user_role'])) {
            echo json_encode(['status' => 'error', 'message' => 'Oturum açmalısınız.']);
            exit;
        }

        $user_id = $_SESSION['user_id'] ?? 0;
        $user_phone = $_SESSION['user_phone'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE user_id = ? OR user_phone = ? ORDER BY id DESC");
        $stmt->execute([$user_id, $user_phone]);
        $subs = $stmt->fetchAll();

        echo json_encode(['status' => 'success', 'data' => $subs]);
        exit;
    }

    // 9. LIST OWNER SUBSCRIPTIONS (For Facility Dashboard)
    if ($action === 'list_owner_subscriptions') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $stmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE facility_id = ? ORDER BY id DESC");
        $stmt->execute([$facility_id]);
        $subs = $stmt->fetchAll();

        echo json_encode(['status' => 'success', 'data' => $subs]);
        exit;
    }

    // 10. OWNER MANUAL ADD SUBSCRIPTION
    if ($action === 'add_owner_subscription') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $user_name = trim($_POST['user_name'] ?? '');
        $user_phone = trim($_POST['user_phone'] ?? '');
        $field_id = intval($_POST['field_id'] ?? 0);
        $package_type = trim($_POST['package_type'] ?? '1_month');
        $booking_mode = trim($_POST['booking_mode'] ?? 'flexible');
        $preferred_day = trim($_POST['preferred_day'] ?? 'Çarşamba');
        $preferred_time = trim($_POST['preferred_time'] ?? '20:00');
        $team_name = trim($_POST['team_name'] ?? 'Abonman Takımı');

        if (empty($user_name) || empty($user_phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Kullanıcı adı ve telefon zorunludur.']);
            exit;
        }

        $facStmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        $facStmt->execute([$facility_id]);
        $fac = $facStmt->fetch();

        $fieldStmt = $pdo->prepare("SELECT * FROM facility_fields WHERE id = ? AND facility_id = ?");
        $fieldStmt->execute([$field_id, $facility_id]);
        $field = $fieldStmt->fetch();

        $fieldName = $field ? $field['field_name'] : 'Tüm Sahalar';
        $hourlyFee = $field ? floatval($field['hourly_fee']) : 1200.00;

        $totalMatches = 4;
        $discountRate = 10;
        $packageName = 'Aylık Paket (4 Maç - %10 İndirim)';

        if ($package_type === '3_months') {
            $totalMatches = 12;
            $discountRate = 15;
            $packageName = '3 Aylık Paket (12 Maç - %15 İndirim)';
        } elseif ($package_type === '6_months') {
            $totalMatches = 24;
            $discountRate = 20;
            $packageName = '6 Aylık Paket (24 Maç - %20 İndirim VIP)';
        }

        $totalPrice = ($hourlyFee * $totalMatches) * (1 - ($discountRate / 100));

        $usedMatches = ($booking_mode === 'periodic') ? $totalMatches : 0;
        $remainingMatches = ($booking_mode === 'periodic') ? 0 : $totalMatches;

        $insSub = $pdo->prepare("INSERT INTO user_subscriptions (user_id, user_name, user_phone, facility_id, facility_name, field_id, field_name, package_name, period_type, total_matches, used_matches, remaining_matches, discount_rate, total_price, booking_mode, preferred_day, preferred_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insSub->execute([0, $user_name, $user_phone, $facility_id, $fac['name'], $field_id, $fieldName, $packageName, $package_type, $totalMatches, $usedMatches, $remainingMatches, $discountRate, $totalPrice, $booking_mode, $preferred_day, $preferred_time, 'Aktif']);
        $subId = $pdo->lastInsertId();

        if ($booking_mode === 'periodic' && $field_id > 0) {
            $turkishDays = ['Pazar' => 0, 'Pazartesi' => 1, 'Salı' => 2, 'Çarşamba' => 3, 'Perşembe' => 4, 'Cuma' => 5, 'Cumartesi' => 6];
            $targetDayNum = $turkishDays[$preferred_day] ?? 3;

            $insRes = $pdo->prepare("INSERT INTO field_reservations (facility_id, field_id, field_name, team_name, contact_name, phone, city, district, reservation_date, reservation_time, fee, status, subscription_plan, subscription_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $perMatchFee = $totalPrice / $totalMatches;

            $currentDate = new DateTime();
            $bookedCount = 0;

            while ($bookedCount < $totalMatches) {
                if ((int)$currentDate->format('w') === $targetDayNum && $currentDate->format('Y-m-d') >= date('Y-m-d')) {
                    $dateStr = $currentDate->format('Y-m-d');
                    
                    $chk = $pdo->prepare("SELECT id FROM field_reservations WHERE field_id = ? AND reservation_date = ? AND reservation_time = ? AND status != 'İptal'");
                    $chk->execute([$field_id, $dateStr, $preferred_time]);

                    if (!$chk->fetch()) {
                        $insRes->execute([$facility_id, $field_id, $fieldName, $team_name, $user_name, $user_phone, $fac['city'], $fac['district'], $dateStr, $preferred_time, $perMatchFee, 'Onaylandı', $packageName, $subId]);
                        $bookedCount++;
                    }
                }
                $currentDate->modify('+1 day');
            }
        }

        echo json_encode(['status' => 'success', 'message' => "Müşteri adına {$packageName} abonmanlığı başarıyla oluşturuldu."]);
        exit;
    }

    // 11. OWNER DELETE/CANCEL SUBSCRIPTION
    if ($action === 'delete_subscription') {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
            exit;
        }

        $facility_id = $_SESSION['facility_id'];
        $sub_id = intval($_POST['sub_id'] ?? 0);

        $del = $pdo->prepare("DELETE FROM user_subscriptions WHERE id = ? AND facility_id = ?");
        $del->execute([$sub_id, $facility_id]);

        echo json_encode(['status' => 'success', 'message' => 'Abonman kaydı başarıyla silindi/iptal edildi.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
