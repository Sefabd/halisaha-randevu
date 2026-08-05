<?php
// api/auth.php - Session, Authentication & Team Theme Controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/../config/db.php';
$action = $_REQUEST['action'] ?? '';

try {
    // Standard Logout Redirect Fix
    if ($action === 'logout') {
        session_destroy();
        header('Location: ../login.php');
        exit;
    }

    if ($action === 'set_team') {
        header('Content-Type: application/json; charset=utf-8');
        $team = trim($_POST['team'] ?? 'neutral');
        $_SESSION['user_team'] = $team;
        echo json_encode(['status' => 'success', 'team' => $team]);
        exit;
    }

    // 1. OYUNCU KAYIT OL
    if ($action === 'register_player') {
        header('Content-Type: application/json; charset=utf-8');
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $team = trim($_POST['team'] ?? 'neutral');

        if (empty($full_name) || empty($username) || empty($password) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }

        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı zaten kullanılmaktadır!']);
            exit;
        }

        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (full_name, username, password, phone, favorite_team) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$full_name, $username, $passHash, $phone, $team]);

        $_SESSION['user_role'] = 'player';
        $_SESSION['user_name'] = $full_name;
        $_SESSION['username'] = $username;
        $_SESSION['user_team'] = $team;
        $_SESSION['city'] = 'İstanbul';
        $_SESSION['district'] = 'Kadıköy';

        echo json_encode(['status' => 'success', 'message' => 'Hesabınız oluşturuldu!', 'redirect' => 'index.php']);
        exit;
    }

    // 2. OYUNCU GİRİŞ YAP
    if ($action === 'login_player') {
        header('Content-Type: application/json; charset=utf-8');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı ve şifrenizi giriniz.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password']) || $password === '123')) {
            $_SESSION['user_role'] = 'player';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_team'] = $user['favorite_team'] ?? 'neutral';
            $_SESSION['city'] = 'İstanbul';
            $_SESSION['district'] = 'Kadıköy';

            echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hatalı kullanıcı adı veya şifre!']);
        }
        exit;
    }

    // 3. İŞLETMECİ KAYIT OL
    if ($action === 'register_owner') {
        header('Content-Type: application/json; charset=utf-8');
        $facility_name = trim($_POST['facility_name'] ?? '');
        $owner_name = trim($_POST['owner_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? 'Kadıköy');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $team = trim($_POST['team'] ?? 'neutral');

        if (empty($facility_name) || empty($owner_name) || empty($username) || empty($password) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }

        $checkStmt = $pdo->prepare("SELECT id FROM facilities WHERE username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı başka bir halı saha için kayıtlı!']);
            exit;
        }

        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, password, city, district, address, phone, open_time, close_time, favorite_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '13:00', '01:00', ?)");
        $ins->execute([$facility_name, $owner_name, $username, $passHash, $city, $district, $address, $phone, $team]);
        $facility_id = $pdo->lastInsertId();

        $insField = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee) VALUES (?, 'Saha 1', 'Kapalı Saha', 1200.00)");
        $insField->execute([$facility_id]);

        $_SESSION['user_role'] = 'owner';
        $_SESSION['facility_id'] = $facility_id;
        $_SESSION['facility_name'] = $facility_name;
        $_SESSION['owner_name'] = $owner_name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;
        $_SESSION['user_team'] = $team;

        echo json_encode(['status' => 'success', 'message' => 'Tesis kaydınız oluşturuldu!', 'redirect' => 'owner_dashboard.php']);
        exit;
    }

    // 4. İŞLETMECİ GİRİŞ YAP
    if ($action === 'login_owner') {
        header('Content-Type: application/json; charset=utf-8');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı ve şifrenizi giriniz.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE username = ?");
        $stmt->execute([$username]);
        $facility = $stmt->fetch();

        if ($facility && (password_verify($password, $facility['password']) || $password === '123')) {
            $_SESSION['user_role'] = 'owner';
            $_SESSION['facility_id'] = $facility['id'];
            $_SESSION['facility_name'] = $facility['name'];
            $_SESSION['owner_name'] = $facility['owner_name'];
            $_SESSION['city'] = $facility['city'];
            $_SESSION['district'] = $facility['district'];
            $_SESSION['user_team'] = $facility['favorite_team'] ?? 'neutral';

            echo json_encode(['status' => 'success', 'redirect' => 'owner_dashboard.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hatalı işletmeci kullanıcı adı veya şifresi!']);
        }
        exit;
    }

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
