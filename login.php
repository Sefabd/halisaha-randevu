<?php
// login.php - SahaNet PRO Account Login, Registration & Password Reset Portal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr" data-team="neutral">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş & Kayıt | SahaNet PRO</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center py-4 min-vh-100 bg-light">

<div class="container max-w-650">
    
    <!-- Brand Header -->
    <div class="text-center mb-3">
        <a href="index.php" class="text-decoration-none">
            <div class="d-inline-flex align-items-center gap-2 brand-badge fs-5 mb-2">
                <i class="fa-solid fa-futbol"></i> SahaNet PRO
            </div>
        </a>
        <h2 class="fw-extrabold text-dark fs-4 mb-1">SPOR TESİSİ REZERVASYON & YÖNETİMİ</h2>
        <p class="text-muted fs-7 mb-0">Giriş yapın veya saniyeler içinde yeni hesabınızı oluşturun.</p>
    </div>

    <!-- Role Selection Tabs -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="role-card active text-center p-3 h-100" id="cardPlayer" onclick="selectRole('player')">
                <div class="role-icon mx-auto mb-2" style="width:40px; height:40px; font-size:1.1rem;">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
                <h4 class="fw-bold text-dark fs-6 mb-0">OYUNCU PORTALI</h4>
                <span class="text-muted fs-8">Randevu al ve kiralama yap</span>
            </div>
        </div>

        <div class="col-6">
            <div class="role-card text-center p-3 h-100" id="cardOwner" onclick="selectRole('owner')">
                <div class="role-icon mx-auto mb-2" style="width:40px; height:40px; font-size:1.1rem;">
                    <i class="fa-solid fa-stadium"></i>
                </div>
                <h4 class="fw-bold text-dark fs-6 mb-0">TESİS İŞLETMECİSİ</h4>
                <span class="text-muted fs-8">Tesisini ve sahalarını yönet</span>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="minimal-card p-4">
        <div id="authAlert" class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger d-none rounded-3 mb-3 fs-7"></div>

        <!-- ==================== OYUNCU SECTION ==================== -->
        <div id="sectionPlayer">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <ul class="nav nav-pills" id="playerTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold py-1 px-3 fs-7" id="player-login-tab" data-bs-toggle="pill" data-bs-target="#player-login" type="button">Oyuncu Girişi</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-1 px-3 fs-7" id="player-register-tab" data-bs-toggle="pill" data-bs-target="#player-register" type="button">Oyuncu Kayıt Ol</button>
                    </li>
                </ul>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fs-8" onclick="fillDemoPlayer()">
                    <i class="fa-solid fa-key me-1"></i> Demo Oyuncu (oyuncu1)
                </button>
            </div>

            <div class="tab-content" id="playerTabContent">
                <!-- Oyuncu Giriş -->
                <div class="tab-pane fade show active" id="player-login">
                    <form onsubmit="handlePlayerLogin(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI *</label>
                                <input type="text" class="form-control" name="username" id="p_login_username" required placeholder="Örn: oyuncu1">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label text-muted fs-7 fw-semibold">ŞİFRE *</label>
                                    <a href="#" class="text-primary text-decoration-none fs-8 fw-semibold" onclick="openForgotPasswordModal()">Şifremi Unuttum?</a>
                                </div>
                                <input type="password" class="form-control" name="password" id="p_login_password" required placeholder="••••••••">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-team w-100 py-2.5 fs-6 fw-bold">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> OYUNCU GİRİŞİ YAP
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Oyuncu Kayıt -->
                <div class="tab-pane fade" id="player-register">
                    <form onsubmit="handlePlayerRegister(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">AD SOYAD *</label>
                                <input type="text" class="form-control" name="full_name" required placeholder="Ahmet Yılmaz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                                <input type="text" class="form-control" name="phone" required placeholder="0532 555 12 34">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI *</label>
                                <input type="text" class="form-control" name="username" required placeholder="Örn: ahmet10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">ŞİFRE *</label>
                                <input type="password" class="form-control" name="password" required placeholder="••••••••">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-team w-100 py-2.5 fs-6 fw-bold">
                                    <i class="fa-solid fa-user-plus me-2"></i> OYUNCU HESABI OLUŞTUR
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ==================== İŞLETME SECTION ==================== -->
        <div id="sectionOwner" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <ul class="nav nav-pills" id="ownerTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold py-1 px-3 fs-7" id="owner-login-tab" data-bs-toggle="pill" data-bs-target="#owner-login" type="button">İşletmeci Girişi</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-1 px-3 fs-7" id="owner-register-tab" data-bs-toggle="pill" data-bs-target="#owner-register" type="button">Tesisini Kaydet</button>
                    </li>
                </ul>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fs-8" onclick="fillDemoOwner()">
                    <i class="fa-solid fa-key me-1"></i> Demo İşletmeci (kadikoy_arena)
                </button>
            </div>

            <div class="tab-content" id="ownerTabContent">
                <!-- İşletme Giriş -->
                <div class="tab-pane fade show active" id="owner-login">
                    <form onsubmit="handleOwnerLogin(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI *</label>
                                <input type="text" class="form-control" name="username" id="o_login_username" required placeholder="Örn: kadikoy_arena">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label text-muted fs-7 fw-semibold">ŞİFRE *</label>
                                    <a href="#" class="text-primary text-decoration-none fs-8 fw-semibold" onclick="openForgotPasswordModal()">Şifremi Unuttum?</a>
                                </div>
                                <input type="password" class="form-control" name="password" id="o_login_password" required placeholder="••••••••">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-team w-100 py-2.5 fs-6 fw-bold">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> İŞLETME PANELİNE GİRİŞ YAP
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- İşletme Kayıt -->
                <div class="tab-pane fade" id="owner-register">
                    <form onsubmit="handleOwnerRegister(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">TESİS ADI *</label>
                                <input type="text" class="form-control" name="facility_name" required placeholder="Örn: Moda Spor Kompleksi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">YETKİLİ AD SOYAD *</label>
                                <input type="text" class="form-control" name="owner_name" required placeholder="Mehmet Kaya">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI *</label>
                                <input type="text" class="form-control" name="username" required placeholder="Örn: moda_spor">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">ŞİFRE *</label>
                                <input type="password" class="form-control" name="password" required placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">İL *</label>
                                <select class="form-select" name="city" id="reg_city" onchange="onRegisterCityChange()" required>
                                    <option value="" disabled selected>İl Seçiniz</option>
                                    <option value="İstanbul">İstanbul</option>
                                    <option value="Ankara">Ankara</option>
                                    <option value="İzmir">İzmir</option>
                                    <option value="Bursa">Bursa</option>
                                    <option value="Antalya">Antalya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">İLÇE *</label>
                                <select class="form-select" name="district" id="reg_district" required>
                                    <option value="" disabled selected>İlçe Seçiniz</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                                <input type="text" class="form-control" name="phone" required placeholder="0532 555 12 34">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fs-7 fw-semibold">ADRES *</label>
                                <input type="text" class="form-control" name="address" required placeholder="Moda Cad. No:12">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-team w-100 py-2.5 fs-6 fw-bold">
                                    <i class="fa-solid fa-stadium me-2"></i> TESİSİNİ KAYDET VE PANELİ AÇ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal: ŞİFREMİ UNUTTUM / ŞİFRE SIFIRLAMA MODALI -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-key text-warning me-2"></i> Şifremi Sıfırla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="handleResetPassword(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">KULLANICI ADI VEYA TELEFON *</label>
                        <input type="text" class="form-control" name="username_or_phone" required placeholder="Örn: oyuncu1 veya 0532...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">YENİ ŞİFRE *</label>
                        <input type="password" class="form-control" name="new_password" required placeholder="Yeni şifreniz">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team fw-bold">Şifreyi Sıfırla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CITIES_DISTRICTS = {
    'İstanbul': ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Beyoğlu', 'Maltepe', 'Ataşehir', 'Ümraniye', 'Bakırköy', 'Fatih', 'Pendik', 'Sarıyer'],
    'Ankara': ['Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Gölbaşı'],
    'İzmir': ['Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Alsancak', 'Çiğli', 'Gaziemir'],
    'Bursa': ['Nilüfer', 'Osmangazi', 'Yıldırım', 'Mudanya'],
    'Antalya': ['Muratpaşa', 'Konyaaltı', 'Kepez', 'Alanya']
};

function onRegisterCityChange() {
    const city = document.getElementById('reg_city').value;
    const distSelect = document.getElementById('reg_district');
    const districts = CITIES_DISTRICTS[city] || [];

    let html = `<option value="" disabled selected>İlçe Seçiniz</option>`;
    html += districts.map(d => `<option value="${d}">${d}</option>`).join('');
    distSelect.innerHTML = html;
}

function selectRole(role) {
    const cardP = document.getElementById('cardPlayer');
    const cardO = document.getElementById('cardOwner');
    const secP = document.getElementById('sectionPlayer');
    const secO = document.getElementById('sectionOwner');

    if (role === 'player') {
        cardP.classList.add('active');
        cardO.classList.remove('active');
        secP.classList.remove('d-none');
        secO.classList.add('d-none');
    } else {
        cardO.classList.add('active');
        cardP.classList.remove('active');
        secO.classList.remove('d-none');
        secP.classList.add('d-none');
    }
}

function fillDemoPlayer() {
    document.getElementById('p_login_username').value = 'oyuncu1';
    document.getElementById('p_login_password').value = '123';
}

function fillDemoOwner() {
    document.getElementById('o_login_username').value = 'kadikoy_arena';
    document.getElementById('o_login_password').value = '123';
}

function showAlert(msg) {
    const box = document.getElementById('authAlert');
    box.classList.remove('d-none');
    box.innerText = msg;
}

function openForgotPasswordModal() {
    new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
}

async function handleResetPassword(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('api/auth.php?action=reset_password', { method: 'POST', body: formData });
    const json = await res.json();
    alert(json.message);
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
    }
}

async function handlePlayerLogin(e) {
    e.preventDefault();
    const u = document.getElementById('p_login_username').value;
    const p = document.getElementById('p_login_password').value;

    const res = await fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(u)}&password=${encodeURIComponent(p)}&role=player`
    });
    const json = await res.json();
    if (json.status === 'success') window.location.href = json.redirect;
    else showAlert(json.message);
}

async function handlePlayerRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/auth.php?action=register', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') window.location.href = json.redirect;
    else showAlert(json.message);
}

async function handleOwnerLogin(e) {
    e.preventDefault();
    const u = document.getElementById('o_login_username').value;
    const p = document.getElementById('o_login_password').value;

    const res = await fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(u)}&password=${encodeURIComponent(p)}&role=owner`
    });
    const json = await res.json();
    if (json.status === 'success') window.location.href = json.redirect;
    else showAlert(json.message);
}

async function handleOwnerRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/auth.php?action=register_owner', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') window.location.href = json.redirect;
    else showAlert(json.message);
}
</script>

</body>
</html>
