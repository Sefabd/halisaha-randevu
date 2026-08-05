<?php
// index.php - Oyuncu & Müşteri Halı Saha Randevu Portalı (Light Mode & Team Engine)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$selected_city = $_SESSION['city'] ?? 'İstanbul';
$selected_district = $_SESSION['district'] ?? 'Kadıköy';
$current_team = $_SESSION['user_team'] ?? 'galatasaray';
?>
<!DOCTYPE html>
<html lang="tr" data-team="<?php echo htmlspecialchars($current_team); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SahaNet PRO - Halı Saha Kirala & Abonman Ol</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Minimal Header Navbar -->
<header class="minimal-navbar py-3 mb-4">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <a href="index.php" class="d-flex align-items-center text-decoration-none gap-2">
            <div class="brand-badge fs-5">
                <i class="fa-solid fa-futbol"></i> SahaNet
            </div>
            <span class="fs-4 fw-extrabold text-dark brand-font">PRO</span>
        </a>

        <!-- City & District Selector & Team Switcher -->
        <div class="d-flex align-items-center gap-2">
            <!-- Süper Lig Team Switcher -->
            <select class="form-select form-select-sm max-w-140" onchange="switchTeamTheme(this.value)">
                <option value="galatasaray" <?php echo $current_team === 'galatasaray' ? 'selected' : ''; ?>>🟡🔴 GS</option>
                <option value="fenerbahce" <?php echo $current_team === 'fenerbahce' ? 'selected' : ''; ?>>🔵🟡 FB</option>
                <option value="besiktas" <?php echo $current_team === 'besiktas' ? 'selected' : ''; ?>>⬛⚪ BJK</option>
                <option value="trabzonspor" <?php echo $current_team === 'trabzonspor' ? 'selected' : ''; ?>>🟣🔴 TS</option>
                <option value="neutral" <?php echo $current_team === 'neutral' ? 'selected' : ''; ?>>🟢⚪ Nötr</option>
            </select>

            <select class="form-select form-select-sm max-w-140" id="portalCity" onchange="loadFacilities()">
                <option value="İstanbul" <?php echo $selected_city === 'İstanbul' ? 'selected' : ''; ?>>İstanbul</option>
                <option value="Ankara" <?php echo $selected_city === 'Ankara' ? 'selected' : ''; ?>>Ankara</option>
                <option value="İzmir" <?php echo $selected_city === 'İzmir' ? 'selected' : ''; ?>>İzmir</option>
            </select>
            <select class="form-select form-select-sm max-w-140" id="portalDistrict" onchange="loadFacilities()">
                <option value="Kadıköy" <?php echo $selected_district === 'Kadıköy' ? 'selected' : ''; ?>>Kadıköy</option>
                <option value="Beşiktaş" <?php echo $selected_district === 'Beşiktaş' ? 'selected' : ''; ?>>Beşiktaş</option>
                <option value="Çankaya" <?php echo $selected_district === 'Çankaya' ? 'selected' : ''; ?>>Çankaya</option>
            </select>
            <a href="login.php" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="fa-solid fa-user-gear me-1"></i> Giriş Yap
            </a>
        </div>
    </div>
</header>

<div class="container-fluid px-4">
    
    <!-- Minimal Hero Banner -->
    <div class="minimal-card p-4 p-md-5 mb-5 text-center position-relative overflow-hidden">
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fs-7 mb-3 fw-bold">
            <i class="fa-solid fa-shirt text-primary me-1"></i> SÜPER LİG TAKIM TEMANIZLA SAHA KİRALAYIN
        </span>
        <h1 class="display-6 fw-extrabold text-dark mb-2">BÖLGENDEKİ EN İYİ HALI SAHALAR</h1>
        <p class="text-muted fs-6 max-w-700 mx-auto">İlçe seçin, tesisin işletmeci saatlerinde (Örn: 13:00 - 01:00) açık saatlerine tıklayarak anında randevunuzu oluşturun.</p>
    </div>

    <!-- 1. TESİSLER VE SAHA SEÇİMİ SECTION -->
    <section class="mb-5">
        <h3 class="fw-bold text-dark mb-4 fs-4">
            <i class="fa-solid fa-stadium text-primary me-2"></i> Bölgenizdeki Halı Saha Tesisleri
        </h3>

        <div class="row g-4" id="facilitiesGrid">
            <!-- Loaded via JS -->
        </div>
    </section>

    <!-- 2. CANLI SAAT MADDESİ & TAKVİM MATRİSİ -->
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

    <!-- 3. ABONMAN PAKETLERİ SECTION -->
    <section id="abonmanSection" class="mb-5">
        <div class="text-center mb-4">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fs-7 mb-2 fw-bold">
                <i class="fa-solid fa-crown me-1"></i> PAKET KİRALAMA
            </span>
            <h2 class="display-6 fw-extrabold text-dark">ABONMAN PAKETLERİ</h2>
            <p class="text-muted fs-6">Sabit gün ve saat garantisi, drone maç özetleri ve VIP ayrıcalıklar.</p>
        </div>

        <div class="subscription-grid">
            <!-- 1. Aylık Fix Paket -->
            <div class="sub-card">
                <h3 class="fw-bold text-dark fs-4 mb-1">AYLIK FİX PAKET</h3>
                <div class="text-muted fs-7 fw-semibold">1 AY SÜRELİ</div>
                <div class="fs-3 fw-extrabold text-dark my-3">4.000 TL <span class="fs-7 text-muted fw-normal">/ Ay</span></div>
                <ul class="list-unstyled text-muted fs-7 mb-4">
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Sabit Gün ve Saat Garantisi</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>%10 Abonman İndirimi</li>
                    <li><i class="fa-solid fa-check text-primary me-2"></i>1 Gün Önceden İptal Hakkı</li>
                </ul>
                <button class="btn btn-outline-secondary w-100 rounded-3 py-2 fw-bold" onclick="selectPlan('Aylık Fix', '4.000 TL')">Hemen Katıl</button>
            </div>

            <!-- 2. Sezonluk Efsane VIP -->
            <div class="sub-card sub-card-vip">
                <div class="vip-badge"><i class="fa-solid fa-crown me-1"></i> MOST POPULAR VIP</div>
                <h3 class="fw-bold text-dark fs-3 mb-1">SEZONLUK EFSANE</h3>
                <div class="text-primary fs-7 fw-bold">6 AY SÜRELİ - VIP</div>
                <div class="fs-2 fw-extrabold text-primary my-3">21.500 TL <span class="fs-7 text-muted fw-normal">/ Sezon</span></div>
                <ul class="list-unstyled text-muted fs-7 mb-4">
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>VIP Sabit Saat Garantisi</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Drone & Kamera Maç Özeti</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Eksik Oyuncu Bulma Desteği</li>
                    <li><i class="fa-solid fa-check text-primary me-2"></i>Turnuvalara Öncelikli Kayıt</li>
                </ul>
                <button class="btn btn-team w-100 rounded-3 py-2.5 fw-bold" onclick="selectPlan('Sezonluk Efsane VIP', '21.500 TL')">
                    VIP ÜYE OL
                </button>
            </div>

            <!-- 3. Kemik Kadro -->
            <div class="sub-card">
                <h3 class="fw-bold text-dark fs-4 mb-1">KEMİK KADRO</h3>
                <div class="text-muted fs-7 fw-semibold">3 AY SÜRELİ</div>
                <div class="fs-3 fw-extrabold text-dark my-3">11.000 TL <span class="fs-7 text-muted fw-normal">/ 3 Ay</span></div>
                <ul class="list-unstyled text-muted fs-7 mb-4">
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>Sabit Gün ve Saat Garantisi</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>%15 Abonman İndirimi</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i>HD Maç Kaydı</li>
                    <li><i class="fa-solid fa-check text-primary me-2"></i>Dönem Sonu 1 Bedava Maç</li>
                </ul>
                <button class="btn btn-outline-secondary w-100 rounded-3 py-2 fw-bold" onclick="selectPlan('Kemik Kadro', '11.000 TL')">Paketi Seç</button>
            </div>
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
    alert(`[${planName}] seçildi! Lütfen önce istediğiniz halı sahadan boş saatinizi seçiniz.`);
    document.getElementById('facilitiesGrid').scrollIntoView({ behavior: 'smooth' });
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
