<?php
// owner_dashboard.php - Tesis İşletmecisi Paneli (Unique Username Disambiguation & Searchable Subscription Picker)
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
$today_str = date('Y-m-d');
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

<!-- Header Navbar -->
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

        <!-- ANLIK CANLI SİSTEM SAATİ WIDGET'I -->
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-dark text-white p-2 rounded-3 fs-7 fw-bold d-none d-sm-block">
                <i class="fa-solid fa-clock text-warning me-1"></i> Sistem Saati: <span id="liveSystemClock">--:--:--</span>
            </div>

            <!-- Team Quick Switcher -->
            <select class="form-select form-select-sm max-w-130 border-0 bg-light" onchange="switchTeamTheme(this.value)">
                <option value="galatasaray" <?php echo $current_team === 'galatasaray' ? 'selected' : ''; ?>>🟡🔴 GS</option>
                <option value="fenerbahce" <?php echo $current_team === 'fenerbahce' ? 'selected' : ''; ?>>🔵🟡 FB</option>
                <option value="besiktas" <?php echo $current_team === 'besiktas' ? 'selected' : ''; ?>>⬛⚪ BJK</option>
                <option value="trabzonspor" <?php echo $current_team === 'trabzonspor' ? 'selected' : ''; ?>>🟣🔴 TS</option>
                <option value="neutral" <?php echo $current_team === 'neutral' ? 'selected' : ''; ?>>🟢⚪ Yeşil</option>
            </select>

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
                <div class="text-muted fs-8 fw-bold">OYNAYAN / TAMAMLANAN</div>
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

    <!-- KRONOLOJİK CANLI SAAT MATRİSİ -->
    <section class="minimal-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calendar-days text-primary me-2"></i> Canlı Saatlik Doluluk Matrisi</h5>
                <span class="text-muted fs-8">Tarih değiştirerek istenen günün doluluğunu inceleyin. (Hafta sonu saatleri otomatik devreye girer). 🟢 Boş saatlere tıklayarak elden kayıt yapabilirsiniz.</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 fs-8 d-none d-md-flex">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">🟢 Boş (Elden Kayıt)</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">🕒 Geçti</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">🔴 Alınan Randevu</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">🟡 Abonmanlı</span>
                </div>
                <input type="date" class="form-control form-control-sm max-w-160 fw-bold border-primary" id="matrixDate" value="<?php echo $today_str; ?>" onchange="onMatrixDateInput(this)">
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
        <!-- 1. GELİŞMİŞ TESİS & ÇALIŞMA SAATLERİ -->
        <div class="col-lg-5">
            <div class="minimal-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Tesis & Gelişmiş Çalışma Saatleri
                </h5>

                <form id="facilityProfileForm" onsubmit="saveFacilityProfile(event)">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">TESİS ADI</label>
                            <input type="text" class="form-control" name="name" id="fac_name" required>
                        </div>

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
                        
                        <!-- HAFTA İÇİ SAATLERİ -->
                        <div class="col-6">
                            <label class="form-label text-primary fs-8 fw-bold">HAFTA İÇİ AÇILIŞ</label>
                            <select class="form-select" name="open_time" id="fac_open_time">
                                <option value="08:00">08:00</option><option value="09:00">09:00</option><option value="10:00">10:00</option><option value="11:00">11:00</option><option value="12:00">12:00</option><option value="13:00" selected>13:00</option><option value="14:00">14:00</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-primary fs-8 fw-bold">HAFTA İÇİ KAPANIŞ</label>
                            <select class="form-select" name="close_time" id="fac_close_time">
                                <option value="22:00">22:00</option><option value="23:00">23:00</option><option value="00:00">00:00</option><option value="01:00" selected>01:00</option><option value="02:00">02:00</option><option value="03:00">03:00</option>
                            </select>
                        </div>

                        <!-- HAFTA SONU SAATLERİ -->
                        <div class="col-6">
                            <label class="form-label text-success fs-8 fw-bold">HAFTA SONU AÇILIŞ</label>
                            <select class="form-select" name="open_time_weekend" id="fac_open_time_weekend">
                                <option value="08:00">08:00</option><option value="09:00" selected>09:00</option><option value="10:00">10:00</option><option value="11:00">11:00</option><option value="12:00">12:00</option><option value="13:00">13:00</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-success fs-8 fw-bold">HAFTA SONU KAPANIŞ</label>
                            <select class="form-select" name="close_time_weekend" id="fac_close_time_weekend">
                                <option value="23:00">23:00</option><option value="00:00">00:00</option><option value="01:00">01:00</option><option value="02:00">02:00</option><option value="03:00" selected>03:00</option><option value="04:00">04:00</option>
                            </select>
                        </div>

                        <!-- TESİS SEVİYESİ İMKANLAR FORMU -->
                        <div class="col-12 border-top pt-3">
                            <label class="form-label text-dark fs-8 fw-bold mb-2"><i class="fa-solid fa-list-check text-primary me-1"></i> TESİS İMKANLARI VE HİZMETLERİ</label>
                            <div class="row g-2 fs-7">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="HD Kamera Kaydı" id="fac_feat_camera" checked>
                                        <label class="form-check-label" for="fac_feat_camera"><i class="fa-solid fa-video text-success me-1"></i> HD Kamera Kaydı</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="Ücretsiz Su & İkram" id="fac_feat_water" checked>
                                        <label class="form-check-label" for="fac_feat_water"><i class="fa-solid fa-bottle-water text-info me-1"></i> Ücretsiz Su & İkram</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="Soyunma Odası & Duş" id="fac_feat_shower" checked>
                                        <label class="form-check-label" for="fac_feat_shower"><i class="fa-solid fa-shower text-primary me-1"></i> Soyunma Odası & Duş</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="Krampon / Ayakkabı Kiralama" id="fac_feat_shoes">
                                        <label class="form-check-label" for="fac_feat_shoes"><i class="fa-solid fa-shoe-prints text-warning me-1"></i> Krampon Kiralama</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ÖNCEDEN KAPALI GÜN / TARİH ARALIĞI EKLEME -->
                        <div class="col-12 border-top pt-3">
                            <label class="form-label text-danger fs-8 fw-bold mb-1"><i class="fa-solid fa-ban me-1"></i> TESİS KAPALI TARİH ARALIĞI EKLE</label>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="fs-8 text-muted">Başlangıç Tarihi</label>
                                    <input type="date" class="form-control form-control-sm" name="closed_start_date" id="closedStartDate" min="<?php echo $today_str; ?>">
                                </div>
                                <div class="col-6">
                                    <label class="fs-8 text-muted">Bitiş Tarihi</label>
                                    <input type="date" class="form-control form-control-sm" name="closed_end_date" id="closedEndDate" min="<?php echo $today_str; ?>">
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" name="closed_reason" id="closedReason" placeholder="Neden? (Örn: Tesis Bakımı / Özel İzin)">
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1 fs-8" id="closedDatesBadgeList"></div>
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
                        <span class="text-muted fs-7">Sahalarınız, anlık doluluk durumları ve kapalı kalacağı tarih/saat aralığı ayarları</span>
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
                                <th>ANLIK DURUM</th>
                                <th>DURUM</th>
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

    <!-- 3. ABONMAN YÖNETİMİ & SATIŞ TAKİBİ KARTI (@KULLANICI ADI DESTEKLİ) -->
    <section class="minimal-card p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-crown text-warning me-2"></i> ABONMAN YÖNETİMİ & SATIŞ TAKİBİ</h5>
                <span class="text-muted fs-7">Tesisinize abone olan oyuncuları, kullanıcı adlarını (@kullanıcı_adı) ve kalan maç kredilerini yönetin.</span>
            </div>
            <button class="btn btn-warning text-dark fw-bold btn-sm shadow-sm" onclick="openOwnerAddSubModal()">
                <i class="fa-solid fa-plus me-1"></i> Müşteriye Abonman Tanımla
            </button>
        </div>

        <div class="scrollable-table-container" style="max-height:300px;">
            <table class="table table-hover align-middle m-0 fs-7">
                <thead class="table-light text-muted border-bottom sticky-top">
                    <tr>
                        <th>MÜŞTERİ AD SOYAD / @KULLANICI ADI</th>
                        <th>TELEFON</th>
                        <th>PAKET</th>
                        <th>SAHA</th>
                        <th>KULLANIM MODU</th>
                        <th>TOPLAM MAÇ</th>
                        <th>KALAN KREDİ</th>
                        <th>TUTAR</th>
                        <th>DURUM</th>
                        <th class="text-end">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody id="ownerSubscriptionsBody"></tbody>
            </table>
        </div>
    </section>

    <!-- 4. RANDEVU LİSTELERİNDE SIRALANABİLİR KOLONLAR VE SAHA FİLTRESİ -->
    <section class="minimal-card p-4 mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0 fs-5"><i class="fa-solid fa-list-check text-primary me-2"></i> İşletme Randevu Yönetimi</h4>
                <span class="text-muted fs-7">Müşteri karışıklığını önlemek için benzersiz @kullanıcı_adı ve telefon numaraları gösterilmektedir.</span>
            </div>

            <!-- CANLI ARAMA KUTUSU VE SAHA FİLTRESİ DROPDOWN'U -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select class="form-select form-select-sm max-w-180 border-primary fw-bold" id="filterReservationField" onchange="filterReservations()">
                    <option value="all">Tüm Sahalar</option>
                </select>

                <div class="max-w-220">
                    <input type="text" class="form-control form-control-sm" id="searchReservationQuery" placeholder="🔍 @kullanıcı, Takım veya Tel Ara..." oninput="filterReservations()">
                </div>
            </div>
        </div>

        <!-- 3 FILTER TABS -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-pills" id="reservationFilterTabs">
                <li class="nav-item">
                    <button class="nav-link active fw-bold py-1.5 px-3 fs-7" onclick="setReservationTab('today')">
                        <i class="fa-solid fa-calendar-day me-1 text-warning"></i> Bugünkü Randevular <span class="badge bg-white text-dark ms-1" id="tabCountToday">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-1.5 px-3 fs-7" onclick="setReservationTab('future')">
                        <i class="fa-solid fa-clock me-1 text-primary"></i> Gelecek Randevular <span class="badge bg-white text-dark ms-1" id="tabCountFuture">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-1.5 px-3 fs-7" onclick="setReservationTab('past')">
                        <i class="fa-solid fa-history me-1 text-secondary"></i> Geçmiş Randevular <span class="badge bg-white text-dark ms-1" id="tabCountPast">0</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- SIRALANABİLİR KOLONLAR (ASC / DESC İKONLARI VE SABİT YÜKSEKLİKLİ İÇ SCROLLBAR) -->
        <div class="scrollable-table-container">
            <table class="table table-hover align-middle m-0 fs-7">
                <thead class="table-light text-muted border-bottom sticky-top">
                    <tr>
                        <th class="py-3 sortable-th" onclick="sortReservationsBy('team_name')">
                            TAKIM ADI <span id="sort-team_name"><i class="fa-solid fa-sort text-muted fs-8 ms-1"></i></span>
                        </th>
                        <th class="py-3 sortable-th" onclick="sortReservationsBy('contact_name')">
                            YETKİLİ KİŞİ / @KULLANICI ADI <span id="sort-contact_name"><i class="fa-solid fa-sort text-muted fs-8 ms-1"></i></span>
                        </th>
                        <th class="py-3">TELEFON</th>
                        <th class="py-3">SAHA</th>
                        <th class="py-3 sortable-th" onclick="sortReservationsBy('reservation_date')">
                            TARİH <span id="sort-reservation_date"><i class="fa-solid fa-sort text-muted fs-8 ms-1"></i></span>
                        </th>
                        <th class="py-3 sortable-th" onclick="sortReservationsBy('reservation_time')">
                            SAAT <span id="sort-reservation_time"><i class="fa-solid fa-sort text-muted fs-8 ms-1"></i></span>
                        </th>
                        <th class="py-3 sortable-th" onclick="sortReservationsBy('fee')">
                            ÜCRET <span id="sort-fee"><i class="fa-solid fa-sort text-muted fs-8 ms-1"></i></span>
                        </th>
                        <th class="py-3">MAÇ DURUMU (OTOMATİK)</th>
                        <th class="py-3 text-end">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody id="ownerReservationsBody"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal: SAHA DURUMU VE KAPALI TARİH/SAAT ARALIĞI AYARLA MODALI -->
<div class="modal fade" id="fieldStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-wrench text-warning me-2"></i> Saha Durumu ve Kapalı Tarih/Saat Aralığı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="handleSaveFieldStatusRange(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="statusFieldId">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">SAHA ADI</label>
                        <input type="text" class="form-control fw-bold" id="statusFieldName" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fs-7 fw-semibold">DURUM SEÇİMİ</label>
                        <select class="form-select fw-bold" id="statusSelect" onchange="onStatusSelectChange(this)">
                            <option value="Aktif">✅ Aktif (Açık - Tüm Saatler Kiralanabilir)</option>
                            <option value="Pasif">🔴 Kapalı (Tarih ve Saat Aralıklı)</option>
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-3" id="closedRangeFormContainer">
                        <h6 class="fw-bold text-danger fs-8 mb-2"><i class="fa-solid fa-calendar-xmark me-1"></i> KAPALI KALACAĞI TARİH VE SAAT ARALIĞI</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="fs-8 text-muted">Başlangıç Tarihi</label>
                                <input type="date" class="form-control form-control-sm" id="fieldClosedStartDate">
                            </div>
                            <div class="col-6">
                                <label class="fs-8 text-muted">Başlangıç Saati</label>
                                <input type="time" class="form-control form-control-sm" id="fieldClosedStartTime" value="00:00">
                            </div>
                            <div class="col-6">
                                <label class="fs-8 text-muted">Bitiş Tarihi</label>
                                <input type="date" class="form-control form-control-sm" id="fieldClosedEndDate">
                            </div>
                            <div class="col-6">
                                <label class="fs-8 text-muted">Bitiş Saati</label>
                                <input type="time" class="form-control form-control-sm" id="fieldClosedEndTime" value="23:59">
                            </div>
                            <div class="col-12 mt-2">
                                <label class="fs-8 text-muted">Kapanış Nedeni</label>
                                <input type="text" class="form-control form-control-sm" id="fieldClosedReason" placeholder="Örn: Çim Bakımı, Projektör Tamiri">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team fw-bold">Ayarları Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: İŞLETMECİ ELDEN ABONMAN TANIMLAMA MODALI -->
<div class="modal fade" id="ownerAddSubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-crown text-warning me-2"></i> Müşteriye Özel Abonman Paketi Tanımla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="handleSaveOwnerSubscription(event)">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">MÜŞTERİ AD SOYAD *</label>
                            <input type="text" class="form-control fw-bold" name="user_name" required placeholder="Örn: Ahmet Yılmaz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">BENZERSİZ KULLANICI ADI (@username)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" class="form-control fw-bold" name="username" placeholder="oyuncu1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                            <input type="text" class="form-control" name="user_phone" required placeholder="0532 555 12 34">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TAKIM ADI *</label>
                            <input type="text" class="form-control" name="team_name" required placeholder="Örn: Karaköy Gücü">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA SEÇİMİ *</label>
                            <select class="form-select fw-bold" name="field_id" id="ownerSubFieldSelect" onchange="checkOwnerPeriodicAvailabilityLive()" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">PAKET SEÇİMİ *</label>
                            <select class="form-select fw-bold" name="package_type" id="ownerSubPackageSelect" onchange="checkOwnerPeriodicAvailabilityLive()" required>
                                <option value="1_month" selected>🔵 Aylık Paket (4 Maç - %10 İndirim)</option>
                                <option value="3_months">🟡 3 Aylık Paket (12 Maç - %15 İndirim)</option>
                                <option value="6_months">👑 6 Aylık Paket (24 Maç - %20 İndirim VIP)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">KULLANIM MODU *</label>
                            <select class="form-select fw-bold" name="booking_mode" id="ownerSubModeSelect" onchange="toggleOwnerPeriodicOptions()" required>
                                <option value="flexible" selected>🎯 Esnek Kullanım (Maç Kredisi Ver)</option>
                                <option value="periodic">📅 Sabit Periyodik Kullanım (Haftalık Otomatik Kitle)</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" id="ownerPeriodicContainer">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="fs-8 text-muted fw-semibold">HAFTANIN GÜNÜ</label>
                                        <select class="form-select form-select-sm fw-bold" name="preferred_day" id="ownerSubPreferredDay" onchange="checkOwnerPeriodicAvailabilityLive()">
                                            <option value="Pazartesi">Pazartesi</option>
                                            <option value="Salı">Salı</option>
                                            <option value="Çarşamba" selected>Çarşamba</option>
                                            <option value="Perşembe">Perşembe</option>
                                            <option value="Cuma">Cuma</option>
                                            <option value="Cumartesi">Cumartesi</option>
                                            <option value="Pazar">Pazar</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-8 text-muted fw-semibold">SABİT SAAT</label>
                                        <select class="form-select form-select-sm fw-bold" name="preferred_time" id="ownerSubPreferredTime" onchange="checkOwnerPeriodicAvailabilityLive()">
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="20:00" selected>20:00</option>
                                            <option value="21:00">21:00</option>
                                            <option value="22:00">22:00</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CANLI ÇAKIŞMA UYARI KUTUSU -->
                        <div id="ownerSubAvailabilityAlert" class="col-12 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-team" id="ownerSubSubmitBtn">Abonmanlığı Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: RANDEVU DETAY POPUP -->
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
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA ADI *</label>
                            <input type="text" class="form-control" name="field_name" id="modal_field_name" required placeholder="Örn: Futbol Sahası 1">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA TİPİ</label>
                            <select class="form-select" name="field_type" id="modal_field_type">
                                <option value="Kapalı Futbol Sahası">⚽ Kapalı Futbol Sahası</option>
                                <option value="Açık Futbol Sahası">⚽ Açık Futbol Sahası</option>
                                <option value="Kapalı Basketbol Sahası">🏀 Kapalı Basketbol Sahası</option>
                                <option value="Açık Tenis Kortu">🎾 Açık Tenis Kortu</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">SAATLİK ÜCRET (TL) *</label>
                            <input type="number" step="0.01" class="form-control" name="hourly_fee" id="modal_hourly_fee" required value="1200.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA DURUMU</label>
                            <select class="form-select" name="status" id="modal_field_status">
                                <option value="Aktif" selected>✅ Aktif (Açık)</option>
                                <option value="Pasif">🔴 Kapalı</option>
                            </select>
                        </div>
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

<!-- Modal: Walk-in / Elden Hızlı Randevu Ekle (SEARCHABLE ABONMAN PICKER CARD SYSTEM) -->
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

                        <!-- YENİLENEN ARAMA DESTEKLİ MÜŞTERİ & ABONMAN SEÇİM ARAYÜZÜ -->
                        <div class="col-12">
                            <label class="form-label text-dark fs-7 fw-bold mb-2"><i class="fa-solid fa-credit-card me-1 text-primary"></i> ÖDEME YÖNTEMİ VE MÜŞTERİ SEÇİMİ</label>
                            <input type="hidden" name="use_subscription_id" id="walkinUseSubId" value="0">
                            <input type="hidden" name="username" id="walkinUsername" value="">

                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="payment_mode_radio" id="payModeStandard" value="standard" checked onchange="switchWalkinPayMode('standard')">
                                <label class="btn btn-outline-secondary fw-bold fs-7 py-2" for="payModeStandard">💵 Standart Elden Ödeme (Ücretli)</label>

                                <input type="radio" class="btn-check" name="payment_mode_radio" id="payModeSub" value="subscription" onchange="switchWalkinPayMode('subscription')">
                                <label class="btn btn-outline-warning text-dark fw-bold fs-7 py-2" for="payModeSub">🎁 Müşteri Abonman Kredisi Kullan</label>
                            </div>

                            <!-- SEARCHABLE ABONMAN PICKER CONTAINER -->
                            <div id="walkinSubPickerContainer" class="p-3 bg-light rounded-3 border d-none">
                                <div id="walkinSubSearchState">
                                    <label class="form-label text-warning fs-8 fw-bold mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> MÜŞTERİ ABONMANI ARA (@kullanıcı_adı, İsim veya Telefon)</label>
                                    <input type="text" class="form-control form-control-sm fw-bold border-warning mb-2" id="walkinSubSearchInput" placeholder="Örn: @oyuncu1, Ahmet Yılmaz, 0532..." oninput="filterWalkinSubPicker()">
                                    
                                    <div id="walkinSubCardList" class="d-flex flex-column gap-2 overflow-auto" style="max-height: 180px;">
                                        <!-- Dynamic cards populated here -->
                                    </div>
                                </div>

                                <!-- SELECTED SUBSCRIPTION BADGE CARD -->
                                <div id="walkinSubSelectedState" class="d-none p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark fs-7"><i class="fa-solid fa-crown me-1"></i> SEÇİLİ ABONMAN</span>
                                            <div>
                                                <div class="fw-bold text-dark fs-6" id="selectedSubCustomerName">--</div>
                                                <span class="badge bg-light text-primary border" id="selectedSubUsername">@--</span>
                                                <span class="text-muted fs-8 ms-1" id="selectedSubPhone">--</span>
                                                <span class="badge bg-success text-white ms-2" id="selectedSubRemaining">Kalan: -- Maç Kredisi</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="clearWalkinSelectedSub()">❌ Değiştir / Kaldır</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TAKIM ADI *</label>
                            <input type="text" class="form-control" name="team_name" id="walkinTeamName" required placeholder="Örn: Karaköy Gücü">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">YETKİLİ KİŞİ *</label>
                            <input type="text" class="form-control" name="contact_name" id="walkinContactName" required placeholder="Ad Soyad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                            <input type="text" class="form-control" name="phone" id="walkinPhone" required placeholder="05XX XXX XX XX">
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
                            <input type="text" class="form-control fw-bold text-success" name="fee" id="walkinFeeInput" value="1200.00" required>
                        </div>
                        <input type="hidden" name="status" value="Onaylandı">
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
function getLiveClientDateAndHour() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const todayStr = `${year}-${month}-${day}`;
    const currentHour = now.getHours();
    return { todayStr, currentHour };
}

function isSlotInPast(dateStr, timeStr, openTimeStr) {
    const { todayStr, currentHour } = getLiveClientDateAndHour();
    if (dateStr < todayStr) return true;
    if (dateStr > todayStr) return false;

    const hourNum = parseInt(timeStr.split(':')[0], 10);
    const openH = parseInt((openTimeStr || '13').split(':')[0], 10);

    if (hourNum < openH) {
        return true;
    }
    return (hourNum <= currentHour);
}

const CITIES_DISTRICTS = {
    'İstanbul': ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Beyoğlu', 'Maltepe', 'Ataşehir', 'Ümraniye', 'Bakırköy', 'Fatih', 'Pendik', 'Sarıyer'],
    'Ankara': ['Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Gölbaşı'],
    'İzmir': ['Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Alsancak', 'Çiğli', 'Gaziemir'],
    'Bursa': ['Nilüfer', 'Osmangazi', 'Yıldırım', 'Mudanya'],
    'Antalya': ['Muratpaşa', 'Konyaaltı', 'Kepez', 'Alanya']
};

document.addEventListener('DOMContentLoaded', () => {
    startLiveClock();
    loadOwnerFacility();
    loadOwnerReservations();
    loadOwnerSubscriptions();
});

function startLiveClock() {
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('liveSystemClock');
        if (clockEl) {
            clockEl.innerText = now.toLocaleTimeString('tr-TR');
        }
    }
    updateClock();
    setInterval(updateClock, 1000);
}

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
let ownerSubscriptionsData = [];
let activeReservationTab = 'today';
let currentSortColumn = 'reservation_date';
let currentSortAsc = true;

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
        document.getElementById('fac_open_time_weekend').value = json.facility.open_time_weekend || '09:00';
        document.getElementById('fac_close_time_weekend').value = json.facility.close_time_weekend || '03:00';

        const facFeats = json.facility.features_array || [];
        document.getElementById('fac_feat_camera').checked = facFeats.includes('HD Kamera Kaydı');
        document.getElementById('fac_feat_water').checked = facFeats.includes('Ücretsiz Su & İkram');
        document.getElementById('fac_feat_shower').checked = facFeats.includes('Soyunma Odası & Duş');
        document.getElementById('fac_feat_shoes').checked = facFeats.includes('Krampon / Ayakkabı Kiralama');

        renderClosedDatesBadges(json.facility.closed_dates_array || []);
        renderOwnerFields(json.fields);
        populateWalkinSelects(json.facility, json.fields);
        populateReservationFieldFilter(json.fields);
        renderOwnerMatrix();
    }
}

function populateReservationFieldFilter(fields) {
    const filterSel = document.getElementById('filterReservationField');
    let html = `<option value="all">Tüm Sahalar</option>`;
    fields.forEach(f => {
        html += `<option value="${f.id}">${escapeHtml(f.field_name)}</option>`;
    });
    filterSel.innerHTML = html;
}

function renderClosedDatesBadges(closedArray) {
    const container = document.getElementById('closedDatesBadgeList');
    if (closedArray.length === 0) {
        container.innerHTML = `<span class="text-muted">Kayıtlı kapalı tarih bulunmuyor.</span>`;
        return;
    }
    let html = '';
    closedArray.forEach(item => {
        const start = isObject(item) ? (item.start || item.date) : item;
        const end = isObject(item) ? (item.end || item.start || item.date) : item;
        const r = isObject(item) ? (item.reason || 'Kapalı') : 'Kapalı';
        const dateRangeText = (start === end) ? start : `${start} - ${end}`;

        html += `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger p-1.5 me-1 mb-1">
            🚫 ${dateRangeText} (${escapeHtml(r)}) <i class="fa-solid fa-xmark ms-1 cursor-pointer" onclick="removeClosedDate('${start}')"></i>
        </span>`;
    });
    container.innerHTML = html;
}

function isObject(val) {
    return val !== null && typeof val === 'object';
}

async function removeClosedDate(startStr) {
    if (!confirm(`${startStr} kapalı gün engelini kaldırmak istiyor musunuz?`)) return;
    const formData = new FormData();
    formData.append('start', startStr);

    const res = await fetch('api/facility.php?action=remove_closed_date', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') loadOwnerFacility();
}

function renderOwnerFields(fields) {
    const tbody = document.getElementById('ownerFieldsList');
    if (fields.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">Henüz eklenmiş saha yok.</td></tr>`;
        return;
    }

    const { todayStr, currentHour } = getLiveClientDateAndHour();
    const currentFormattedH = (currentHour < 10 ? '0' : '') + currentHour + ':00';

    let html = '';
    fields.forEach(f => {
        let icon = '⚽';
        if (f.field_name.includes('Basketbol') || (f.field_type && f.field_type.includes('Basketbol'))) icon = '🏀';
        else if (f.field_name.includes('Tenis') || (f.field_type && f.field_type.includes('Tenis'))) icon = '🎾';

        let liveStatusBadge = '';
        if (f.status === 'Pasif') {
            liveStatusBadge = `<span class="badge bg-danger text-white">🔴 Kapalı</span>`;
        } else {
            const isBookedNow = ownerReservationsData.some(r => r.field_id == f.id && r.reservation_date === todayStr && r.reservation_time === currentFormattedH && r.status !== 'İptal');
            if (isBookedNow) {
                liveStatusBadge = `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">🔴 Dolu (Maç Var)</span>`;
            } else {
                liveStatusBadge = `<span class="badge bg-success bg-opacity-10 text-success border border-success">🟢 Boşta</span>`;
            }
        }

        const isPassive = (f.status === 'Pasif');
        const toggleBtn = `<button class="btn btn-sm ${isPassive ? 'btn-outline-danger' : 'btn-outline-primary'}" onclick="openFieldStatusModal(${f.id}, '${escapeHtml(f.field_name)}', '${f.status}')">⚙️ Durumu Ayarla</button>`;

        html += `<tr>
            <td class="fw-bold text-dark">${icon} ${escapeHtml(f.field_name)}</td>
            <td><span class="badge bg-light text-dark border">${escapeHtml(f.field_type)}</span></td>
            <td>${liveStatusBadge}</td>
            <td>${toggleBtn}</td>
            <td class="fw-bold text-dark">₺${parseFloat(f.hourly_fee).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-info me-1" onclick="editField(${f.id})"><i class="fa-solid fa-pen"></i> Düzenle</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteField(${f.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function openFieldStatusModal(fieldId, fieldName, currentStatus) {
    const { todayStr } = getLiveClientDateAndHour();
    const f = ownerFieldsData.find(item => item.id == fieldId);
    document.getElementById('statusFieldId').value = fieldId;
    document.getElementById('statusFieldName').value = fieldName;
    
    const selectEl = document.getElementById('statusSelect');
    selectEl.value = currentStatus || 'Aktif';

    const range = (f && f.closed_range_obj) ? f.closed_range_obj : {};
    document.getElementById('fieldClosedStartDate').value = range.start_date || todayStr;
    document.getElementById('fieldClosedStartDate').min = todayStr;
    document.getElementById('fieldClosedStartTime').value = range.start_time || '00:00';
    document.getElementById('fieldClosedEndDate').value = range.end_date || todayStr;
    document.getElementById('fieldClosedEndDate').min = todayStr;
    document.getElementById('fieldClosedEndTime').value = range.end_time || '23:59';
    document.getElementById('fieldClosedReason').value = range.reason || 'Bakım / Tamir';

    onStatusSelectChange(selectEl);
    new bootstrap.Modal(document.getElementById('fieldStatusModal')).show();
}

function onStatusSelectChange(selectEl) {
    const container = document.getElementById('closedRangeFormContainer');
    if (selectEl.value === 'Aktif') {
        container.style.opacity = '0.5';
    } else {
        container.style.opacity = '1.0';
    }
}

async function handleSaveFieldStatusRange(e) {
    e.preventDefault();
    const statusVal = document.getElementById('statusSelect').value;

    const formData = new FormData();
    formData.append('field_id', document.getElementById('statusFieldId').value);
    formData.append('status', statusVal);

    if (statusVal === 'Pasif') {
        formData.append('closed_start_date', document.getElementById('fieldClosedStartDate').value);
        formData.append('closed_start_time', document.getElementById('fieldClosedStartTime').value);
        formData.append('closed_end_date', document.getElementById('fieldClosedEndDate').value);
        formData.append('closed_end_time', document.getElementById('fieldClosedEndTime').value);
        formData.append('closed_reason', document.getElementById('fieldClosedReason').value);
    } else {
        formData.append('closed_start_date', '');
    }

    const res = await fetch('api/facility.php?action=set_field_closed_range', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('fieldStatusModal')).hide();
        loadOwnerFacility();
    } else {
        alert(json.message);
    }
}

function onMatrixDateInput(inputEl) {
    const val = inputEl.value;
    if (!val || val.length !== 10) return;

    const parts = val.split('-');
    const year = parseInt(parts[0], 10);
    if (isNaN(year) || year < 2026) return;

    renderOwnerMatrix();
}

function isSlotClosedByRange(dateStr, timeStr, fieldObj) {
    if (!fieldObj) return false;
    if (fieldObj.status === 'Pasif' && (!fieldObj.closed_range_obj || !fieldObj.closed_range_obj.start_date)) return true;

    const range = fieldObj.closed_range_obj;
    if (!range || !range.start_date) return false;

    const slotDt = `${dateStr} ${timeStr}`;
    const startDt = `${range.start_date} ${range.start_time || '00:00'}`;
    const endDt = `${range.end_date || range.start_date} ${range.end_time || '23:59'}`;

    return (slotDt >= startDt && slotDt <= endDt);
}

function renderOwnerMatrix() {
    if (!ownerFacilityData || ownerFieldsData.length === 0) return;
    const { todayStr } = getLiveClientDateAndHour();
    const dateInput = document.getElementById('matrixDate');
    const date = (dateInput && dateInput.value && dateInput.value.length === 10) ? dateInput.value : todayStr;

    const parts = date.split('-');
    const dateObj = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    const isWeekend = (dateObj.getDay() === 0 || dateObj.getDay() === 6);

    let activeOpenStr = (isWeekend && ownerFacilityData.open_time_weekend) ? ownerFacilityData.open_time_weekend : (ownerFacilityData.open_time || '13:00');
    let activeCloseStr = (isWeekend && ownerFacilityData.close_time_weekend) ? ownerFacilityData.close_time_weekend : (ownerFacilityData.close_time || '01:00');

    const openH = parseInt(activeOpenStr.split(':')[0], 10);
    let closeH = parseInt(activeCloseStr.split(':')[0], 10);
    if (closeH <= openH) closeH += 24;

    const hourNumbers = [];
    for (let h = openH; h < closeH; h++) {
        hourNumbers.push(h % 24);
    }
    hourNumbers.sort((a, b) => a - b);

    const hours = hourNumbers.map(h => (h < 10 ? '0' : '') + h + ':00');

    const header = document.getElementById('ownerMatrixHeader');
    let hHtml = `<th class="text-start">SAHA ADI</th>`;
    hours.forEach(h => hHtml += `<th>${h}</th>`);
    header.innerHTML = hHtml;

    const tbody = document.getElementById('ownerMatrixBody');
    let bHtml = '';

    ownerFieldsData.forEach(field => {
        let icon = '⚽';
        if (field.field_name.includes('Basketbol') || (field.field_type && field.field_type.includes('Basketbol'))) icon = '🏀';
        else if (field.field_name.includes('Tenis') || (field.field_type && field.field_type.includes('Tenis'))) icon = '🎾';

        bHtml += `<tr><td class="fw-bold text-dark text-start py-2">${icon} ${escapeHtml(field.field_name)}</td>`;

        hours.forEach(h => {
            const isClosedSlot = isSlotClosedByRange(date, h, field);

            if (isClosedSlot) {
                bHtml += `<td><div class="slot-badge bg-danger bg-opacity-10 text-danger border border-danger" style="cursor:not-allowed;" title="Kapalı">KAPALI</div></td>`;
                return;
            }

            const booking = ownerReservationsData.find(r => r.field_id == field.id && r.reservation_date === date && r.reservation_time === h && r.status !== 'İptal');

            if (booking) {
                const isSub = booking.subscription_plan && booking.subscription_plan !== 'Standart';
                const badgeClass = isSub ? 'slot-busy-sub' : 'slot-busy-normal';
                const iconClass = isSub ? 'fa-crown' : 'fa-lock';

                bHtml += `<td>
                    <div class="slot-badge ${badgeClass}" onclick="showBookingDetails(${booking.id})" title="Detay için tıkla">
                        <i class="fa-solid ${iconClass} me-1"></i>${h}
                    </div>
                </td>`;
            } else if (isSlotInPast(date, h, activeOpenStr)) {
                bHtml += `<td><div class="slot-badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25" style="cursor:not-allowed;" title="Saat Geçti"><i class="fa-solid fa-clock-rotate-left me-1"></i>GEÇTİ</div></td>`;
            } else {
                bHtml += `<td>
                    <div class="slot-badge slot-free" onclick="quickWalkinModal(${field.id}, '${date}', '${h}')" title="Hızlı Elden Kayıt">
                        +${h}
                    </div>
                </td>`;
            }
        });

        bHtml += `</tr>`;
    });

    tbody.innerHTML = bHtml;
}

async function loadOwnerSubscriptions() {
    const res = await fetch('api/facility.php?action=list_owner_subscriptions');
    const json = await res.json();
    if (json.status === 'success') {
        ownerSubscriptionsData = json.data || [];
        renderOwnerSubscriptions(ownerSubscriptionsData);
    }
}

function renderOwnerSubscriptions(list) {
    const tbody = document.getElementById('ownerSubscriptionsBody');
    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-3">Tesisinize tanımlı abonmanlık bulunmuyor.</td></tr>`;
        return;
    }
    let html = '';
    list.forEach(s => {
        const modeBadge = (s.booking_mode === 'periodic') ? `<span class="badge bg-info bg-opacity-10 text-info border border-info">📅 Periyodik (${escapeHtml(s.preferred_day)} ${s.preferred_time})</span>` : `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary">🎯 Esnek Kredi</span>`;
        const statusBadge = (s.status === 'Aktif') ? `<span class="badge bg-success text-white">✅ Aktif</span>` : `<span class="badge bg-secondary text-white">🏁 Tamamlandı</span>`;
        const usernameTag = s.username ? `@${escapeHtml(s.username)}` : '@oyuncu1';

        html += `<tr>
            <td class="px-3 py-3">
                <div class="fw-bold text-dark fs-7">${escapeHtml(s.user_name)}</div>
                <span class="badge bg-light text-primary border fs-8">${usernameTag}</span>
            </td>
            <td class="px-3 py-3">${escapeHtml(s.user_phone)}</td>
            <td class="px-3 py-3"><span class="badge bg-warning bg-opacity-10 text-warning border border-warning fw-bold">${escapeHtml(s.package_name)}</span></td>
            <td class="px-3 py-3 fw-semibold">${escapeHtml(s.field_name)}</td>
            <td class="px-3 py-3">${modeBadge}</td>
            <td class="px-3 py-3 fw-bold">${s.total_matches} Maç</td>
            <td class="px-3 py-3"><strong class="text-success fs-6">${s.remaining_matches} Kredi</strong></td>
            <td class="px-3 py-3 fw-bold text-dark">₺${parseFloat(s.total_price).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
            <td class="px-3 py-3">${statusBadge}</td>
            <td class="px-3 py-3 text-end">
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSubscription(${s.id})"><i class="fa-solid fa-trash me-1"></i> İptal Et / Sil</button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function toggleOwnerPeriodicOptions() {
    const mode = document.getElementById('ownerSubModeSelect').value;
    const container = document.getElementById('ownerPeriodicContainer');
    if (mode === 'periodic') {
        container.classList.remove('d-none');
        checkOwnerPeriodicAvailabilityLive();
    } else {
        container.classList.add('d-none');
        document.getElementById('ownerSubAvailabilityAlert').classList.add('d-none');
        document.getElementById('ownerSubSubmitBtn').disabled = false;
    }
}

async function checkOwnerPeriodicAvailabilityLive() {
    const mode = document.getElementById('ownerSubModeSelect').value;
    const alertBox = document.getElementById('ownerSubAvailabilityAlert');
    const submitBtn = document.getElementById('ownerSubSubmitBtn');

    if (mode !== 'periodic') {
        alertBox.classList.add('d-none');
        if (submitBtn) submitBtn.disabled = false;
        return;
    }

    const fieldId = document.getElementById('ownerSubFieldSelect').value;
    const packageType = document.getElementById('ownerSubPackageSelect').value;
    const preferredDay = document.getElementById('ownerSubPreferredDay').value;
    const preferredTime = document.getElementById('ownerSubPreferredTime').value;

    if (!fieldId || fieldId == 0) return;

    const res = await fetch(`api/facility.php?action=check_subscription_availability&field_id=${fieldId}&package_type=${packageType}&preferred_day=${encodeURIComponent(preferredDay)}&preferred_time=${encodeURIComponent(preferredTime)}`);
    const json = await res.json();

    if (json.status === 'success' && json.has_conflict) {
        const conflictText = json.conflicts.map(c => `📅 <strong>${c.date}</strong> (${escapeHtml(c.team)})`).join('<br>');
        alertBox.className = 'col-12 alert alert-danger fw-semibold fs-7 mb-0 shadow-sm';
        alertBox.innerHTML = `<div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-5 mt-0.5"></i>
            <div>
                <strong class="text-danger d-block mb-1">⚠️ DOLULUK ÇAKIŞMASI TESPİT EDİLDİ!</strong>
                <p class="mb-1 fs-8">Seçtiğiniz <u>${escapeHtml(preferredDay)} - ${preferredTime}</u> saatinde önümüzdeki ${json.total_matches} hafta içerisinde daha önceden alınmış randevular var:</p>
                <div class="p-2 bg-white rounded border border-danger border-opacity-25 fs-8 mb-2">${conflictText}</div>
                <span class="fs-8 text-dark d-block">💡 <strong>Çözüm:</strong> Lütfen boş bir gün/saat seçiniz ya da <strong>'Esnek Kullanım'</strong> modunu seçerek maç kredisi veriniz.</span>
            </div>
        </div>`;
        alertBox.classList.remove('d-none');
        if (submitBtn) submitBtn.disabled = true;
    } else {
        alertBox.className = 'col-12 alert alert-success fw-semibold fs-7 mb-0 shadow-sm';
        alertBox.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> <strong>SABİT RANDEVU UYGUNDUR:</strong> Önümüzdeki ${json.total_matches} haftanın tamamı bu gün ve saatte boştur! Müşteri abonmanlığı kilitlenebilir.`;
        alertBox.classList.remove('d-none');
        if (submitBtn) submitBtn.disabled = false;
    }
}

async function deleteSubscription(subId) {
    if (!confirm('Bu abonmanlık kaydını iptal etmek / silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('sub_id', subId);

    const res = await fetch('api/facility.php?action=delete_subscription', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
        loadOwnerSubscriptions();
        loadOwnerReservations();
    } else {
        alert(json.message);
    }
}

function openOwnerAddSubModal() {
    const selectEl = document.getElementById('ownerSubFieldSelect');
    selectEl.innerHTML = ownerFieldsData.map(f => `<option value="${f.id}">${escapeHtml(f.field_name)} (₺${parseFloat(f.hourly_fee).toLocaleString('tr-TR')}/Saat)</option>`).join('');
    new bootstrap.Modal(document.getElementById('ownerAddSubModal')).show();
}

async function handleSaveOwnerSubscription(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/facility.php?action=add_owner_subscription', { method: 'POST', body: formData });
    const json = await res.json();
    alert(json.message);
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('ownerAddSubModal')).hide();
        loadOwnerSubscriptions();
        loadOwnerReservations();
    }
}

function showBookingDetails(id) {
    const r = ownerReservationsData.find(item => item.id == id);
    if (!r) return;

    const usernameTag = r.username ? `@${escapeHtml(r.username)}` : '@oyuncu1';

    document.getElementById('detailTitle').innerText = `Randevu #${r.id}`;
    document.getElementById('detailContent').innerHTML = `
        <p class="mb-1"><strong>Takım Adı:</strong> ${escapeHtml(r.team_name)}</p>
        <p class="mb-1"><strong>Yetkili / @Kullanıcı:</strong> ${escapeHtml(r.contact_name)} <span class="badge bg-light text-primary border">${usernameTag}</span></p>
        <p class="mb-1"><strong>Telefon:</strong> ${escapeHtml(r.phone)}</p>
        <p class="mb-1"><strong>Saha:</strong> ${escapeHtml(r.field_name)}</p>
        <p class="mb-1"><strong>Tarih / Saat:</strong> ${r.reservation_date} - ${r.reservation_time}</p>
        <p class="mb-1"><strong>Ücret:</strong> ₺${parseFloat(r.fee).toLocaleString('tr-TR')}</p>
        <p class="mb-0"><strong>Paket:</strong> <span class="badge bg-warning text-dark">${escapeHtml(r.subscription_plan)}</span></p>
    `;

    new bootstrap.Modal(document.getElementById('reservationDetailsModal')).show();
}

function quickWalkinModal(fieldId, date, time) {
    const openTime = ownerFacilityData ? ownerFacilityData.open_time : '13:00';
    if (isSlotInPast(date, time, openTime)) {
        alert('⚠️ Geçmiş bir tarihe veya saate randevu eklenemez!');
        return;
    }

    document.getElementById('walkinFieldSelect').value = fieldId;
    document.getElementById('walkinDate').value = date;
    document.getElementById('walkinTimeSelect').value = time;

    // Reset payment mode radio to standard
    document.getElementById('payModeStandard').checked = true;
    switchWalkinPayMode('standard');

    new bootstrap.Modal(document.getElementById('walkinModal')).show();
}

function switchWalkinPayMode(mode) {
    const picker = document.getElementById('walkinSubPickerContainer');
    const feeInput = document.getElementById('walkinFeeInput');

    if (mode === 'subscription') {
        picker.classList.remove('d-none');
        filterWalkinSubPicker();
    } else {
        picker.classList.add('d-none');
        clearWalkinSelectedSub();
        const fieldId = document.getElementById('walkinFieldSelect').value;
        const field = ownerFieldsData.find(f => f.id == fieldId);
        feeInput.value = field ? field.hourly_fee : '1200.00';
    }
}

function filterWalkinSubPicker() {
    const query = document.getElementById('walkinSubSearchInput').value.toLowerCase().trim();
    const container = document.getElementById('walkinSubCardList');

    const activeSubs = ownerSubscriptionsData.filter(s => s.remaining_matches > 0 && s.status === 'Aktif');

    let filtered = activeSubs;
    if (query) {
        filtered = activeSubs.filter(s => 
            (s.user_name && s.user_name.toLowerCase().includes(query)) ||
            (s.username && s.username.toLowerCase().includes(query)) ||
            (s.user_phone && s.user_phone.includes(query)) ||
            (s.package_name && s.package_name.toLowerCase().includes(query))
        );
    }

    if (filtered.length === 0) {
        container.innerHTML = `<div class="p-3 text-center text-muted fs-8">Eşleşen aktif abonman kredisi bulunamadı.</div>`;
        return;
    }

    let html = '';
    filtered.forEach(s => {
        const usernameTag = s.username ? `@${escapeHtml(s.username)}` : '@oyuncu1';
        html += `<div class="p-2.5 bg-white rounded-3 border d-flex align-items-center justify-content-between cursor-pointer hover-shadow" onclick="selectWalkinSubCard(${s.id})">
            <div>
                <div class="fw-bold text-dark fs-7">${escapeHtml(s.user_name)} <span class="badge bg-light text-primary border ms-1">${usernameTag}</span></div>
                <span class="text-muted fs-8"><i class="fa-solid fa-phone text-success me-1"></i>${escapeHtml(s.user_phone)} &bull; <strong class="text-warning">${escapeHtml(s.package_name)}</strong></span>
            </div>
            <div class="text-end">
                <span class="badge bg-success text-white fw-bold fs-8">🎁 Kalan: ${s.remaining_matches} Maç</span>
                <span class="d-block text-muted fs-8">₺0.00 / Maç</span>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function selectWalkinSubCard(subId) {
    const s = ownerSubscriptionsData.find(item => item.id == subId);
    if (!s) return;

    document.getElementById('walkinUseSubId').value = s.id;
    document.getElementById('walkinUsername').value = s.username || 'oyuncu1';

    document.getElementById('walkinContactName').value = s.user_name;
    document.getElementById('walkinPhone').value = s.user_phone;
    document.getElementById('walkinTeamName').value = s.user_name + ' (Abonman)';
    document.getElementById('walkinFeeInput').value = '0.00 (Abonman Kredisi)';

    document.getElementById('selectedSubCustomerName').innerText = s.user_name;
    document.getElementById('selectedSubUsername').innerText = s.username ? `@${s.username}` : '@oyuncu1';
    document.getElementById('selectedSubPhone').innerText = s.user_phone;
    document.getElementById('selectedSubRemaining').innerText = `Kalan: ${s.remaining_matches} Maç Kredisi`;

    document.getElementById('walkinSubSearchState').classList.add('d-none');
    document.getElementById('walkinSubSelectedState').classList.remove('d-none');
}

function clearWalkinSelectedSub() {
    document.getElementById('walkinUseSubId').value = '0';
    document.getElementById('walkinUsername').value = '';

    const searchInput = document.getElementById('walkinSubSearchInput');
    if (searchInput) searchInput.value = '';

    document.getElementById('walkinSubSearchState').classList.remove('d-none');
    document.getElementById('walkinSubSelectedState').classList.add('d-none');

    const fieldId = document.getElementById('walkinFieldSelect').value;
    const field = ownerFieldsData.find(f => f.id == fieldId);
    if (field) {
        document.getElementById('walkinFeeInput').value = field.hourly_fee;
    }
}

function populateWalkinSelects(facility, fields) {
    const activeFields = fields.filter(f => f.status === 'Aktif');
    const fieldSel = document.getElementById('walkinFieldSelect');
    fieldSel.innerHTML = activeFields.map(f => `<option value="${f.id}">${escapeHtml(f.field_name)}</option>`).join('');

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
    document.getElementById('modal_field_status').value = 'Aktif';
    document.getElementById('fieldModalTitle').innerText = 'Yeni Saha Ekle';

    new bootstrap.Modal(document.getElementById('fieldModal')).show();
}

function editField(id) {
    const f = ownerFieldsData.find(item => item.id == id);
    if (!f) return;

    document.getElementById('modal_field_id').value = f.id;
    document.getElementById('modal_field_name').value = f.field_name;
    document.getElementById('modal_field_type').value = f.field_type;
    document.getElementById('modal_hourly_fee').value = f.hourly_fee;
    document.getElementById('modal_field_status').value = f.status || 'Aktif';
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

    if (json.status === 'success') {
        ownerReservationsData = json.data;
        renderOwnerMatrix();
        filterReservations();
    }
}

function setReservationTab(tab) {
    activeReservationTab = tab;
    document.querySelectorAll('#reservationFilterTabs .nav-link').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');
    filterReservations();
}

function sortReservationsBy(column) {
    if (currentSortColumn === column) {
        currentSortAsc = !currentSortAsc;
    } else {
        currentSortColumn = column;
        currentSortAsc = true;
    }

    ['team_name', 'contact_name', 'reservation_date', 'reservation_time', 'fee'].forEach(col => {
        const el = document.getElementById(`sort-${col}`);
        if (el) el.innerHTML = `<i class="fa-solid fa-sort text-muted fs-8 ms-1"></i>`;
    });

    const activeIconEl = document.getElementById(`sort-${column}`);
    if (activeIconEl) {
        activeIconEl.innerHTML = currentSortAsc ? `<i class="fa-solid fa-caret-up text-primary fs-7 ms-1"></i>` : `<i class="fa-solid fa-caret-down text-primary fs-7 ms-1"></i>`;
    }

    filterReservations();
}

function computeMatchStatusBadge(resDate, resTime) {
    const { todayStr, currentHour } = getLiveClientDateAndHour();
    const resHour = parseInt(resTime.split(':')[0], 10);

    if (resDate < todayStr) {
        return `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="fa-solid fa-flag-checkered me-1"></i>🏁 Bitti</span>`;
    }
    if (resDate > todayStr) {
        return `<span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1"><i class="fa-solid fa-hourglass-half me-1 text-warning"></i>⏳ Bekliyor</span>`;
    }

    if (resHour < currentHour && resHour >= 8) {
        return `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="fa-solid fa-flag-checkered me-1"></i>🏁 Bitti</span>`;
    } else if (resHour === currentHour) {
        return `<span class="badge bg-success text-white px-2.5 py-1 shadow-sm"><i class="fa-solid fa-futbol me-1"></i>⚽ Başladı (Maç Oynanıyor)</span>`;
    } else {
        return `<span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1"><i class="fa-solid fa-hourglass-half me-1 text-warning"></i>⏳ Bekliyor</span>`;
    }
}

function filterReservations() {
    const { todayStr, currentHour } = getLiveClientDateAndHour();
    const query = document.getElementById('searchReservationQuery').value.toLowerCase().trim();
    const selectedFieldId = document.getElementById('filterReservationField').value;
    const tbody = document.getElementById('ownerReservationsBody');

    let todayRes = [];
    let futureRes = [];
    let pastRes = [];
    let income = 0;
    let playedOrFinishedCount = 0;

    ownerReservationsData.forEach(r => {
        const resH = parseInt(r.reservation_time.split(':')[0], 10);

        if (r.reservation_date === todayStr) {
            todayRes.push(r);
            income += parseFloat(r.fee);
            if (resH <= currentHour) playedOrFinishedCount++;
        } else if (r.reservation_date > todayStr) {
            futureRes.push(r);
        } else {
            pastRes.push(r);
            playedOrFinishedCount++;
        }
    });

    document.getElementById('tabCountToday').innerText = todayRes.length;
    document.getElementById('tabCountFuture').innerText = futureRes.length;
    document.getElementById('tabCountPast').innerText = pastRes.length;

    document.getElementById('ownerStatTotal').innerText = ownerReservationsData.length;
    document.getElementById('ownerStatToday').innerText = todayRes.length;
    document.getElementById('ownerStatApproved').innerText = playedOrFinishedCount;
    document.getElementById('ownerStatIncome').innerText = income.toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ₺';

    let activeList = (activeReservationTab === 'today') ? todayRes : ((activeReservationTab === 'future') ? futureRes : pastRes);

    if (selectedFieldId !== 'all') {
        activeList = activeList.filter(r => r.field_id == selectedFieldId);
    }

    if (query) {
        activeList = activeList.filter(r => 
            r.team_name.toLowerCase().includes(query) || 
            r.contact_name.toLowerCase().includes(query) || 
            (r.username && r.username.toLowerCase().includes(query)) ||
            r.phone.includes(query)
        );
    }

    activeList.sort((a, b) => {
        let valA = a[currentSortColumn];
        let valB = b[currentSortColumn];
        if (currentSortColumn === 'fee') {
            valA = parseFloat(valA);
            valB = parseFloat(valB);
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
        }
        if (valA < valB) return currentSortAsc ? -1 : 1;
        if (valA > valB) return currentSortAsc ? 1 : -1;
        return 0;
    });

    if (activeList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">Bu kriterlerde kayıtlı randevu bulunamadı.</td></tr>`;
        return;
    }

    let html = '';
    activeList.forEach(r => {
        const usernameTag = r.username ? `@${escapeHtml(r.username)}` : '@oyuncu1';

        html += `<tr>
            <td class="fw-bold text-dark px-3 py-3">${escapeHtml(r.team_name)}</td>
            <td class="px-3 py-3">
                <div class="fw-bold text-dark fs-7">${escapeHtml(r.contact_name)}</div>
                <span class="badge bg-light text-primary border fs-8">${usernameTag}</span>
            </td>
            <td class="px-3 py-3">${escapeHtml(r.phone)}</td>
            <td class="px-3 py-3"><span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(r.field_name)}</span></td>
            <td class="px-3 py-3 text-primary fw-semibold">${r.reservation_date}</td>
            <td class="px-3 py-3 text-dark fw-bold">${r.reservation_time}</td>
            <td class="px-3 py-3 text-success fw-bold">₺${parseFloat(r.fee).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
            <td class="px-3 py-3">${computeMatchStatusBadge(r.reservation_date, r.reservation_time)}</td>
            <td class="px-3 py-3 text-end">
                <button class="btn btn-sm btn-outline-danger" onclick="cancelReservation(${r.id})">İptal Et / Sil</button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

async function saveWalkinReservation(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const res = await fetch('api/reservations.php?action=save', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('walkinModal')).hide();
        loadOwnerReservations();
        loadOwnerSubscriptions();
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

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
</script>

</body>
</html>
