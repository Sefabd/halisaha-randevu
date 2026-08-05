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

        <!-- Right Navigation Links (SporPin Header Style) -->
        <nav class="d-none d-lg-flex align-items-center gap-4 text-muted fs-7 fw-semibold">
            <a href="index.php" class="text-dark text-decoration-none"><i class="fa-solid fa-house me-1"></i> Ana Sayfa</a>
            <a href="#facilitiesSection" class="text-secondary text-decoration-none"><i class="fa-solid fa-stadium me-1"></i> Sahalar</a>
            <a href="#abonmanSection" class="text-secondary text-decoration-none"><i class="fa-solid fa-crown me-1"></i> Abonman Paketleri</a>
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

<!-- 2. SPORPIN STYLE BODY HERO SEARCH BANNER -->
<section class="py-5 mb-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
    <div class="container text-center py-4 position-relative" style="z-index: 2;">
        <span class="badge bg-white text-dark rounded-pill px-3 py-2 fs-7 mb-3 fw-bold shadow-sm">
            <i class="fa-solid fa-bolt text-warning me-1"></i> SPORCULARLA HALI SAHALARI BULUŞTURAN PLATFORM!
        </span>
        <h1 class="display-6 fw-extrabold mb-2">Hemen Halı Saha veya VIP Saha Bul, Rezervasyonunu Yap.</h1>
        <p class="text-muted fs-6 mb-4 max-w-700 mx-auto">İl ve ilçe seçerek açık saatleri listeleyin, online saha kiralayın.</p>

        <!-- YÜZEN 3'LÜ ARAMA KUTUSU (BODY HERO SEARCH BAR) -->
        <div class="minimal-card p-3 max-w-900 mx-auto shadow-lg text-dark bg-white">
            <form onsubmit="searchFacilities(event)" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-futbol text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="searchSportType">
                            <option value="Halı Saha">Halı Saha</option>
                            <option value="Kapalı Çim">Kapalı Suni Çim</option>
                            <option value="VIP Saha">VIP Kamera Kayıtlı</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-city text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="portalCity" onchange="searchFacilities(event)">
                            <option value="İstanbul" <?php echo $selected_city === 'İstanbul' ? 'selected' : ''; ?>>İstanbul</option>
                            <option value="Ankara" <?php echo $selected_city === 'Ankara' ? 'selected' : ''; ?>>Ankara</option>
                            <option value="İzmir" <?php echo $selected_city === 'İzmir' ? 'selected' : ''; ?>>İzmir</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                        <select class="form-select border-0 bg-light" id="portalDistrict" onchange="searchFacilities(event)">
                            <option value="Kadıköy" <?php echo $selected_district === 'Kadıköy' ? 'selected' : ''; ?>>Kadıköy</option>
                            <option value="Beşiktaş" <?php echo $selected_district === 'Beşiktaş' ? 'selected' : ''; ?>>Beşiktaş</option>
                            <option value="Çankaya" <?php echo $selected_district === 'Çankaya' ? 'selected' : ''; ?>>Çankaya</option>
                        </select>
                    </div>
                </div>

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

    <!-- 3. KOMPAKT KÜÇÜK ABONMAN PAKETLERİ SECTION -->
    <section id="abonmanSection" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0 fs-5"><i class="fa-solid fa-crown text-warning me-2"></i> Abonman Paketleri</h4>
                <span class="text-muted fs-7">Sabit gün, saat garantisi ve indirimli paketler.</span>
            </div>
        </div>

        <div class="row g-3">
            <!-- 1. Aylık Fix (Kompakt Mini Kart) -->
            <div class="col-md-4">
                <div class="minimal-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-light text-primary border fs-8 mb-1">1 AY SÜRELİ</span>
                        <h6 class="fw-bold text-dark mb-0">AYLIK FİX PAKET</h6>
                        <span class="text-primary fw-extrabold fs-6">4.000 TL <span class="fs-8 text-muted fw-normal">/Ay</span></span>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary rounded-2" onclick="selectPlan('Aylık Fix', '4.000 TL')">Seç</button>
                </div>
            </div>

            <!-- 2. Sezonluk Efsane VIP (Kompakt Mini Kart) -->
            <div class="col-md-4">
                <div class="minimal-card p-3 border-primary border-2 d-flex align-items-center justify-content-between bg-light">
                    <div>
                        <span class="badge bg-primary text-white fs-8 mb-1">6 AY VIP</span>
                        <h6 class="fw-bold text-dark mb-0">SEZONLUK EFSANE</h6>
                        <span class="text-primary fw-extrabold fs-6">21.500 TL <span class="fs-8 text-muted fw-normal">/Sezon</span></span>
                    </div>
                    <button class="btn btn-sm btn-team rounded-2" onclick="selectPlan('Sezonluk Efsane VIP', '21.500 TL')">VIP Seç</button>
                </div>
            </div>

            <!-- 3. Kemik Kadro (Kompakt Mini Kart) -->
            <div class="col-md-4">
                <div class="minimal-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-light text-dark border fs-8 mb-1">3 AY SÜRELİ</span>
                        <h6 class="fw-bold text-dark mb-0">KEMİK KADRO</h6>
                        <span class="text-dark fw-extrabold fs-6">11.000 TL <span class="fs-8 text-muted fw-normal">/3 Ay</span></span>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary rounded-2" onclick="selectPlan('Kemik Kadro', '11.000 TL')">Seç</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. SOSYALHALISA HAKKINDA & SİSTEM AVANTAJLARI (SPORPİN İLHAMLI) -->
    <section id="howItWorks" class="mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h2 class="fw-extrabold text-dark display-6 mb-3">SahaNet PRO Nedir?</h2>
                <p class="text-muted fs-6 mb-3">SahaNet PRO; halı saha ve spor merkezlerinin rezervasyonlarını kolaylıkla yönetebileceği ve oyuncuların saniyeler içinde boş saatleri görüp randevu alabileceği nesil bir sistemdir.</p>
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
                            <p class="text-muted fs-7 mb-0">İşletmeci çalışma saatlerini ayarlar, oyuncular anında online randevusunu oluşturur.</p>
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
                            <i class="fa-solid fa-user-plus text-warning fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">Eksik Oyuncu & Rakip Bulma</h6>
                            <p class="text-muted fs-7 mb-0">Kadronuzda adam eksikse ilan verin, anında 14 kişiyi tamamlayın.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="minimal-card p-3 h-100">
                            <i class="fa-brands fa-whatsapp text-success fs-3 mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">WhatsApp Bildirimleri</h6>
                            <p class="text-muted fs-7 mb-0">Randevu onayınız ve maç detaylarınız anında cep telefonunuza iletilir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TESİSLER VE CANLI SAAT MADDESİ SECTION -->
    <section id="facilitiesSection" class="mb-5">
        <h4 class="fw-bold text-dark mb-3 fs-5">
            <i class="fa-solid fa-stadium text-primary me-2"></i> Listelenen Halı Sahalar
        </h4>

        <div class="row g-4" id="facilitiesGrid">
            <!-- Loaded via JS -->
        </div>
    </section>

    <!-- 6. CANLI SAAT MADDESİ & TAKVİM MATRİSİ -->
    <section class="minimal-card p-4 mb-5 d-none" id="facilityTimelineSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0 fs-5" id="selectedFacilityTitle">Tesis Sahaları & Açık Saatler</h4>
                <span class="text-muted fs-7" id="selectedFacilityHoursInfo">Açık saatler listelenmektedir.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="date" class="form-control form-control-sm max-w-160" id="timelineDate" onchange="loadFacilityTimeline()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless text-center align-middle m-0">
                <thead class="border-bottom text-muted fs-7">
                    <tr id="timelineHeaderRow">
                        <!-- Hours dynamically generated based on facility open/close time -->
                    </tr>
                </thead>
                <tbody id="timelineBodyRows">
                    <!-- Fields dynamically generated -->
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal: Oyuncu Randevu Alma -->
<div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="playerModalTitle"><i class="fa-solid fa-futbol text-primary me-2"></i> Halı Saha Randevusu Al</h5>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadFacilities();
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('timelineDate').value = todayStr;
});

function switchTeamTheme(team) {
    document.documentElement.setAttribute('data-team', team);
    fetch('api/auth.php?action=set_team', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `team=${encodeURIComponent(team)}`
    });
}

let currentFacilities = [];
let activeFacility = null;

async function searchFacilities(e) {
    if (e) e.preventDefault();
    await loadFacilities();
}

async function loadFacilities() {
    const city = document.getElementById('portalCity').value;
    const district = document.getElementById('portalDistrict').value;

    const res = await fetch(`api/facility.php?action=list_public&city=${encodeURIComponent(city)}&district=${encodeURIComponent(district)}`);
    const json = await res.json();

    const grid = document.getElementById('facilitiesGrid');
    if (json.status === 'success') {
        currentFacilities = json.data;

        if (currentFacilities.length === 0) {
            grid.innerHTML = `<div class="col-12 text-center text-muted py-5">Seçilen il/ilçede kayıtlı tesis bulunamadı.</div>`;
            document.getElementById('facilityTimelineSection').classList.add('d-none');
            return;
        }

        let html = '';
        currentFacilities.forEach((fac, idx) => {
            html += `<div class="col-md-6 col-lg-4">
                <div class="facility-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="fw-bold text-dark fs-5 mb-0">${escapeHtml(fac.name)}</h4>
                            <span class="badge bg-light text-dark border fw-bold fs-8"><i class="fa-solid fa-clock me-1 text-primary"></i>${fac.open_time} - ${fac.close_time}</span>
                        </div>
                        <p class="text-muted fs-7 mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i>${escapeHtml(fac.address)}</p>
                        <p class="text-muted fs-7 mb-3"><i class="fa-solid fa-phone text-success me-1"></i>${escapeHtml(fac.phone)}</p>
                        
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            ${fac.fields.map(f => `<span class="badge bg-light text-dark border fs-8">${escapeHtml(f.field_name)}</span>`).join('')}
                        </div>
                    </div>
                    <button class="btn btn-team w-100 py-2 fs-7 fw-bold" onclick="selectFacility(${fac.id})">
                        <i class="fa-solid fa-calendar-days me-1"></i> SAHALARI VE SAATLERİ GÖR
                    </button>
                </div>
            </div>`;
        });
        grid.innerHTML = html;

        if (currentFacilities.length > 0) {
            selectFacility(currentFacilities[0].id);
        }
    }
}

function selectFacility(id) {
    activeFacility = currentFacilities.find(f => f.id == id);
    if (!activeFacility) return;

    document.getElementById('facilityTimelineSection').classList.remove('d-none');
    document.getElementById('selectedFacilityTitle').innerText = `${activeFacility.name} - Sahalar ve Çalışma Saatleri`;
    document.getElementById('selectedFacilityHoursInfo').innerText = `İşletmenin Belirlediği Açık Saatler: ${activeFacility.open_time} - ${activeFacility.close_time}`;

    loadFacilityTimeline();
    document.getElementById('facilityTimelineSection').scrollIntoView({ behavior: 'smooth' });
}

async function loadFacilityTimeline() {
    if (!activeFacility) return;
    const date = document.getElementById('timelineDate').value;

    const res = await fetch(`api/reservations.php?action=list&facility_id=${activeFacility.id}`);
    const json = await res.json();
    const reservations = json.data || [];

    const openH = parseInt(activeFacility.open_time || '13');
    let closeH = parseInt(activeFacility.close_time || '01');
    if (closeH <= openH) closeH += 24;

    const hours = [];
    for (let h = openH; h < closeH; h++) {
        const realH = h % 24;
        hours.push((realH < 10 ? '0' : '') + realH + ':00');
    }

    const headerRow = document.getElementById('timelineHeaderRow');
    let hHtml = `<th class="text-start">SAHA ADI</th>`;
    hours.forEach(h => hHtml += `<th>${h}</th>`);
    headerRow.innerHTML = hHtml;

    const bodyRows = document.getElementById('timelineBodyRows');
    let bHtml = '';

    activeFacility.fields.forEach(field => {
        bHtml += `<tr><td class="fw-bold text-dark text-start"><i class="fa-solid fa-futbol text-primary me-2"></i>${escapeHtml(field.field_name)}</td>`;

        hours.forEach(h => {
            const isBooked = reservations.some(r => r.field_id == field.id && r.reservation_date === date && r.reservation_time === h && r.status !== 'İptal');

            if (isBooked) {
                bHtml += `<td><div class="slot-badge slot-busy"><i class="fa-solid fa-lock me-1"></i>DOLU</div></td>`;
            } else {
                bHtml += `<td>
                    <div class="slot-badge slot-free" onclick="openPlayerBookModal(${activeFacility.id}, ${field.id}, '${escapeHtml(field.field_name)}', '${date}', '${h}', ${field.hourly_fee})">
                        <i class="fa-solid fa-plus me-1"></i>${h}
                    </div>
                </td>`;
            }
        });

        bHtml += `</tr>`;
    });

    bodyRows.innerHTML = bHtml;
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

function selectPlan(planName, price) {
    alert(`[${planName}] seçildi! Lütfen önce aşağıdan sahanızı ve boş saatinizi seçiniz.`);
    document.getElementById('facilitiesSection').scrollIntoView({ behavior: 'smooth' });
}

async function handlePlayerBook(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/reservations.php?action=save', { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
        alert('🎉 Randevunuz başarıyla oluşturuldu!');
        bootstrap.Modal.getInstance(document.getElementById('playerModal')).hide();
        loadFacilityTimeline();
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
