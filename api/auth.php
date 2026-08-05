<?php
// api/auth.php - Authentication, Profile, OTP Email Password Reset & User Reservations API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. LOGIN
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'player');

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı ve şifrenizi giriniz.']);
            exit;
        }

        if ($role === 'owner') {
            $stmt = $pdo->prepare("SELECT * FROM facilities WHERE username = ?");
            $stmt->execute([$username]);
            $owner = $stmt->fetch();

            if ($owner && password_verify($password, $owner['password'])) {
                $_SESSION['user_role'] = 'owner';
                $_SESSION['owner_id'] = $owner['id'];
                $_SESSION['owner_name'] = mb_strtoupper($owner['owner_name'], 'UTF-8');
                $_SESSION['facility_id'] = $owner['id'];
                $_SESSION['facility_name'] = $owner['name'];
                $_SESSION['user_team'] = $owner['favorite_team'] ?? 'neutral';
                $_SESSION['city'] = $owner['city'];
                $_SESSION['district'] = $owner['district'];

                echo json_encode(['status' => 'success', 'redirect' => 'owner_dashboard.php']);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tesis Girişi Başarısız: Kullanıcı adı veya şifre hatalı.']);
                exit;
            }
        } else {
            // Player
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_role'] = 'player';
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = mb_strtoupper($user['full_name'], 'UTF-8');
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_team'] = $user['favorite_team'] ?? 'neutral';

                echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Oyuncu Girişi Başarısız: Kullanıcı adı veya şifre hatalı.']);
                exit;
            }
        }
    }

    // 2. REGISTER
    if ($action === 'register') {
        $full_name = mb_strtoupper(trim($_POST['full_name'] ?? ''), 'UTF-8');
        $username = trim($_POST['username'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($full_name) || empty($username) || empty($phone) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }

        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $chk->execute([$username]);
        if ($chk->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı zaten kullanılmaktadır.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $hash, $phone]);

        $_SESSION['user_role'] = 'player';
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_username'] = $username;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_team'] = 'neutral';

        echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        exit;
    }

    // 3. SEND EMAIL OTP CODE FOR PASSWORD RESET
    if ($action === 'send_reset_code') {
        $account = trim($_POST['account'] ?? '');

        if (empty($account)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı veya telefon adresinizi giriniz.']);
            exit;
        }

        $user_found = false;
        $account_type = 'player';
        $user_id = 0;

        // Check users
        $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE username = ? OR phone = ?");
        $stmt->execute([$account, $account]);
        $u = $stmt->fetch();

        if ($u) {
            $user_found = true;
            $account_type = 'player';
            $user_id = $u['id'];
        } else {
            // Check facilities (owner)
            $stmtFac = $pdo->prepare("SELECT id, username, name FROM facilities WHERE username = ? OR phone = ?");
            $stmtFac->execute([$account, $account]);
            $f = $stmtFac->fetch();

            if ($f) {
                $user_found = true;
                $account_type = 'owner';
                $user_id = $f['id'];
            }
        }

        if (!$user_found) {
            echo json_encode(['status' => 'error', 'message' => 'Girilen kullanıcı adı veya telefon numarası sistemde bulunamadı.']);
            exit;
        }

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_user_id'] = $user_id;
        $_SESSION['reset_account_type'] = $account_type;

        echo json_encode([
            'status' => 'success',
            'code' => $otp,
            'message' => "📧 Gelen Gmail İletisi (Simülasyon): 6 Haneli Doğrulama Kodunuz [ {$otp} ]"
        ]);
        exit;
    }

    // 4. VERIFY OTP AND RESET PASSWORD
    if ($action === 'verify_reset_code') {
        $code = trim($_POST['code'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if (empty($code) || empty($new_password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen doğrulama kodunu ve yeni şifrenizi giriniz.']);
            exit;
        }

        if (!isset($_SESSION['reset_otp']) || $_SESSION['reset_otp'] !== $code) {
            echo json_encode(['status' => 'error', 'message' => 'Girdiğiniz 6 haneli doğrulama kodu hatalı veya süresi dolmuş!']);
            exit;
        }

        $user_id = $_SESSION['reset_user_id'];
        $account_type = $_SESSION['reset_account_type'];
        $newHash = password_hash($new_password, PASSWORD_DEFAULT);

        if ($account_type === 'player') {
            $up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $up->execute([$newHash, $user_id]);
        } else {
            $up = $pdo->prepare("UPDATE facilities SET password = ? WHERE id = ?");
            $up->execute([$newHash, $user_id]);
        }

        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_account_type']);

        echo json_encode(['status' => 'success', 'message' => '🎉 Şifreniz başarıyla güncellendi! Yeni şifrenizle giriş yapabilirsiniz.']);
        exit;
    }

    // 5. SET TEAM THEME
    if ($action === 'set_team') {
        $team = trim($_POST['team'] ?? 'neutral');
        $_SESSION['user_team'] = $team;

        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'player') {
            $up = $pdo->prepare("UPDATE users SET favorite_team = ? WHERE id = ?");
            $up->execute([$team, $_SESSION['user_id']]);
        } elseif (isset($_SESSION['owner_id']) && $_SESSION['user_role'] === 'owner') {
            $up = $pdo->prepare("UPDATE facilities SET favorite_team = ? WHERE id = ?");
            $up->execute([$team, $_SESSION['owner_id']]);
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    // 6. GET LOGGED-IN USER PROFILE & RESERVATIONS
    if ($action === 'get_user_profile') {
        if (!isset($_SESSION['user_role'])) {
            echo json_encode(['status' => 'error', 'message' => 'Oturum kapalı']);
            exit;
        }

        if ($_SESSION['user_role'] === 'player') {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("SELECT id, full_name, username, phone, favorite_team FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $resStmt = $pdo->prepare("SELECT r.*, f.name as facility_name FROM field_reservations r LEFT JOIN facilities f ON r.facility_id = f.id WHERE r.phone = ? OR r.contact_name = ? ORDER BY r.reservation_date DESC, r.reservation_time ASC");
            $resStmt->execute([$user['phone'], $user['full_name']]);
            $myReservations = $resStmt->fetchAll();

            echo json_encode(['status' => 'success', 'profile' => $user, 'reservations' => $myReservations]);
            exit;
        } else {
            $owner_id = $_SESSION['owner_id'];
            $stmt = $pdo->prepare("SELECT id, owner_name as full_name, username, phone, favorite_team FROM facilities WHERE id = ?");
            $stmt->execute([$owner_id]);
            $owner = $stmt->fetch();

            echo json_encode(['status' => 'success', 'profile' => $owner, 'reservations' => []]);
            exit;
        }
    }

    // 7. UPDATE PROFILE
    if ($action === 'update_profile') {
        if (!isset($_SESSION['user_role'])) {
            echo json_encode(['status' => 'error', 'message' => 'Oturum kapalı']);
            exit;
        }

        $full_name = mb_strtoupper(trim($_POST['full_name'] ?? ''), 'UTF-8');
        $phone = trim($_POST['phone'] ?? '');
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if ($_SESSION['user_role'] === 'player') {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!empty($new_password)) {
                if (!password_verify($current_password, $user['password'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Mevcut şifreniz yanlış!']);
                    exit;
                }
                $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                $up = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?");
                $up->execute([$full_name, $phone, $newHash, $user_id]);
            } else {
                $up = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                $up->execute([$full_name, $phone, $user_id]);
            }

            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_phone'] = $phone;

            echo json_encode(['status' => 'success', 'message' => 'Profil bilgileri ve şifreniz güncellendi.']);
            exit;
        }
    }

    // 8. LOGOUT
    if ($action === 'logout') {
        session_unset();
        session_destroy();
        header('Location: ../index.php');
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
