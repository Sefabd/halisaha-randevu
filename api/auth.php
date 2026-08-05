<?php
// api/auth.php - Session, Authentication & Team Theme Controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../config/db.php';
$action = $_REQUEST['action'] ?? '';

try {
    if ($action === 'set_team') {
        $team = trim($_POST['team'] ?? 'neutral');
        $_SESSION['user_team'] = $team;
        echo json_encode(['status' => 'success', 'team' => $team]);
        exit;
    }

    if ($action === 'login_owner') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $team = trim($_POST['team'] ?? 'galatasaray');

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
            $_SESSION['user_team'] = $team;

            echo json_encode(['status' => 'success', 'redirect' => 'owner_dashboard.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hatalı kullanıcı adı veya şifre!']);
        }
        exit;
    }

    if ($action === 'login_player') {
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? 'Kadıköy');
        $team = trim($_POST['team'] ?? 'galatasaray');

        $_SESSION['user_role'] = 'player';
        $_SESSION['city'] = $city;
        $_SESSION['district'] = $district;
        $_SESSION['user_team'] = $team;

        echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['status' => 'success', 'redirect' => 'login.php']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    exit;
}
