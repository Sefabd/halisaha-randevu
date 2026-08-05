<?php
// api/auth.php - Authentication, Email Registration, Real SMTP Email Link Password Reset & User Reservations API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/mailer.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. LOGIN
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'player');

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı/e-posta ve şifrenizi giriniz.']);
            exit;
        }

        if ($role === 'owner') {
            $stmt = $pdo->prepare("SELECT * FROM facilities WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
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
                echo json_encode(['status' => 'error', 'message' => 'Tesis Girişi Başarısız: Kullanıcı adı/e-posta veya şifre hatalı.']);
                exit;
            }
        } else {
            // Player
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
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
                echo json_encode(['status' => 'error', 'message' => 'Oyuncu Girişi Başarısız: Kullanıcı adı/e-posta veya şifre hatalı.']);
                exit;
            }
        }
    }

    // 2. REGISTER PLAYER (WITH EMAIL)
    if ($action === 'register') {
        $full_name = mb_strtoupper(trim($_POST['full_name'] ?? ''), 'UTF-8');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($full_name) || empty($username) || empty($email) || empty($phone) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen e-posta dahil tüm alanları doldurunuz.']);
            exit;
        }

        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı veya E-Posta adresi zaten kullanılmaktadır.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $email, $hash, $phone]);

        $_SESSION['user_role'] = 'player';
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_username'] = $username;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_team'] = 'neutral';

        echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        exit;
    }

    // 2.5 REGISTER OWNER (WITH EMAIL)
    if ($action === 'register_owner') {
        $facility_name = trim($_POST['facility_name'] ?? '');
        $owner_name = mb_strtoupper(trim($_POST['owner_name'] ?? ''), 'UTF-8');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? 'Kadıköy');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($facility_name) || empty($owner_name) || empty($username) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurunuz.']);
            exit;
        }

        $chk = $pdo->prepare("SELECT id FROM facilities WHERE username = ? OR email = ?");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı veya E-Posta adresi zaten kullanılmaktadır.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO facilities (name, owner_name, username, email, password, city, district, address, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$facility_name, $owner_name, $username, $email, $hash, $city, $district, $address, $phone]);

        $fac_id = $pdo->lastInsertId();

        $insField = $pdo->prepare("INSERT INTO facility_fields (facility_id, field_name, field_type, hourly_fee, status) VALUES (?, ?, ?, ?, ?)");
        $insField->execute([$fac_id, 'Saha 1', 'Kapalı Futbol Sahası', 1200.00, 'Aktif']);

        $_SESSION['user_role'] = 'owner';
        $_SESSION['owner_id'] = $fac_id;
        $_SESSION['owner_name'] = $owner_name;
        $_SESSION['facility_id'] = $fac_id;
        $_SESSION['facility_name'] = $facility_name;
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;
        $_SESSION['user_team'] = 'neutral';

        echo json_encode(['status' => 'success', 'redirect' => 'owner_dashboard.php']);
        exit;
    }

    // 3. SEND EMAIL PASSWORD RESET LINK (REAL SMTP + SIMULATION BACKUP)
    if ($action === 'send_reset_email') {
        $email_or_user = trim($_POST['email'] ?? '');

        if (empty($email_or_user)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen E-Posta adresinizi veya kullanıcı adınızı giriniz.']);
            exit;
        }

        $target_email = '';
        $account_type = 'player';
        $user_display_name = '';

        // Search in users
        $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email_or_user, $email_or_user]);
        $u = $stmt->fetch();

        if ($u) {
            $target_email = $u['email'];
            $user_display_name = $u['full_name'];
            $account_type = 'player';
        } else {
            // Search in facilities
            $stmtFac = $pdo->prepare("SELECT email, owner_name FROM facilities WHERE email = ? OR username = ?");
            $stmtFac->execute([$email_or_user, $email_or_user]);
            $f = $stmtFac->fetch();

            if ($f) {
                $target_email = $f['email'];
                $user_display_name = $f['owner_name'];
                $account_type = 'owner';
            }
        }

        if (empty($target_email)) {
            echo json_encode(['status' => 'error', 'message' => 'Girilen E-Posta adresi veya kullanıcı adı sistemde bulunamadı.']);
            exit;
        }

        // Generate secure 32-char hex token
        $token = bin2hex(random_bytes(16));

        // Delete previous tokens for this email
        $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $del->execute([$target_email]);

        // Insert new token
        $ins = $pdo->prepare("INSERT INTO password_resets (email, token, account_type) VALUES (?, ?, ?)");
        $ins->execute([$target_email, $token, $account_type]);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $reset_link = "{$protocol}://{$host}/reset_password.php?token={$token}";

        // Construct HTML email for real SMTP sending
        $htmlContent = "
        <div style='font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #ffffff;'>
            <div style='text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;'>
                <h2 style='color: #0f172a; margin: 0;'>⚽ SahaNet PRO</h2>
                <span style='color: #64748b; font-size: 13px;'>Online Spor Tesisleri Kiralama Portalı</span>
            </div>
            <p style='font-size: 15px; color: #334155;'>Merhaba <strong>" . htmlspecialchars($user_display_name) . "</strong>,</p>
            <p style='font-size: 14px; color: #475569;'>SahaNet PRO hesabınız için bir şifre sıfırlama talebinde bulundunuz. Yeni şifrenizi belirlemek için aşağıdaki yeşil butona tıklayabilirsiniz:</p>
            <div style='text-align: center; margin: 28px 0;'>
                <a href='{$reset_link}' style='background-color: #10b981; color: #ffffff; padding: 14px 28px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block; font-size: 15px; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);'>👉 ŞİFREMİ SIFIRLA</a>
            </div>
            <p style='font-size: 12px; color: #94a3b8; line-height: 1.5;'>Butona tıklayamıyorsanız tarayıcınıza şu bağlantıyı kopyalayabilirsiniz:<br><a href='{$reset_link}' style='color: #0284c7;'>{$reset_link}</a></p>
            <hr style='border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
            <p style='font-size: 11px; color: #94a3b8; text-align: center; margin: 0;'>Bu talebi siz yapmadıysanız e-postayı güvenle göz ardı edebilirsiniz.</p>
        </div>";

        // Try sending via real SMTP
        $smtpResult = send_smtp_email($target_email, 'SahaNet PRO - Şifre Sıfırlama Bağlantınız', $htmlContent);

        if ($smtpResult['success']) {
            $msg = "📧 GERÇEK E-POSTA GÖNDERİLDİ!\nŞifre sıfırlama bağlantısı {$target_email} Gmail adresinize ulaştırıldı. Lütfen gelen kutunuzu (veya spam klasörünü) kontrol ediniz.";
        } else {
            $msg = "📧 E-Posta bağlantısı oluşturuldu! (Gmail SMTP şifresi yapılandırılmadıysa aşağıdaki simüle mail butonuna tıklayabilirsiniz)";
        }

        echo json_encode([
            'status' => 'success',
            'email' => $target_email,
            'reset_link' => $reset_link,
            'smtp_sent' => $smtpResult['success'],
            'message' => $msg
        ]);
        exit;
    }

    // 4. RESET PASSWORD WITH TOKEN
    if ($action === 'reset_password_with_token') {
        $token = trim($_POST['token'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if (empty($token) || empty($new_password)) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen geçerli bağlantı ve yeni şifrenizi giriniz.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş!']);
            exit;
        }

        $newHash = password_hash($new_password, PASSWORD_DEFAULT);

        if ($row['account_type'] === 'player') {
            $up = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $up->execute([$newHash, $row['email']]);
        } else {
            $up = $pdo->prepare("UPDATE facilities SET password = ? WHERE email = ?");
            $up->execute([$newHash, $row['email']]);
        }

        $del = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $del->execute([$token]);

        echo json_encode(['status' => 'success', 'message' => '🎉 Şifreniz başarıyla sıfırlandı! Yeni şifrenizle giriş yapabilirsiniz.']);
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
            $stmt = $pdo->prepare("SELECT id, full_name, username, email, phone, favorite_team FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $resStmt = $pdo->prepare("SELECT r.*, f.name as facility_name FROM field_reservations r LEFT JOIN facilities f ON r.facility_id = f.id WHERE r.phone = ? OR r.contact_name = ? ORDER BY r.reservation_date DESC, r.reservation_time ASC");
            $resStmt->execute([$user['phone'], $user['full_name']]);
            $myReservations = $resStmt->fetchAll();

            echo json_encode(['status' => 'success', 'profile' => $user, 'reservations' => $myReservations]);
            exit;
        } else {
            $owner_id = $_SESSION['owner_id'];
            $stmt = $pdo->prepare("SELECT id, owner_name as full_name, username, email, phone, favorite_team FROM facilities WHERE id = ?");
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
        $email = trim($_POST['email'] ?? '');
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
                $up = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
                $up->execute([$full_name, $phone, $email, $newHash, $user_id]);
            } else {
                $up = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?");
                $up->execute([$full_name, $phone, $email, $user_id]);
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
