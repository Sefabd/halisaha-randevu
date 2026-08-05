<?php
// index.php - SporPin & SosyalHalıSaha Konseptli SahaNet PRO Portalı
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$selected_city = $_SESSION['city'] ?? 'İstanbul';
$selected_district = $_SESSION['district'] ?? 'Kadıköy';
$current_team = $_SESSION['user_team'] ?? 'galatasaray';
$user_name = $_SESSION['user_name'] ?? ($_SESSION['owner_name'] ?? null);
?>
<!DOCTYPE html>
<html lang="tr" data-team="<?php echo htmlspecialchars($current_team); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SahaNet PRO - Online Halı Saha Rezervasyon Platformu</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- 1. SPORPIN STYLE TOP HEADER NAVBAR -->
<header class="minimal-navbar py-3 sticky-top">
    <div class="container px-4 d-flex align-items-center justify-content-between">
        <!-- Logo & Süper Lig Team Selector -->
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="d-flex align-items-center text-decoration-none gap-2">
                <div class="brand-badge fs-5">
                    <i class="fa-solid fa-futbol"></i> SahaNet
                </div>
                <span class="fs-4 fw-extrabold text-dark brand-font">PRO</span>
            </a>

            <!-- Süper Lig Team Switcher -->
            <select class="form-select form-select-sm max-w-130 border-0 bg-light" onchange="switchTeamTheme(this.value)">
                <option value="galatasaray" <?php echo $current_team === 'galatasaray' ? 'selected' : ''; ?>>🟡🔴 GS</option>
                <option value="fenerbahce" <?php echo $current_team === 'fenerbahce' ? 'selected' : ''; ?>>🔵🟡 FB</option>
                <option value="besiktas" <?php echo $current_team === 'besiktas' ? 'selected' : ''; ?>>⬛⚪ BJK</option>
                <option value="trabzonspor" <?php echo $current_team === 'trabzonspor' ? 'selected' : ''; ?>>🟣🔴 TS</option>
                <option value="neutral" <?php echo $current_team === 'neutral' ? 'selected' : ''; ?>>🟢⚪ Nötr</option>
            </select>
        </div>

        <!-- Right Navigation Links -->
        <nav class="d-none d-lg-flex align-items-center gap-4 text-muted fs-7 fw-semibold">
            <a href="index.php" class="text-dark text-decoration-none"><i class="fa-solid fa-house me-1"></i> Ana Sayfa</a>
            <a href="#facilitiesSection" class="text-secondary text-decoration-none"><i class="fa-solid fa-stadium me-1"></i> Sahalar</a>
            <a href="#howItWorks" class="text-secondary text-decoration-none"><i class="fa-solid fa-circle-question me-1"></i> Nasıl Çalışır</a>
        </nav>

        <!-- Auth Button -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($user_name): ?>
                <span class="badge bg-light text-dark border p-2"><i class="fa-solid fa-user text-primary me-1"></i> <?php echo htmlspecialchars($user_name); ?></span>
                <a href="api/auth.php?action=logout" class="btn btn-sm btn-outline-danger rounded-3"><i class="fa-solid fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="login.php" class="btn btn-team btn-sm rounded-3">
                    <i class="fa-solid fa-user me-1"></i> Giriş Yap / Kayıt Ol
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- 2. SPORPIN STYLE BODY HERO SEARCH BANNER WITH 5 CITIES & ALL DISTRICTS -->
<section class="py-5 mb-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
    <div class="container text-center py-4 position-relative" style="z-index: 2;">
        <span class="badge bg-white text-dark rounded-pill px-3 py-2 fs-7 mb-3 fw-bold shadow-sm">
            <i class="fa-solid fa-bolt text-warning me-1"></i> SPORCULARLA HALI SAHALARI BULUŞTURAN PLATFORM!
        </span>
        <h1 class="display-6 fw-extrabold mb-2">Hemen Saha veya Kort Bul, Online Rezervasyon Yap.</h1>
        <p class="text-muted fs-6 mb-4 max-w-700 mx-auto">5 il ve tüm ilçelerinde spor tesislerini filtreleyin, açık saatleri görüp anında randevu alın.</p>

        <!-- YÜZEN 3'LÜ ARAMA KUTUSU -->
        <div class="minimal-card p-3 max-w-950 mx-auto shadow-lg text-dark bg-white">
            <form onsubmit="searchFacilities(event)" class="row g-2 align-items-center">
                
                <!-- 1. SPOR TİPİ -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-futbol text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="searchSportType">
                            <option value="Halı Saha">⚽ Halı Saha</option>
                            <option value="Basketbol Sahası">🏀 Basketbol Sahası</option>
                            <option value="Tenis Kortu">🎾 Tenis Kortu</option>
                            <option value="Voleybol Sahası">🏐 Voleybol Sahası</option>
                        </select>
                    </div>
                </div>

                <!-- 2. DİNAMİK İL SEÇİMİ (5 İL) -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-city text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="portalCity" onchange="onCityChange()">
                            <option value="İstanbul" selected>İstanbul</option>
                            <option value="Ankara">Ankara</option>
                            <option value="İzmir">İzmir</option>
                            <option value="Bursa">Bursa</option>
                            <option value="Antalya">Antalya</option>
                        </select>
                    </div>
                </div>

                <!-- 3. İLE GÖRE DİNAMİK İLÇE SEÇİMİ -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="portalDistrict">
                            <!-- Populated dynamically via JS -->
                        </select>
                    </div>
                </div>

                <!-- 4. SAHA BUL BUTONU -->
                <div class="col-md-3">
                    <button type="submit" class="btn btn-team w-100 py-2.5 fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> SAHA BUL
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container px-4">

    <!-- 3. ALT ALTA TESİSLER LİSTESİ (LEFT: SAHALAR & MATRİS | RIGHT: TESİS BİLGİLERİ VE TESİSE ÖZEL ABONMANLIK) -->
    <section id="facilitiesSection" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0 fs-4">
                    <i class="fa-solid fa-stadium text-primary me-2"></i> Bulunan Spor Tesisleri
                </h4>
                <span class="text-muted fs-7">Saha bilgilerini inceleyin, açık saatlerde randevu alın veya bu tesise özel abonman olun.</span>
            </div>
        </div>

        <!-- Stacked Facilities Container -->
        <div class="d-flex flex-column gap-4" id="stackedFacilitiesList">
            <!-- Loaded dynamically via JS -->
        </div>
    </section>

    <!-- 4. SPORPİN İLHAMLI SİSTEM AVANTAJLARI -->
    <section id="howItWorks" class="mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h2 class="fw-extrabold text-dark display-6 mb-3">SahaNet PRO Nedir?</h2>
                <p class="text-muted fs-6 mb-3">SahaNet PRO; halı saha, tenis kortu, basketbol ve voleybol gibi spor merkezlerinin saniyeler içinde açık saatlerini görüp online randevu ve tesise özel abonman alabildiğiniz nesil bir sistemdir.</p>
                <div class="d-flex align-items-center gap-3 mt-4">
                    <div class="text-center">
                        <h3 class="fw-bold text-primary mb-0">100+</h3>
                        <span class="text-muted fs-7">Kayıtlı Saha</span>
                    </div>
                    <div class="border-end py-3"></div>
                    <div class="text-center">
                        <h3 class="fw-bold text-success mb-0">5.000+</h3>
                        <span class="text-muted fs-7">Tamamlanan Maç</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="minimal-card p-3 h-100">
                            <i class="fa-solid fa-calendar-check text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">Rezervasyon Yönetimi</h6>
                            <p class="text-muted fs-7 mb-0">İşletmeci açık saatleri belirler, oyuncular anında randevu alır.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="minimal-card p-3 h-100">
                            <i class="fa-solid fa-video text-success fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">HD Kamera Kayıt Sistemi</h6>
                            <p class="text-muted fs-7 mb-0">Maçtan hemen sonra maç özetinizi izleyin, gol videolarınızı paylaşın.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="minimal-card p-3 h-100">
                            <i class="fa-solid fa-crown text-warning fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">Tesise Özel Abonmanlık</h6>
                            <p class="text-muted fs-7 mb-0">Sevdiğiniz tesise özel aylık ve sezonluk sabit saat garantili abonman olun.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="minimal-card p-3 h-100">
                            <i class="fa-brands fa-whatsapp text-success fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">WhatsApp Bildirimleri</h6>
                            <p class="text-muted fs-7 mb-0">Randevu detaylarınız anında cep telefonunuza iletilir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Modal: Oyuncu Randevu Alma -->
<div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-futbol text-primary me-2"></i> Halı Saha Randevusu Al</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="playerBookForm" onsubmit="handlePlayerBook(event)">
                <div class="modal-body p-4">
                    <input type="hidden" name="facility_id" id="modalFacId">
                    <input type="hidden" name="field_id" id="modalFieldId">
                    <input type="hidden" name="subscription_plan" id="modalSubPlan" value="Standart">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TAKIM ADINIZ *</label>
                            <input type="text" class="form-control" name="team_name" required placeholder="Örn: Kadıköy Gücü">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">KAPTAN / YETKİLİ AD SOYAD *</label>
                            <input type="text" class="form-control" name="contact_name" required placeholder="Ahmet Yılmaz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON NUMARASI *</label>
                            <input type="text" class="form-control" name="phone" required placeholder="0532 555 12 34">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA SEÇİMİ</label>
                            <input type="text" class="form-control" id="modalFieldName" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TARİH</label>
                            <input type="date" class="form-control" name="reservation_date" id="modalDate" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">SAAT</label>
                            <input type="text" class="form-control" name="reservation_time" id="modalTime" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">ÜCRET (TL)</label>
                            <input type="text" class="form-control" name="fee" id="modalFee" readonly>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="needs_player" id="modalNeedsPlayer" value="1">
                                <label class="form-check-label text-dark fs-7 fw-semibold" for="modalNeedsPlayer">
                                    <i class="fa-solid fa-user-plus me-1 text-primary"></i> Eksik Oyuncu İlanı Yayınlansın
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-team fs-6 fw-bold">Randevuyu Onayla ve Kirala</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: TESİSE ÖZEL ABONMANLIK MODALI -->
<div class="modal fade" id="facilitySubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="facSubModalTitle"><i class="fa-solid fa-crown text-warning me-2"></i> Tesise Özel Abonman Ol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="handleFacilitySubscription(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="facSubId">
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">TESİS ADI</label>
                        <input type="text" class="form-control fw-bold" id="facSubName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">ABONMAN PAKETİ SEÇİN</label>
                        <select class="form-select" id="facSubTierSelect">
                            <option value="Aylık Fix (4.000 TL/Ay)">🔵 Aylık Fix Paket - 4.000 TL / Ay (%10 İndirim + Sabit Saat)</option>
                            <option value="Sezonluk Efsane VIP (21.500 TL/Sezon)" selected>👑 Sezonluk Efsane VIP - 21.500 TL / Sezon (Drone Özet + VIP Garantili)</option>
                            <option value="Kemik Kadro (11.000 TL/3 Ay)">🟡 Kemik Kadro Paket - 11.000 TL / 3 Ay (%15 İndirim + 1 Bedava Maç)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">TAKIM KAPTANI AD SOYAD *</label>
                        <input type="text" class="form-control" id="facSubCaptain" required placeholder="Ahmet Yılmaz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                        <input type="text" class="form-control" id="facSubPhone" required placeholder="0532 555 12 34">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team fw-bold">Abonmanlığı Başlat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 5 İL VE TÜM İLÇELERİ DİNAMİK DICTIONARY
const CITIES_DISTRICTS = {
    'İstanbul': ['Tüm İlçeler', 'Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Beyoğlu', 'Maltepe', 'Ataşehir', 'Ümraniye', 'Bakırköy', 'Fatih', 'Pendik', 'Sarıyer'],
    'Ankara': ['Tüm İlçeler', 'Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Gölbaşı'],
    'İzmir': ['Tüm İlçeler', 'Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Alsancak', 'Çiğli', 'Gaziemir'],
    'Bursa': ['Tüm İlçeler', 'Nilüfer', 'Osmangazi', 'Yıldırım', 'Mudanya'],
    'Antalya': ['Tüm İlçeler', 'Muratpaşa', 'Konyaaltı', 'Kepez', 'Alanya']
};

document.addEventListener('DOMContentLoaded', () => {
    onCityChange('<?php echo htmlspecialchars($selected_district); ?>');
    loadFacilities();
});

function onCityChange(defaultDistrict = null) {
    const city = document.getElementById('portalCity').value;
    const distSelect = document.getElementById('portalDistrict');
    const districts = CITIES_DISTRICTS[city] || ['Tüm İlçeler'];

    distSelect.innerHTML = districts.map(d => `<option value="${d}" ${d === defaultDistrict ? 'selected' : ''}>${d}</option>`).join('');
    loadFacilities();
}

function switchTeamTheme(team) {
    document.documentElement.setAttribute('data-team', team);
    fetch('api/auth.php?action=set_team', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `team=${encodeURIComponent(team)}`
    });
}

let currentFacilities = [];

async function searchFacilities(e) {
    if (e) e.preventDefault();
    await loadFacilities();
}

async function loadFacilities() {
    const city = document.getElementById('portalCity').value;
    const districtSelect = document.getElementById('portalDistrict').value;
    const district = (districtSelect === 'Tüm İlçeler') ? '' : districtSelect;

    const res = await fetch(`api/facility.php?action=list_public&city=${encodeURIComponent(city)}&district=${encodeURIComponent(district)}`);
    const json = await res.json();

    const listContainer = document.getElementById('stackedFacilitiesList');
    if (json.status === 'success') {
        currentFacilities = json.data;

        if (currentFacilities.length === 0) {
            listContainer.innerHTML = `<div class="minimal-card p-5 text-center text-muted">Seçilen il/ilçede kayıtlı tesis bulunamadı. Lütfen başka bir il veya ilçe seçiniz.</div>`;
            return;
        }

        const todayStr = new Date().toISOString().split('T')[0];

        let html = '';
        currentFacilities.forEach((fac) => {
            const openH = parseInt(fac.open_time || '13');
            let closeH = parseInt(fac.close_time || '01');
            if (closeH <= openH) closeH += 24;

            const hours = [];
            for (let h = openH; h < closeH; h++) {
                const realH = h % 24;
                hours.push((realH < 10 ? '0' : '') + realH + ':00');
            }

            html += `<div class="minimal-card p-4">
                <div class="row g-4">
                    
                    <!-- LEFT COLUMN (70%): SAHALAR, AÇIK SAATLER VE MATRİS -->
                    <div class="col-lg-8 border-end border-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="fw-bold text-dark fs-4 mb-0"><i class="fa-solid fa-stadium text-primary me-2"></i>${escapeHtml(fac.name)}</h4>
                            <span class="badge bg-light text-dark border"><i class="fa-solid fa-clock me-1 text-primary"></i>Açık Saatler: ${fac.open_time} - ${fac.close_time}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            ${fac.fields.map(f => `<span class="badge bg-light text-dark border fs-8"><i class="fa-solid fa-futbol text-success me-1"></i>${escapeHtml(f.field_name)}</span>`).join('')}
                        </div>

                        <!-- SAAT MATRİSİ -->
                        <div class="table-responsive">
                            <table class="table table-borderless text-center align-middle m-0 fs-8">
                                <thead>
                                    <tr class="text-muted border-bottom">
                                        <th class="text-start">SAHA</th>
                                        ${hours.map(h => `<th>${h}</th>`).join('')}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${fac.fields.map(field => `
                                        <tr>
                                            <td class="fw-bold text-dark text-start py-2">${escapeHtml(field.field_name)}</td>
                                            ${hours.map(h => `
                                                <td>
                                                    <div class="slot-badge slot-free" onclick="openPlayerBookModal(${fac.id}, ${field.id}, '${escapeHtml(field.field_name)}', '${todayStr}', '${h}', ${field.hourly_fee})">
                                                        +${h}
                                                    </div>
                                                </td>
                                            `).join('')}
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN (30%): ADRES, İLETİŞİM VE TESİSE ÖZEL ABONMAN OL BUTONU -->
                    <div class="col-lg-4 d-flex flex-column justify-content-between ps-lg-4">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold mb-2">${escapeHtml(fac.city)} / ${escapeHtml(fac.district)}</span>
                            <p class="text-muted fs-7 mb-2"><i class="fa-solid fa-location-dot text-danger me-2"></i>${escapeHtml(fac.address)}</p>
                            <p class="text-muted fs-7 mb-3"><i class="fa-solid fa-phone text-success me-2"></i>${escapeHtml(fac.phone)}</p>
                        </div>
                        <div>
                            <button class="btn btn-team w-100 py-2.5 fw-bold mb-2" onclick="openFacilitySubModal(${fac.id}, '${escapeHtml(fac.name)}')">
                                <i class="fa-solid fa-crown me-1"></i> TESİSE ÖZEL ABONMAN OL
                            </button>
                            <span class="text-muted fs-8 d-block text-center"><i class="fa-solid fa-shield-halved text-success me-1"></i> Sabit Gün & Saat Garantili</span>
                        </div>
                    </div>

                </div>
            </div>`;
        });

        listContainer.innerHTML = html;
    }
}

function openPlayerBookModal(facId, fieldId, fieldName, date, time, fee) {
    document.getElementById('modalFacId').value = facId;
    document.getElementById('modalFieldId').value = fieldId;
    document.getElementById('modalFieldName').value = fieldName;
    document.getElementById('modalDate').value = date;
    document.getElementById('modalTime').value = time;
    document.getElementById('modalFee').value = fee;
    document.getElementById('modalSubPlan').value = 'Standart';

    new bootstrap.Modal(document.getElementById('playerModal')).show();
}

function openFacilitySubModal(facId, facName) {
    document.getElementById('facSubId').value = facId;
    document.getElementById('facSubName').value = facName;
    new bootstrap.Modal(document.getElementById('facilitySubModal')).show();
}

function handleFacilitySubscription(e) {
    e.preventDefault();
    const facName = document.getElementById('facSubName').value;
    const tier = document.getElementById('facSubTierSelect').value;
    const captain = document.getElementById('facSubCaptain').value;

    alert(`🎉 TEBRİKLER!\n${facName} tesisi için [${tier}] abonmanlık talebiniz oluşturuldu.\nKaptan: ${captain}\nİşletmeci sizinle en kısa sürede iletişime geçecektir.`);
    bootstrap.Modal.getInstance(document.getElementById('facilitySubModal')).hide();
}

async function handlePlayerBook(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/reservations.php?action=save', { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
        alert('🎉 Randevunuz başarıyla oluşturuldu!');
        bootstrap.Modal.getInstance(document.getElementById('playerModal')).hide();
        loadFacilities();
    } else {
        alert(json.message);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
</script>

</body>
</html>
