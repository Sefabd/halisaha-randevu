<?php
// reset_password.php - Standalone Password Reset Page via Email Reset Token Link
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = trim($_GET['token'] ?? '');
$pdo = require __DIR__ . '/config/db.php';

$valid = false;
$email = '';

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $valid = true;
        $email = $reset['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-team="neutral">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Sıfırla | SahaNet PRO</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light py-4">

<div class="container max-w-500">
    
    <!-- Brand Logo -->
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none">
            <div class="d-inline-flex align-items-center gap-2 brand-badge fs-5 mb-2">
                <i class="fa-solid fa-futbol"></i> SahaNet PRO
            </div>
        </a>
        <h3 class="fw-extrabold text-dark fs-4 mb-1">Şifrenizi Yenileyin</h3>
        <p class="text-muted fs-7">E-posta bağlantınız onaylandı. Yeni şifrenizi giriniz.</p>
    </div>

    <div class="minimal-card p-4 shadow-sm">
        <?php if (!$valid): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-danger p-3 rounded-3 text-center mb-0">
                <i class="fa-solid fa-triangle-exclamation display-5 mb-2 d-block"></i>
                <h5 class="fw-bold mb-1">Geçersiz veya Süresi Dolmuş Bağlantı!</h5>
                <p class="fs-7 mb-3">Bu şifre sıfırlama e-posta bağlantısı kullanılmış veya geçerliliğini yitirmiş.</p>
                <a href="login.php" class="btn btn-team btn-sm fw-bold">Giriş Sayfasına Dön</a>
            </div>
        <?php else: ?>
            <form onsubmit="handleExecutePasswordReset(event)">
                <input type="hidden" id="resetTokenInput" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <span class="text-muted fs-8 fw-semibold d-block text-uppercase">HESAP E-POSTASI</span>
                    <strong class="text-dark fs-7"><i class="fa-solid fa-envelope text-primary me-1"></i> <?php echo htmlspecialchars($email); ?></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fs-7 fw-semibold">YENİ ŞİFRE *</label>
                    <input type="password" class="form-control" id="newPasswordInput" required placeholder="••••••••" minlength="3">
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted fs-7 fw-semibold">YENİ ŞİFRE (TEKRAR) *</label>
                    <input type="password" class="form-control" id="newPasswordConfirmInput" required placeholder="••••••••" minlength="3">
                </div>

                <button type="submit" class="btn btn-team w-100 py-2.5 fs-6 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> ŞİFREYİ GÜNCELLE VE GİRİŞ YAP
                </button>
            </form>
        <?php endif; ?>
    </div>

</div>

<script>
async function handleExecutePasswordReset(e) {
    e.preventDefault();
    const token = document.getElementById('resetTokenInput').value;
    const p1 = document.getElementById('newPasswordInput').value;
    const p2 = document.getElementById('newPasswordConfirmInput').value;

    if (p1 !== p2) {
        alert('⚠️ Şifreler birbiriyle uyuşmuyor! Lütfen kontrol ediniz.');
        return;
    }

    const res = await fetch('api/auth.php?action=reset_password_with_token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `token=${encodeURIComponent(token)}&new_password=${encodeURIComponent(p1)}`
    });
    const json = await res.json();
    alert(json.message);
    if (json.status === 'success') {
        window.location.href = 'login.php';
    }
}
</script>

</body>
</html>
