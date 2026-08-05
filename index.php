<?php
// index.php - SporNet PRO Online Spor Tesisleri Kiralama Portalı
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$selected_city = $_SESSION['city'] ?? '';
$selected_district = $_SESSION['district'] ?? '';
$is_logged_in = isset($_SESSION['user_role']);
$current_team = $is_logged_in ? ($_SESSION['user_team'] ?? 'neutral') : 'neutral';
$user_name = $_SESSION['user_name'] ?? ($_SESSION['owner_name'] ?? null);
$user_name_upper = $user_name ? mb_strtoupper($user_name, 'UTF-8') : '';
$today_str = date('Y-m-d');
$current_hour = (int)date('H');
?>
<!DOCTYPE html>
<html lang="tr" data-team="<?php echo htmlspecialchars($current_team); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SahaNet PRO - Online Spor Tesisleri Kiralama Portalı</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- 1. CLEAN HEADER NAVBAR -->
<header class="minimal-navbar py-3 sticky-top">
    <div class="container px-4 d-flex align-items-center justify-content-between">
        <!-- Logo -->
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="d-flex align-items-center text-decoration-none gap-2">
                <div class="brand-badge fs-5">
                    <i class="fa-solid fa-futbol"></i> SahaNet
                </div>
                <span class="fs-4 fw-extrabold text-dark brand-font">PRO</span>
            </a>
        </div>

        <!-- Clean Navigation Links -->
        <nav class="d-none d-lg-flex align-items-center gap-4 text-muted fs-7 fw-semibold">
            <a href="index.php" class="text-dark text-decoration-none"><i class="fa-solid fa-house me-1"></i> Ana Sayfa</a>
            <a href="#facilitiesSection" class="text-secondary text-decoration-none"><i class="fa-solid fa-stadium me-1"></i> Sahalar & Tesisler</a>
        </nav>

        <!-- Auth Button / Logged-in Team Selector -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($user_name): ?>
                <!-- Team Quick Switcher (Logged-in only) -->
                <select class="form-select form-select-sm max-w-130 border-0 bg-light" onchange="switchTeamTheme(this.value)">
                    <option value="galatasaray" <?php echo $current_team === 'galatasaray' ? 'selected' : ''; ?>>🟡🔴 GS</option>
                    <option value="fenerbahce" <?php echo $current_team === 'fenerbahce' ? 'selected' : ''; ?>>🔵🟡 FB</option>
                    <option value="besiktas" <?php echo $current_team === 'besiktas' ? 'selected' : ''; ?>>⬛⚪ BJK</option>
                    <option value="trabzonspor" <?php echo $current_team === 'trabzonspor' ? 'selected' : ''; ?>>🟣🔴 TS</option>
                    <option value="neutral" <?php echo $current_team === 'neutral' ? 'selected' : ''; ?>>🟢⚪ Yeşil</option>
                </select>

                <!-- Clean User Name Badge -->
                <span class="badge bg-light text-dark border p-2"><i class="fa-solid fa-user text-primary me-1"></i> <?php echo htmlspecialchars($user_name_upper); ?></span>
                <a href="api/auth.php?action=logout" class="btn btn-sm btn-outline-danger rounded-3" title="Çıkış Yap"><i class="fa-solid fa-right-from-bracket"></i> Çıkış</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-team btn-sm rounded-3">
                    <i class="fa-solid fa-user me-1"></i> Giriş Yap / Kayıt Ol
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- 2. HERO SEARCH BANNER WITH 4-COLUMN SEARCH BAR -->
<section class="py-5 mb-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
    <div class="container text-center py-4 position-relative" style="z-index: 2;">
        <span class="badge bg-white text-dark rounded-pill px-3 py-2 fs-7 mb-3 fw-bold shadow-sm">
            <i class="fa-solid fa-bolt text-warning me-1"></i> SPORCULARLA TESİSLERİ BULUŞTURAN PLATFORM
        </span>
        <h1 class="display-6 fw-extrabold mb-2">Hemen Saha Bul, Online Rezervasyon Yap.</h1>
        <p class="fs-6 mb-4 max-w-700 mx-auto fw-medium" style="color: #cbd5e1;">İl ve ilçe seçip "SAHA BUL" butonuna basarak spor tesislerini listeleyin.</p>

        <!-- YÜZEN ARAMA KUTUSU (4 ANA KOLON) -->
        <div class="minimal-card p-4 max-w-1000 mx-auto shadow-lg text-dark bg-white">
            <form onsubmit="executeSearch(event)" class="row g-3 align-items-center">
                
                <!-- 1. SPOR TİPİ -->
                <div class="col-md-3">
                    <label class="form-label text-muted fs-8 fw-bold mb-1 text-uppercase text-start d-block">SPOR TİPİ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-futbol text-primary"></i></span>
                        <select class="form-select border-0 bg-light fw-bold text-dark" id="searchSportType">
                            <option value="Halı Saha" selected>⚽ Halı Saha (Futbol)</option>
                            <option value="Basketbol Sahası">🏀 Basketbol Sahası</option>
                            <option value="Tenis Kortu">🎾 Tenis Kortu</option>
                            <option value="Voleybol Sahası">🏐 Voleybol Sahası</option>
                        </select>
                    </div>
                </div>

                <!-- 2. İL SEÇİMİ -->
                <div class="col-md-3">
                    <label class="form-label text-muted fs-8 fw-bold mb-1 text-uppercase text-start d-block">İL SEÇİMİ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-city text-primary"></i></span>
                        <select class="form-select border-0 bg-light fw-bold text-dark" id="portalCity" onchange="onCityChange()">
                            <option value="" disabled selected>İl Seçiniz</option>
                            <option value="İstanbul">İstanbul</option>
                            <option value="Ankara">Ankara</option>
                            <option value="İzmir">İzmir</option>
                            <option value="Bursa">Bursa</option>
                            <option value="Antalya">Antalya</option>
                        </select>
                    </div>
                </div>

                <!-- 3. İLÇE SEÇİMİ -->
                <div class="col-md-3">
                    <label class="form-label text-muted fs-8 fw-bold mb-1 text-uppercase text-start d-block">İLÇE SEÇİMİ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                        <select class="form-select border-0 bg-light fw-bold text-dark" id="portalDistrict">
                            <option value="" disabled selected>İlçe Seçiniz</option>
                        </select>
                    </div>
                </div>

                <!-- 4. SAHA TİPİ -->
                <div class="col-md-3">
                    <label class="form-label text-muted fs-8 fw-bold mb-1 text-uppercase text-start d-block">SAHA TİPİ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-building text-primary"></i></span>
                        <select class="form-select border-0 bg-light fw-bold text-dark" id="filterFieldCover">
                            <option value="Tümü" selected>Tüm Sahalar</option>
                            <option value="Kapalı">🏢 Kapalı Saha</option>
                            <option value="Açık">☀️ Açık Saha</option>
                        </select>
                    </div>
                </div>

                <!-- SAHA BUL BUTONU VE FİLTRE PILL'LERİ -->
                <div class="col-12 border-top pt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 fs-7">
                            <div class="filter-pill" id="pillCamera" onclick="togglePill('pillCamera')">
                                <i class="fa-solid fa-video me-1"></i> HD Kamera Kaydı
                            </div>
                            <div class="filter-pill" id="pillWater" onclick="togglePill('pillWater')">
                                <i class="fa-solid fa-bottle-water me-1"></i> Ücretsiz Su & İkram
                            </div>
                            <div class="filter-pill" id="pillShower" onclick="togglePill('pillShower')">
                                <i class="fa-solid fa-shower me-1"></i> Soyunma Odası & Duş
                            </div>
                            <div class="filter-pill" id="pillShoes" onclick="togglePill('pillShoes')">
                                <i class="fa-solid fa-shoe-prints me-1 text-warning"></i> Krampon Kiralama
                            </div>
                        </div>

                        <div class="max-w-250 w-100">
                            <button type="submit" class="btn btn-team w-100 py-2.5 fw-bold fs-6">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> SAHA BUL
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container px-4">

    <!-- 3. TESİSLER LİSTESİ VE SAĞ TARAFTA ARAMA / FİLTRE ÖZETİ PANENLİ -->
    <section id="facilitiesSection" class="mb-5">
        
        <div class="row g-4">
            
            <!-- LEFT COLUMN (70%): SCROLLABLE STACKED FACILITY CARDS -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-dark mb-0 fs-5">
                        <i class="fa-solid fa-stadium text-primary me-2"></i> Listelenen Spor Tesisleri
                    </h4>
                    <span class="text-muted fs-7" id="facilityCountBadge">Arama bekleniyor...</span>
                </div>

                <!-- Initial Placeholder / Facility List -->
                <div class="d-flex flex-column gap-3" id="stackedFacilitiesList">
                    <div class="minimal-card p-5 text-center text-muted">
                        <i class="fa-solid fa-magnifying-glass text-primary display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-2">Henüz Arama Yapmadınız</h5>
                        <p class="fs-7 max-w-500 mx-auto">Lütfen yukarıdaki arama kutusundan İl ve İlçe seçip <strong>"SAHA BUL"</strong> butonuna basınız.</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (30%): STICKY QUICK FILTER WITH MOVED CANLI TESİS ADI ARAMA INPUT -->
            <div class="col-lg-4">
                <div class="minimal-card p-4 position-sticky" style="top: 90px;">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-sliders text-primary me-2"></i> Arama & Filtre Özeti
                    </h5>

                    <!-- TESİS ADI İLE CANLI ARAMA INPUT -->
                    <div class="mb-3">
                        <label class="form-label text-muted fs-8 fw-bold">TESİS ADI İLE CANLI ARA</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-primary"></i></span>
                            <input type="text" class="form-control border-start-0 bg-light fw-semibold" id="searchFacilityName" placeholder="Tesis adı yazın... (Örn: Kadıköy, Moda)" oninput="filterFacilitiesByName()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fs-8 fw-bold">SEÇİLİ KONUM</label>
                        <div class="p-2.5 bg-light rounded-3 d-flex align-items-center justify-content-between fs-7 fw-bold text-dark border">
                            <span><i class="fa-solid fa-location-dot text-danger me-2"></i><span id="summaryLocation">Henüz İl Seçilmedi</span></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fs-8 fw-bold">AKTİF FİLTRELER</label>
                        <div class="d-flex flex-wrap gap-1 fs-8" id="summaryFilters">
                            <span class="badge bg-light text-dark border">Tüm Tesisler</span>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <h6 class="fw-bold text-dark fs-7 mb-1"><i class="fa-solid fa-headset text-success me-1"></i> Müşteri Destek Hatlarımız</h6>
                        <p class="text-muted fs-8 mb-1">Rezervasyonunuzla ilgili 7/24 destek almak için:</p>
                        <span class="fw-bold text-success fs-7"><i class="fa-brands fa-whatsapp me-1"></i> 0850 555 00 11</span>
                    </div>

                    <div class="text-center text-muted fs-8">
                        <i class="fa-solid fa-shield-halved text-primary me-1"></i> SahaNet PRO Güvencesiyle Online Kirala
                    </div>
                </div>
            </div>

        </div>

    </section>

</div>

<!-- Modal: GİRİŞ YAPMALISINIZ UYARI MODALI -->
<div class="modal fade" id="authRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4">
            <div class="mb-3">
                <i class="fa-solid fa-lock text-warning display-4"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Giriş Yapmalısınız</h5>
            <p class="text-muted fs-7 mb-4">Randevu almak veya tesise abonman olmak için lütfen önce giriş yapın ya da yeni hesap oluşturun.</p>
            <a href="login.php" class="btn btn-team w-100 py-2.5 fw-bold mb-2">
                <i class="fa-solid fa-right-to-bracket me-1"></i> GİRİŞ YAP / KAYIT OL
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-bs-dismiss="modal">Kapat</button>
        </div>
    </div>
</div>

<!-- Modal: Oyuncu Randevu Alma -->
<div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-futbol text-primary me-2"></i> Spor Tesisi Randevusu Al</h5>
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
                            <input type="text" class="form-control fw-bold text-dark" name="contact_name" id="modalContactName" value="<?php echo htmlspecialchars($user_name_upper); ?>" required>
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
                            <input type="date" class="form-control" name="reservation_date" id="modalDate" min="<?php echo $today_str; ?>" readonly>
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
                <h5 class="modal-title fw-bold" id="facSubModalTitle"><i class="fa-solid fa-crown text-warning me-2"></i> İncele & Abone Ol</h5>
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
                            <option value="Sezonluk Efsane VIP (21.500 TL/Sezon)" selected>👑 Sezonluk Efsane VIP - 21.500 TL / Sezon (HD Özet + VIP Garantili)</option>
                            <option value="Kemik Kadro (11.000 TL/3 Ay)">🟡 Kemik Kadro Paket - 11.000 TL / 3 Ay (%15 İndirim + 1 Bedava Maç)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">TAKIM KAPTANI AD SOYAD *</label>
                        <input type="text" class="form-control fw-bold" id="facSubCaptain" value="<?php echo htmlspecialchars($user_name_upper); ?>" required>
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
const IS_LOGGED_IN = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
const TODAY_STR = '<?php echo $today_str; ?>';
const CURRENT_HOUR = <?php echo $current_hour; ?>;

const CITIES_DISTRICTS = {
    'İstanbul': ['Tüm İlçeler', 'Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Beyoğlu', 'Maltepe', 'Ataşehir', 'Ümraniye', 'Bakırköy', 'Fatih', 'Pendik', 'Sarıyer'],
    'Ankara': ['Tüm İlçeler', 'Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Gölbaşı'],
    'İzmir': ['Tüm İlçeler', 'Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Alsancak', 'Çiğli', 'Gaziemir'],
    'Bursa': ['Tüm İlçeler', 'Nilüfer', 'Osmangazi', 'Yıldırım', 'Mudanya'],
    'Antalya': ['Tüm İlçeler', 'Muratpaşa', 'Konyaaltı', 'Kepez', 'Alanya']
};

const TURKISH_DAYS = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
const TURKISH_MONTHS = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylul', 'Ekim', 'Kasım', 'Aralık'];

function formatTurkishDate(dateStr) {
    if (!dateStr || dateStr.length !== 10) return '';
    const parts = dateStr.split('-');
    const year = parseInt(parts[0]);
    const month = parseInt(parts[1]) - 1;
    const day = parseInt(parts[2]);

    const dateObj = new Date(year, month, day);
    const dayName = TURKISH_DAYS[dateObj.getDay()];
    const monthName = TURKISH_MONTHS[month];

    return `${day} ${monthName} ${year} - ${dayName}`;
}

function onCityChange() {
    const city = document.getElementById('portalCity').value;
    const distSelect = document.getElementById('portalDistrict');
    const districts = CITIES_DISTRICTS[city] || ['Tüm İlçeler'];

    let html = `<option value="" disabled selected>İlçe Seçiniz</option>`;
    html += districts.map(d => `<option value="${d}">${d}</option>`).join('');
    distSelect.innerHTML = html;
    document.getElementById('summaryLocation').innerText = `${city} / (İlçe Seçiniz)`;
}

function togglePill(id) {
    document.getElementById(id).classList.toggle('active');
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

function executeSearch(e) {
    if (e) e.preventDefault();

    const city = document.getElementById('portalCity').value;

    if (!city) {
        alert('Lütfen bir İl seçiniz!');
        return;
    }

    loadFacilities();
}

async function loadFacilities() {
    const city = document.getElementById('portalCity').value;
    const districtSelect = document.getElementById('portalDistrict').value;
    const district = (!districtSelect || districtSelect === 'Tüm İlçeler' || districtSelect === 'İlçe Seçiniz') ? '' : districtSelect;
    const sportType = document.getElementById('searchSportType').value;

    document.getElementById('summaryLocation').innerText = `${city} / ${districtSelect || 'Tüm İlçeler'}`;

    let reqFeatures = [];
    let filterPillsSummary = '';

    if (document.getElementById('pillCamera').classList.contains('active')) {
        reqFeatures.push('HD Kamera Kaydı');
        filterPillsSummary += `<span class="badge bg-success bg-opacity-10 text-success border me-1">📹 HD Kamera</span>`;
    }
    if (document.getElementById('pillWater').classList.contains('active')) {
        reqFeatures.push('Ücretsiz Su & İkram');
        filterPillsSummary += `<span class="badge bg-info bg-opacity-10 text-info border me-1">💧 Ücretsiz Su</span>`;
    }
    if (document.getElementById('pillShower').classList.contains('active')) {
        reqFeatures.push('Soyunma Odası & Duş');
        filterPillsSummary += `<span class="badge bg-primary bg-opacity-10 text-primary border me-1">🚿 Duş</span>`;
    }
    if (document.getElementById('pillShoes').classList.contains('active')) {
        reqFeatures.push('Krampon / Ayakkabı Kiralama');
        filterPillsSummary += `<span class="badge bg-warning bg-opacity-10 text-warning border me-1">👟 Krampon Kiralama</span>`;
    }

    document.getElementById('summaryFilters').innerHTML = filterPillsSummary || `<span class="badge bg-light text-dark border">Tüm Tesisler</span>`;

    const featuresParam = encodeURIComponent(reqFeatures.join(','));
    const res = await fetch(`api/facility.php?action=list_public&city=${encodeURIComponent(city)}&district=${encodeURIComponent(district)}&sport_type=${encodeURIComponent(sportType)}&features=${featuresParam}`);
    const json = await res.json();

    if (json.status === 'success') {
        currentFacilities = json.data;
        renderFacilitiesList(currentFacilities);
    }
}

function filterFacilitiesByName() {
    const query = document.getElementById('searchFacilityName').value.toLowerCase().trim();
    if (!query) {
        renderFacilitiesList(currentFacilities);
        return;
    }
    const filtered = currentFacilities.filter(f => f.name.toLowerCase().includes(query) || f.address.toLowerCase().includes(query));
    renderFacilitiesList(filtered);
}

function renderFacilitiesList(facilities) {
    const listContainer = document.getElementById('stackedFacilitiesList');
    document.getElementById('facilityCountBadge').innerText = `${facilities.length} Tesis Bulundu`;

    if (facilities.length === 0) {
        listContainer.innerHTML = `<div class="minimal-card p-5 text-center text-muted">Seçilen kriterlerde kayıtlı tesis bulunamadı. Lütfen başka bir spor tipi, il veya ilçe seçiniz.</div>`;
        return;
    }

    let html = '';
    facilities.forEach((fac) => {
        const featList = fac.features_array || ["HD Kamera Kaydı", "Ücretsiz Su & İkram", "Soyunma Odası & Duş"];

        html += `<div class="minimal-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold text-dark fs-5 mb-0"><i class="fa-solid fa-stadium text-primary me-1"></i>${escapeHtml(fac.name)}</h4>
                        <span class="badge bg-light text-dark border fs-8"><i class="fa-solid fa-clock text-primary me-1"></i>Açık Saatler: ${fac.open_time} - ${fac.close_time}</span>
                    </div>
                    <p class="text-muted fs-7 mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i>${escapeHtml(fac.address)} &bull; <i class="fa-solid fa-phone text-success me-1"></i>${escapeHtml(fac.phone)}</p>
                </div>
            </div>

            <!-- Clean Field Badges -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="text-muted fs-8 fw-bold">SAHALAR:</span>
                ${fac.fields.map(f => {
                    const isCovered = f.field_type && f.field_type.includes('Kapalı');
                    let icon = '⚽';
                    if (field_name_has(f, 'Basketbol')) icon = '🏀';
                    else if (field_name_has(f, 'Tenis')) icon = '🎾';
                    else if (field_name_has(f, 'Voleybol')) icon = '🏐';

                    const coverBadge = isCovered ? '🏢 Kapalı' : '☀️ Açık';
                    const statusText = (f.status === 'Pasif') ? ' <span class="text-danger fw-bold">(KAPALI)</span>' : '';
                    return `<span class="badge bg-light text-dark border fs-8">${icon} ${escapeHtml(f.field_name)} <span class="text-muted">(${coverBadge})</span>${statusText} <strong class="text-primary">(₺${parseFloat(f.hourly_fee).toLocaleString('tr-TR')})</strong></span>`;
                }).join('')}
            </div>

            <!-- Dynamic Feature Badges -->
            <div class="d-flex flex-wrap gap-3 fs-8 text-muted border-top pt-3">
                ${featList.map(ft => `<span><i class="fa-solid fa-check text-success me-1"></i>${escapeHtml(ft)}</span>`).join('')}
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                <button class="btn btn-team flex-grow-1 py-2 fs-7 fw-bold" onclick="toggleAccordionDrawer(${fac.id})">
                    <i class="fa-solid fa-calendar-days me-1"></i> Saatleri Gör & Randevu Al
                </button>
                <button class="btn btn-outline-secondary py-2 fs-7 fw-bold" onclick="openFacilitySubModal(${fac.id}, '${escapeHtml(fac.name)}')">
                    <i class="fa-solid fa-crown text-warning me-1"></i> İncele & Abone Ol
                </button>
            </div>

            <!-- INLINE SLIDE-DOWN ACCORDION RESERVATION DRAWER -->
            <div id="drawer-fac-${fac.id}" class="facility-accordion-drawer d-none">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calendar-check text-primary me-2"></i> Uygun Saat Seçimi</h6>
                        <span class="badge bg-white text-dark border mt-1 fs-7 fw-bold" id="day-label-fac-${fac.id}">${formatTurkishDate(TODAY_STR)}</span>
                    </div>
                    <input type="date" class="form-control form-control-sm max-w-160 fw-bold" id="date-fac-${fac.id}" min="${TODAY_STR}" value="${TODAY_STR}" onchange="onDrawerDateChange(${fac.id}, this)">
                </div>
                <div id="timeline-container-${fac.id}" class="table-responsive">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

        </div>`;
    });

    listContainer.innerHTML = html;
}

function field_name_has(f, word) {
    return f.field_name.includes(word) || (f.field_type && f.field_type.includes(word));
}

// TARİH SEÇERKEN YAZMA ERKEN UYARI BUG FIX (ONCHANGE + YIL 2026 KONTROLÜ)
function onDrawerDateChange(facId, inputEl) {
    const val = inputEl.value;
    if (!val || val.length !== 10) return;

    const parts = val.split('-');
    const year = parseInt(parts[0]);
    if (isNaN(year) || year < 2026) return;

    if (val < TODAY_STR) {
        alert('⚠️ Geçmiş bir tarihe randevu alınamaz!');
        inputEl.value = TODAY_STR;
        return;
    }

    const dayLabel = document.getElementById(`day-label-fac-${facId}`);
    if (dayLabel) dayLabel.innerText = formatTurkishDate(val);

    renderInlineDrawerTimeline(facId);
}

async function toggleAccordionDrawer(facId) {
    const drawer = document.getElementById(`drawer-fac-${facId}`);
    if (!drawer) return;

    if (!drawer.classList.contains('d-none')) {
        drawer.classList.add('d-none');
        return;
    }

    document.querySelectorAll('.facility-accordion-drawer').forEach(el => el.classList.add('d-none'));
    drawer.classList.remove('d-none');
    await renderInlineDrawerTimeline(facId);
}

// KRONOLOJİK SAAT DİZİLİMİ (00:00 - 23:00) VE TARİH ARALIĞI FIX
async function renderInlineDrawerTimeline(facId) {
    const fac = currentFacilities.find(f => f.id == facId);
    if (!fac) return;

    const dateInput = document.getElementById(`date-fac-${facId}`);
    let date = (dateInput && dateInput.value && dateInput.value.length === 10) ? dateInput.value : TODAY_STR;

    const dayLabel = document.getElementById(`day-label-fac-${facId}`);
    if (dayLabel) dayLabel.innerText = formatTurkishDate(date);

    // TESİS KAPALI GÜN KONTROLÜ
    const closedDates = fac.closed_dates_array || [];
    const closedMatch = closedDates.find(cd => {
        if (isObject(cd)) {
            const s = cd.start || cd.date;
            const e = cd.end || cd.start || cd.date;
            return (date >= s && date <= e);
        }
        return cd === date;
    });

    if (closedMatch) {
        const reason = isObject(closedMatch) ? (closedMatch.reason || 'Tesis Kapalı') : 'Kapalı';
        document.getElementById(`timeline-container-${facId}`).innerHTML = `
            <div class="p-4 text-center text-danger bg-danger bg-opacity-10 rounded-3 border border-danger">
                <i class="fa-solid fa-ban display-5 mb-2 d-block"></i>
                <h5 class="fw-bold">Tesis Bu Tarihte Hizmete Kapalıdır</h5>
                <p class="mb-0 fs-7 text-dark">Neden: <strong>${escapeHtml(reason)}</strong>. Lütfen başka bir gün için randevu saati seçiniz.</p>
            </div>`;
        return;
    }

    const res = await fetch(`api/reservations.php?action=list&facility_id=${facId}`);
    const json = await res.json();
    const reservations = json.data || [];

    const openH = parseInt(fac.open_time || '13');
    let closeH = parseInt(fac.close_time || '01');
    if (closeH <= openH) closeH += 24;

    const hourNumbers = [];
    for (let h = openH; h < closeH; h++) {
        hourNumbers.push(h % 24);
    }
    // Kronolojik Sıralama
    hourNumbers.sort((a, b) => a - b);

    const hours = hourNumbers.map(h => (h < 10 ? '0' : '') + h + ':00');

    let hHtml = `<table class="table table-borderless text-center align-middle m-0 fs-8"><thead class="border-bottom text-muted"><tr><th class="text-start">SAHA</th>`;
    hours.forEach(h => hHtml += `<th>${h}</th>`);
    hHtml += `</tr></thead><tbody>`;

    fac.fields.forEach(field => {
        let icon = '⚽';
        if (field_name_has(field, 'Basketbol')) icon = '🏀';
        else if (field_name_has(field, 'Tenis')) icon = '🎾';

        hHtml += `<tr><td class="fw-bold text-dark text-start py-2">${icon} ${escapeHtml(field.field_name)}</td>`;

        hours.forEach(h => {
            // SAHA ÖZEL KAPALI ARALIK KONTROLÜ
            const range = field.closed_range_obj || {};
            let isFieldClosedNow = (field.status === 'Pasif');
            let closeReasonText = 'Saha Kapalı';

            if (range.start_date && range.end_date) {
                if (date >= range.start_date && date <= range.end_date) {
                    isFieldClosedNow = true;
                    closeReasonText = range.reason || 'Kapalı';
                }
            }

            if (isFieldClosedNow) {
                hHtml += `<td><div class="slot-badge bg-danger bg-opacity-10 text-danger border border-danger" style="cursor:not-allowed;" title="${escapeHtml(closeReasonText)}"><i class="fa-solid fa-ban me-1"></i>KAPALI</div></td>`;
                return;
            }

            const hourNum = parseInt(h.split(':')[0]);
            const isToday = (date === TODAY_STR);
            
            const isNightShiftHour = (hourNum < openH);
            const isPastHourToday = isToday && !isNightShiftHour && (hourNum <= CURRENT_HOUR);

            const isBooked = reservations.some(r => r.field_id == field.id && r.reservation_date === date && r.reservation_time === h && r.status !== 'İptal');

            if (isBooked) {
                hHtml += `<td><div class="slot-badge slot-busy-normal"><i class="fa-solid fa-lock me-1"></i>DOLU</div></td>`;
            } else if (isPastHourToday) {
                hHtml += `<td><div class="slot-badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25" style="cursor:not-allowed;" title="Saat Geçti"><i class="fa-solid fa-clock-rotate-left me-1"></i>GEÇTİ</div></td>`;
            } else {
                hHtml += `<td>
                    <div class="slot-badge slot-free" onclick="handleSlotClick(${fac.id}, ${field.id}, '${escapeHtml(field.field_name)}', '${date}', '${h}', ${field.hourly_fee})">
                        +${h}
                    </div>
                </td>`;
            }
        });

        hHtml += `</tr>`;
    });

    hHtml += `</tbody></table>`;
    document.getElementById(`timeline-container-${facId}`).innerHTML = hHtml;
}

function isObject(val) {
    return val !== null && typeof val === 'object';
}

function handleSlotClick(facId, fieldId, fieldName, date, time, fee) {
    if (!IS_LOGGED_IN) {
        new bootstrap.Modal(document.getElementById('authRequiredModal')).show();
        return;
    }
    if (date < TODAY_STR) {
        alert('⚠️ Geçmiş bir tarihe randevu alınamaz!');
        return;
    }
    const hourNum = parseInt(time.split(':')[0]);
    if (date === TODAY_STR && hourNum <= CURRENT_HOUR && hourNum >= 8) {
        alert('⚠️ Geçmiş bir saate randevu alınamaz!');
        return;
    }
    openPlayerBookModal(facId, fieldId, fieldName, date, time, fee);
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
    if (!IS_LOGGED_IN) {
        new bootstrap.Modal(document.getElementById('authRequiredModal')).show();
        return;
    }
    document.getElementById('facSubId').value = facId;
    document.getElementById('facSubName').value = facName;
    new bootstrap.Modal(document.getElementById('facilitySubModal')).show();
}

function handleFacilitySubscription(e) {
    e.preventDefault();
    const facName = document.getElementById('facSubName').value;
    const tier = document.getElementById('facSubTierSelect').value;
    const captain = document.getElementById('facSubCaptain').value;

    alert(`🎉 TEBRİKLER!\n${facName} tesisi için [${tier}] abonmanlık talebiniz oluşturuldu.\nKaptan: ${captain}\nİşletmeci sizinle iletişime geçecektir.`);
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
        const facId = document.getElementById('modalFacId').value;
        renderInlineDrawerTimeline(facId);
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
