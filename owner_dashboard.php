<?php
// owner_dashboard.php - Tesis İşletmecisi Paneli (Logo, Temiz Tablo & Renkli Saat Matrisi)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: login.php');
    exit;
}

$facility_name = $_SESSION['facility_name'] ?? 'Tesisim';
$owner_name = $_SESSION['owner_name'] ?? 'İşletmeci';
$current_team = $_SESSION['user_team'] ?? 'neutral';
?>
<!DOCTYPE html>
<html lang="tr" data-team="<?php echo htmlspecialchars($current_team); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($facility_name); ?> - Tesis Paneli</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Header Navbar with Official Brand Logo -->
<header class="minimal-navbar py-3 mb-4">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="owner_dashboard.php" class="d-flex align-items-center text-decoration-none gap-2">
                <div class="brand-badge fs-5">
                    <i class="fa-solid fa-futbol"></i> SahaNet
                </div>
                <span class="fs-4 fw-extrabold text-dark brand-font">PRO</span>
            </a>
            <div class="border-start ps-3 d-none d-md-block">
                <h4 class="fw-bold text-dark mb-0 fs-6"><?php echo htmlspecialchars($facility_name); ?></h4>
                <span class="text-muted fs-8"><i class="fa-solid fa-circle-check text-success me-1"></i> <?php echo htmlspecialchars($owner_name); ?></span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Team Quick Switcher -->
            <select class="form-select form-select-sm max-w-130 border-0 bg-light" onchange="switchTeamTheme(this.value)">
                <option value="galatasaray" <?php echo $current_team === 'galatasaray' ? 'selected' : ''; ?>>🟡🔴 GS</option>
                <option value="fenerbahce" <?php echo $current_team === 'fenerbahce' ? 'selected' : ''; ?>>🔵🟡 FB</option>
                <option value="besiktas" <?php echo $current_team === 'besiktas' ? 'selected' : ''; ?>>⬛⚪ BJK</option>
                <option value="trabzonspor" <?php echo $current_team === 'trabzonspor' ? 'selected' : ''; ?>>🟣🔴 TS</option>
                <option value="neutral" <?php echo $current_team === 'neutral' ? 'selected' : ''; ?>>🟢⚪ Yeşil</option>
            </select>

            <button class="btn btn-team btn-sm" data-bs-toggle="modal" data-bs-target="#walkinModal">
                <i class="fa-solid fa-plus-circle me-1"></i> Elden Hızlı Randevu Ekle
            </button>
            <a href="api/auth.php?action=logout" class="btn btn-outline-danger btn-sm rounded-3">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Çıkış
            </a>
        </div>
    </div>
</header>

<div class="container-fluid px-4">
    
    <!-- Top Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="minimal-card p-3">
                <div class="text-muted fs-8 fw-bold">TOPLAM RANDEVUM</div>
                <div class="fs-3 fw-extrabold text-dark mt-1" id="ownerStatTotal">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="minimal-card p-3">
                <div class="text-muted fs-8 fw-bold">BUGÜNKÜ RANDEVUM</div>
                <div class="fs-3 fw-extrabold text-warning mt-1" id="ownerStatToday">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="minimal-card p-3">
                <div class="text-muted fs-8 fw-bold">ONAYLANAN</div>
                <div class="fs-3 fw-extrabold text-success mt-1" id="ownerStatApproved">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="minimal-card p-3">
                <div class="text-muted fs-8 fw-bold">BUGÜNKÜ KAZANÇ</div>
                <div class="fs-3 fw-extrabold text-primary mt-1" id="ownerStatIncome">0 ₺</div>
            </div>
        </div>
    </div>

    <!-- RENKLİ SAAT MATRİSİ PANENLİ (🟢 Yeşil: Boş | 🔴 Kırmızı: Alınan Normal | 🟡 Sarı: Abonmanlı) -->
    <section class="minimal-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calendar-days text-primary me-2"></i> Canlı Saatlik Doluluk Matrisi</h5>
                <span class="text-muted fs-8">Saha durumlarını renklerle takip edin. Üzerlerine tıklayarak detay görün veya hızlı randevu yazın.</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Color Legend -->
                <div class="d-flex align-items-center gap-2 fs-8">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">🟢 Boş (Elden Kayıt)</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">🔴 Alınan Randevu</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">🟡 Abonmanlı</span>
                </div>
                <input type="date" class="form-control form-control-sm max-w-150" id="matrixDate" onchange="loadOwnerFacility()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless text-center align-middle m-0 fs-8">
                <thead class="border-bottom text-muted">
                    <tr id="ownerMatrixHeader"></tr>
                </thead>
                <tbody id="ownerMatrixBody"></tbody>
            </table>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <!-- 1. TESİS AYARLARI VE DİNAMİK İL/İLÇE DROPDOWN -->
        <div class="col-lg-5">
            <div class="minimal-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Tesis & Çalışma Saatleri
                </h5>

                <form id="facilityProfileForm" onsubmit="saveFacilityProfile(event)">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">TESİS ADI</label>
                            <input type="text" class="form-control" name="name" id="fac_name" required>
                        </div>

                        <!-- 5 İL VE TÜM İLÇELERİ DROPDOWN (SERBEST METİN GİRİŞİ YERİNE) -->
                        <div class="col-6">
                            <label class="form-label text-muted fs-7 fw-semibold">İL SEÇİMİ</label>
                            <select class="form-select" name="city" id="fac_city" onchange="onOwnerCityChange()">
                                <option value="İstanbul">İstanbul</option>
                                <option value="Ankara">Ankara</option>
                                <option value="İzmir">İzmir</option>
                                <option value="Bursa">Bursa</option>
                                <option value="Antalya">Antalya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-7 fw-semibold">İLÇE SEÇİMİ</label>
                            <select class="form-select" name="district" id="fac_district"></select>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">ADRES</label>
                            <textarea class="form-control" name="address" id="fac_address" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON</label>
                            <input type="text" class="form-control" name="phone" id="fac_phone" required>
                        </div>
                        
                        <!-- Çalışma Saatleri -->
                        <div class="col-6">
                            <label class="form-label text-primary fs-7 fw-bold">AÇILIŞ SAATİ</label>
                            <select class="form-select" name="open_time" id="fac_open_time">
                                <option value="08:00">08:00</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="12:00">12:00</option>
                                <option value="13:00" selected>13:00</option>
                                <option value="14:00">14:00</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-primary fs-7 fw-bold">KAPANIŞ SAATİ</label>
                            <select class="form-select" name="close_time" id="fac_close_time">
                                <option value="22:00">22:00</option>
                                <option value="23:00">23:00</option>
                                <option value="00:00">00:00</option>
                                <option value="01:00" selected>01:00</option>
                                <option value="02:00">02:00</option>
                                <option value="03:00">03:00</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-team w-100 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Ayarları Kaydet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. SAHALARIM YÖNETİM KARTI -->
        <div class="col-lg-7">
            <div class="minimal-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-vector-square text-success me-2"></i> Tesis Sahalarım
                        </h5>
                        <span class="text-muted fs-7">Sahalarınız ve ücret tanımları</span>
                    </div>
                    <button class="btn btn-team btn-sm" onclick="openAddFieldModal()">
                        <i class="fa-solid fa-plus me-1"></i> Yeni Saha Ekle
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0 fs-7">
                        <thead class="bg-light text-muted border-bottom">
                            <tr>
                                <th>SAHA ADI</th>
                                <th>TİPİ</th>
                                <th>SAATLİK ÜCRET</th>
                                <th class="text-end">İŞLEMLER</th>
                            </tr>
                        </thead>
                        <tbody id="ownerFieldsList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KENDİ SAHALARIMIN RANDEVU LİSTESİ (TEMİZ GÖRSEL TABLO REVİZYONU - SCREENSHOT 5 FIX) -->
    <section class="minimal-card p-4 mb-5">
        <h4 class="fw-bold text-dark mb-3 fs-5"><i class="fa-solid fa-list-check text-primary me-2"></i> İşletme Randevu Listesi</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0 fs-7">
                <thead class="table-light text-muted border-bottom">
                    <tr>
                        <th class="py-3">TAKIM ADI</th>
                        <th class="py-3">YETKİLİ KİŞİ</th>
                        <th class="py-3">TELEFON</th>
                        <th class="py-3">SAHA</th>
                        <th class="py-3">TARİH</th>
                        <th class="py-3">SAAT</th>
                        <th class="py-3">ÜCRET</th>
                        <th class="py-3">DURUM</th>
                        <th class="py-3 text-end">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody id="ownerReservationsBody"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal: RANDEVU DETAY POPUP (DOLU SAATE TIKLANINCA AÇILIR) -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="detailTitle">Randevu Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 fs-7" id="detailContent"></div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Saha Ekle / Düzenle -->
<div class="modal fade" id="fieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="fieldModalTitle">Yeni Saha Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="fieldForm" onsubmit="saveField(event)">
                <div class="modal-body p-4">
                    <input type="hidden" name="field_id" id="modal_field_id" value="0">
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">SAHA ADI *</label>
                        <input type="text" class="form-control" name="field_name" id="modal_field_name" required placeholder="Örn: Futbol Sahası 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">SAHA TİPİ</label>
                        <select class="form-select" name="field_type" id="modal_field_type">
                            <option value="Kapalı Futbol Sahası">⚽ Kapalı Futbol Sahası</option>
                            <option value="Açık Futbol Sahası">⚽ Açık Futbol Sahası</option>
                            <option value="Kapalı Basketbol Sahası">🏀 Kapalı Basketbol Sahası</option>
                            <option value="Açık Tenis Kortu">🎾 Açık Tenis Kortu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">SAATLİK ÜCRET (TL) *</label>
                        <input type="number" step="0.01" class="form-control" name="hourly_fee" id="modal_hourly_fee" required value="1200.00">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Walk-in / Elden Hızlı Randevu Ekle -->
<div class="modal fade" id="walkinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-success me-2"></i> Hızlı Randevu Kaydı (İşletmeci)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="walkinForm" onsubmit="saveWalkinReservation(event)">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TAKIM ADI *</label>
                            <input type="text" class="form-control" name="team_name" required placeholder="Örn: Karaköy Gücü">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">YETKİLİ KİŞİ *</label>
                            <input type="text" class="form-control" name="contact_name" required placeholder="Ad Soyad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                            <input type="text" class="form-control" name="phone" required placeholder="05XX XXX XX XX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA SEÇİMİ *</label>
                            <select class="form-select" name="field_id" id="walkinFieldSelect" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TARİH *</label>
                            <input type="date" class="form-control" name="reservation_date" id="walkinDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">SAAT *</label>
                            <select class="form-select" name="reservation_time" id="walkinTimeSelect" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">ÜCRET (TL) *</label>
                            <input type="number" step="0.01" class="form-control" name="fee" value="1200.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">DURUM *</label>
                            <select class="form-select" name="status">
                                <option value="Onaylandı" selected>✅ Onaylandı</option>
                                <option value="Tamamlandı">🏆 Tamamlandı</option>
                                <option value="Bekliyor">⏳ Bekliyor</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team">Randevuyu Kaydet</button>
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

document.addEventListener('DOMContentLoaded', () => {
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('matrixDate').value = todayStr;
    document.getElementById('walkinDate').value = todayStr;

    loadOwnerFacility();
    loadOwnerReservations();
});

function switchTeamTheme(team) {
    document.documentElement.setAttribute('data-team', team);
    fetch('api/auth.php?action=set_team', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `team=${encodeURIComponent(team)}`
    });
}

function onOwnerCityChange(defaultDistrict = null) {
    const city = document.getElementById('fac_city').value;
    const distSelect = document.getElementById('fac_district');
    const districts = CITIES_DISTRICTS[city] || ['Kadıköy'];

    distSelect.innerHTML = districts.map(d => `<option value="${d}" ${d === defaultDistrict ? 'selected' : ''}>${d}</option>`).join('');
}

let ownerFieldsData = [];
let ownerFacilityData = null;
let ownerReservationsData = [];

async function loadOwnerFacility() {
    const res = await fetch('api/facility.php?action=get_owner_facility');
    const json = await res.json();

    if (json.status === 'success') {
        ownerFacilityData = json.facility;
        ownerFieldsData = json.fields;

        document.getElementById('fac_name').value = json.facility.name;
        document.getElementById('fac_city').value = json.facility.city;
        onOwnerCityChange(json.facility.district);
        document.getElementById('fac_address').value = json.facility.address;
        document.getElementById('fac_phone').value = json.facility.phone;
        document.getElementById('fac_open_time').value = json.facility.open_time || '13:00';
        document.getElementById('fac_close_time').value = json.facility.close_time || '01:00';

        renderOwnerFields(json.fields);
        populateWalkinSelects(json.facility, json.fields);
        renderOwnerMatrix();
    }
}

function renderOwnerFields(fields) {
    const tbody = document.getElementById('ownerFieldsList');
    if (fields.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Henüz eklenmiş saha yok.</td></tr>`;
        return;
    }

    let html = '';
    fields.forEach(f => {
        html += `<tr>
            <td class="fw-bold text-dark"><i class="fa-solid fa-futbol text-success me-2"></i>${escapeHtml(f.field_name)}</td>
            <td><span class="badge bg-light text-dark border">${escapeHtml(f.field_type)}</span></td>
            <td class="fw-bold text-dark">₺${parseFloat(f.hourly_fee).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-info me-1" onclick="editField(${f.id}, '${escapeHtml(f.field_name)}', '${escapeHtml(f.field_type)}', ${f.hourly_fee})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteField(${f.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function renderOwnerMatrix() {
    if (!ownerFacilityData || ownerFieldsData.length === 0) return;
    const date = document.getElementById('matrixDate').value;

    const openH = parseInt(ownerFacilityData.open_time || '13');
    let closeH = parseInt(ownerFacilityData.close_time || '01');
    if (closeH <= openH) closeH += 24;

    const hours = [];
    for (let h = openH; h < closeH; h++) {
        const realH = h % 24;
        hours.push((realH < 10 ? '0' : '') + realH + ':00');
    }

    const header = document.getElementById('ownerMatrixHeader');
    let hHtml = `<th class="text-start">SAHA ADI</th>`;
    hours.forEach(h => hHtml += `<th>${h}</th>`);
    header.innerHTML = hHtml;

    const tbody = document.getElementById('ownerMatrixBody');
    let bHtml = '';

    ownerFieldsData.forEach(field => {
        bHtml += `<tr><td class="fw-bold text-dark text-start py-2">${escapeHtml(field.field_name)}</td>`;

        hours.forEach(h => {
            const booking = ownerReservationsData.find(r => r.field_id == field.id && r.reservation_date === date && r.reservation_time === h && r.status !== 'İptal');

            if (booking) {
                const isSub = booking.subscription_plan && booking.subscription_plan !== 'Standart';
                const badgeClass = isSub ? 'slot-busy-sub' : 'slot-busy-normal';
                const icon = isSub ? 'fa-crown' : 'fa-lock';

                bHtml += `<td>
                    <div class="slot-badge ${badgeClass}" onclick="showBookingDetails(${booking.id})" title="Detay için tıkla">
                        <i class="fa-solid ${icon} me-1"></i>${h}
                    </div>
                </td>`;
            } else {
                bHtml += `<td>
                    <div class="slot-badge slot-free" onclick="quickWalkinModal(${field.id}, '${h}')" title="Hızlı Elden Kayıt">
                        +${h}
                    </div>
                </td>`;
            }
        });

        bHtml += `</tr>`;
    });

    tbody.innerHTML = bHtml;
}

function showBookingDetails(id) {
    const r = ownerReservationsData.find(item => item.id == id);
    if (!r) return;

    document.getElementById('detailTitle').innerText = `Randevu #${r.id}`;
    document.getElementById('detailContent').innerHTML = `
        <p class="mb-1"><strong>Takım Adı:</strong> ${escapeHtml(r.team_name)}</p>
        <p class="mb-1"><strong>Yetkili:</strong> ${escapeHtml(r.contact_name)}</p>
        <p class="mb-1"><strong>Telefon:</strong> ${escapeHtml(r.phone)}</p>
        <p class="mb-1"><strong>Saha:</strong> ${escapeHtml(r.field_name)}</p>
        <p class="mb-1"><strong>Tarih / Saat:</strong> ${r.reservation_date} - ${r.reservation_time}</p>
        <p class="mb-1"><strong>Ücret:</strong> ₺${parseFloat(r.fee).toLocaleString('tr-TR')}</p>
        <p class="mb-0"><strong>Paket:</strong> <span class="badge bg-warning text-dark">${escapeHtml(r.subscription_plan)}</span></p>
    `;

    new bootstrap.Modal(document.getElementById('reservationDetailsModal')).show();
}

function quickWalkinModal(fieldId, time) {
    document.getElementById('walkinFieldSelect').value = fieldId;
    document.getElementById('walkinTimeSelect').value = time;
    new bootstrap.Modal(document.getElementById('walkinModal')).show();
}

function populateWalkinSelects(facility, fields) {
    const fieldSel = document.getElementById('walkinFieldSelect');
    fieldSel.innerHTML = fields.map(f => `<option value="${f.id}">${escapeHtml(f.field_name)}</option>`).join('');

    const timeSel = document.getElementById('walkinTimeSelect');
    const openH = parseInt(facility.open_time || '13');
    let closeH = parseInt(facility.close_time || '01');
    if (closeH <= openH) closeH += 24;

    let timeOptions = '';
    for (let h = openH; h < closeH; h++) {
        const realH = h % 24;
        const formatted = (realH < 10 ? '0' : '') + realH + ':00';
        timeOptions += `<option value="${formatted}">${formatted} - ${(realH+1)%24}:00</option>`;
    }
    timeSel.innerHTML = timeOptions;
}

async function saveFacilityProfile(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/facility.php?action=update_profile', { method: 'POST', body: formData });
    const json = await res.json();
    alert(json.message);
    if (json.status === 'success') loadOwnerFacility();
}

function openAddFieldModal() {
    document.getElementById('modal_field_id').value = '0';
    document.getElementById('modal_field_name').value = '';
    document.getElementById('fieldModalTitle').innerText = 'Yeni Saha Ekle';
    new bootstrap.Modal(document.getElementById('fieldModal')).show();
}

function editField(id, name, type, fee) {
    document.getElementById('modal_field_id').value = id;
    document.getElementById('modal_field_name').value = name;
    document.getElementById('modal_field_type').value = type;
    document.getElementById('modal_hourly_fee').value = fee;
    document.getElementById('fieldModalTitle').innerText = 'Sahayı Düzenle';
    new bootstrap.Modal(document.getElementById('fieldModal')).show();
}

async function saveField(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/facility.php?action=save_field', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('fieldModal')).hide();
        loadOwnerFacility();
    } else {
        alert(json.message);
    }
}

async function deleteField(id) {
    if (!confirm('Bu sahayı silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('field_id', id);

    const res = await fetch('api/facility.php?action=delete_field', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') loadOwnerFacility();
}

async function loadOwnerReservations() {
    const res = await fetch('api/reservations.php?action=list');
    const json = await res.json();
    const tbody = document.getElementById('ownerReservationsBody');

    if (json.status === 'success') {
        ownerReservationsData = json.data;
        renderOwnerMatrix();

        let total = json.data.length;
        let todayCount = 0;
        let approvedCount = 0;
        let income = 0;
        const todayStr = new Date().toISOString().split('T')[0];

        let html = '';
        json.data.forEach(r => {
            if (r.reservation_date === todayStr) {
                todayCount++;
                if (r.status === 'Onaylandı' || r.status === 'Tamamlandı') income += parseFloat(r.fee);
            }
            if (r.status === 'Onaylandı') approvedCount++;

            html += `<tr>
                <td class="fw-bold text-dark px-3 py-3">${escapeHtml(r.team_name)}</td>
                <td class="px-3 py-3">${escapeHtml(r.contact_name)}</td>
                <td class="px-3 py-3">${escapeHtml(r.phone)}</td>
                <td class="px-3 py-3"><span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(r.field_name)}</span></td>
                <td class="px-3 py-3 text-primary fw-semibold">${r.reservation_date}</td>
                <td class="px-3 py-3 text-dark fw-bold">${r.reservation_time}</td>
                <td class="px-3 py-3 text-success fw-bold">₺${parseFloat(r.fee).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
                <td class="px-3 py-3">${getStatusBadge(r.status)}</td>
                <td class="px-3 py-3 text-end">
                    <button class="btn btn-sm btn-outline-danger" onclick="cancelReservation(${r.id})">İptal Et / Sil</button>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html || `<tr><td colspan="9" class="text-center text-muted py-4">Tesisinize ait henüz randevu yok.</td></tr>`;

        document.getElementById('ownerStatTotal').innerText = total;
        document.getElementById('ownerStatToday').innerText = todayCount;
        document.getElementById('ownerStatApproved').innerText = approvedCount;
        document.getElementById('ownerStatIncome').innerText = income.toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ₺';
    }
}

async function saveWalkinReservation(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/reservations.php?action=save', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('walkinModal')).hide();
        loadOwnerReservations();
    } else {
        alert(json.message);
    }
}

async function cancelReservation(id) {
    if (!confirm('Randevuyu silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('id', id);

    const res = await fetch('api/reservations.php?action=delete', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') loadOwnerReservations();
}

function getStatusBadge(status) {
    switch (status) {
        case 'Onaylandı': return `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Onaylandı</span>`;
        case 'Tamamlandı': return `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">Tamamlandı</span>`;
        case 'İptal': return `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">İptal</span>`;
        default: return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">Bekliyor</span>`;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
</script>

</body>
</html>
