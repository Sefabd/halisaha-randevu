<?php
// login.php - SahaNet PRO FIFA Role & Login Portal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | SahaNet PRO - Halı Saha Randevu & Yönetim Platformu</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center py-5 min-vh-100">

<div class="container max-w-900">
    
    <!-- Brand Header -->
    <div class="text-center mb-5">
        <div class="d-inline-flex align-items-center gap-2 brand-logo fs-3 mb-3">
            <i class="fa-solid fa-futbol"></i> SahaNet PRO
        </div>
        <h1 class="display-6 fw-extrabold text-white">PROFESYONEL HALI SAHA PLATFORMU</h1>
        <p class="text-muted fs-6">Lütfen giriş tipinizi seçerek devam ediniz.</p>
    </div>

    <!-- Role Selection Tabs -->
    <div class="row g-4 mb-4">
        <!-- Role 1: Oyuncu / Müşteri -->
        <div class="col-md-6">
            <div class="role-card active text-center h-100" id="cardPlayer" onclick="selectRole('player')">
                <div class="role-icon bg-success bg-opacity-20 text-success mx-auto">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
                <h3 class="fw-bold text-white mb-2">OYUNCU / MÜŞTERİ</h3>
                <p class="text-muted fs-7 mb-0">İl ve ilçe seçerek halı sahaları keşfedin, açık saatleri görüp anında saha veya abonman kiralayın.</p>
            </div>
        </div>

        <!-- Role 2: Halı Saha İşletmecisi -->
        <div class="col-md-6">
            <div class="role-card text-center h-100" id="cardOwner" onclick="selectRole('owner')">
                <div class="role-icon bg-warning bg-opacity-20 text-warning mx-auto">
                    <i class="fa-solid fa-stadium text-warning"></i>
                </div>
                <h3 class="fw-bold text-white mb-2">HALI SAHA İŞLETMECİSİ</h3>
                <p class="text-muted fs-7 mb-0">Sahalarınızı, çalışma saatlerinizi, adres ve iletişim bilgilerinizi yönetin. Yanınıza gelenlere randevu yazın.</p>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="glass-panel p-4 p-md-5">
        
        <!-- Form: Oyuncu Girişi (City/District) -->
        <form id="formPlayer" onsubmit="handlePlayerLogin(event)">
            <h4 class="fw-bold text-white mb-3">
                <i class="fa-solid fa-location-dot text-success me-2"></i> Şehir & İlçe Seçiniz
            </h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fs-7 fw-semibold">İL SEÇİMİ</label>
                    <select class="form-select form-select-lg" name="city" id="playerCity">
                        <option value="İstanbul" selected>İstanbul</option>
                        <option value="Ankara">Ankara</option>
                        <option value="İzmir">İzmir</option>
                        <option value="Bursa">Bursa</option>
                        <option value="Antalya">Antalya</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted fs-7 fw-semibold">İLÇE SEÇİMİ</label>
                    <select class="form-select form-select-lg" name="district" id="playerDistrict">
                        <option value="Kadıköy" selected>Kadıköy</option>
                        <option value="Beşiktaş">Beşiktaş</option>
                        <option value="Beyoğlu">Beyoğlu</option>
                        <option value="Çankaya">Çankaya</option>
                    </select>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-fifa w-100 py-3 fs-5 fw-extrabold">
                        SAHALARI LİSTELE VE RANDEVU AL <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Form: Halı Saha İşletmecisi Girişi -->
        <form id="formOwner" class="d-none" onsubmit="handleOwnerLogin(event)">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-white mb-0">
                    <i class="fa-solid fa-lock text-warning me-2"></i> İşletmeci Yönetim Girişi
                </h4>
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fs-8" onclick="fillDemoOwner()">
                    <i class="fa-solid fa-key me-1"></i> Demo Giriş Bilgisini Doldur (Kadıköy Arena)
                </button>
            </div>

            <div id="loginAlert" class="alert alert-danger bg-danger bg-opacity-25 border border-danger border-opacity-50 text-white d-none rounded-3 mb-3 fs-7"></div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI</label>
                    <input type="text" class="form-control form-control-lg" name="username" id="ownerUsername" required placeholder="Örn: kadikoy_arena">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted fs-7 fw-semibold">ŞİFRE</label>
                    <input type="password" class="form-control form-control-lg" name="password" id="ownerPassword" required placeholder="••••••••">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-warning text-dark w-100 py-3 fs-5 fw-extrabold shadow-lg">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> İŞLETME PANELİNE GİRİŞ YAP
                    </button>
                </div>
            </div>
        </form>

    </div>

</div>

<script>
function selectRole(role) {
    const cardP = document.getElementById('cardPlayer');
    const cardO = document.getElementById('cardOwner');
    const formP = document.getElementById('formPlayer');
    const formO = document.getElementById('formOwner');

    if (role === 'player') {
        cardP.classList.add('active');
        cardO.classList.remove('active');
        formP.classList.remove('d-none');
        formO.classList.add('d-none');
    } else {
        cardO.classList.add('active');
        cardP.classList.remove('active');
        formO.classList.remove('d-none');
        formP.classList.add('d-none');
    }
}

function fillDemoOwner() {
    document.getElementById('ownerUsername').value = 'kadikoy_arena';
    document.getElementById('ownerPassword').value = '123';
}

async function handlePlayerLogin(e) {
    e.preventDefault();
    const city = document.getElementById('playerCity').value;
    const district = document.getElementById('playerDistrict').value;

    const res = await fetch('api/auth.php?action=login_player', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `city=${encodeURIComponent(city)}&district=${encodeURIComponent(district)}`
    });
    const json = await res.json();
    if (json.status === 'success') {
        window.location.href = json.redirect;
    }
}

async function handleOwnerLogin(e) {
    e.preventDefault();
    const u = document.getElementById('ownerUsername').value;
    const p = document.getElementById('ownerPassword').value;
    const alertBox = document.getElementById('loginAlert');

    const res = await fetch('api/auth.php?action=login_owner', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(u)}&password=${encodeURIComponent(p)}`
    });
    const json = await res.json();
    if (json.status === 'success') {
        window.location.href = json.redirect;
    } else {
        alertBox.classList.remove('d-none');
        alertMsg = json.message;
        alertBox.innerText = alertMsg;
    }
}
</script>

</body>
</html>
