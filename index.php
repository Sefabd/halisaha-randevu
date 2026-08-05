<?php
// index.php - SahaNet PRO Main Dashboard & UEFA Interface
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4" id="hero">
    
    <!-- Top Dashboard Metrics Bar -->
    <div class="row g-3 mb-5">
        <!-- Metric 1: Toplam Randevu -->
        <div class="col-6 col-md-3">
            <div class="glass-card metric-card" style="--accent-color: #0284c7;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-7 font-heading fw-semibold">TOPLAM RANDEVU</div>
                        <div class="metric-value text-white mt-1" id="statTotal">0</div>
                    </div>
                    <div class="icon-box m-0 bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-list-ol fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 2: Bugünkü Randevu -->
        <div class="col-6 col-md-3">
            <div class="glass-card metric-card" style="--accent-color: #f59e0b;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-7 font-heading fw-semibold">BUGÜNKÜ RANDEVU</div>
                        <div class="metric-value text-warning mt-1" id="statToday">0</div>
                    </div>
                    <div class="icon-box m-0 bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-calendar-day fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Onaylanan Randevu -->
        <div class="col-6 col-md-3">
            <div class="glass-card metric-card" style="--accent-color: #10b981;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-7 font-heading fw-semibold">ONAYLANAN</div>
                        <div class="metric-value text-success mt-1" id="statApproved">0</div>
                    </div>
                    <div class="icon-box m-0 bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 4: Günlük Gelir -->
        <div class="col-6 col-md-3">
            <div class="glass-card metric-card" style="--accent-color: #8b5cf6;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-7 font-heading fw-semibold">GÜNLÜK GELİR</div>
                        <div class="metric-value text-info mt-1" id="statIncome">0 ₺</div>
                    </div>
                    <div class="icon-box m-0 bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-sack-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ABONMAN PAKETLERİ SECTION -->
    <section id="abonman" class="mb-5">
        <div class="text-center mb-4">
            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 rounded-pill px-3 py-2 fs-7 mb-2 fw-bold">
                <i class="fa-solid fa-crown me-1"></i> ÖZEL AYRICALIKLAR
            </span>
            <h2 class="abonman-section-title display-6 d-block">ABONMAN PAKETLERİ</h2>
            <p class="text-muted fs-6 max-w-600 mx-auto">Sabit gün ve saat garantisi, drone maç özetleri ve VIP imkanlarla halı saha deneyiminizi zirveye taşıyın.</p>
        </div>

        <div class="subscription-grid">
            <!-- 1. Aylık Fix Paket (Blue/Silver) -->
            <div class="sub-card sub-card-blue">
                <div>
                    <div class="icon-box">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="fw-bold text-white fs-4 mb-1">AYLIK FİX PAKET</h3>
                    <div class="text-muted fs-7 fw-semibold">1 AY SÜRELİ</div>
                    <div class="sub-price text-info">4.000 TL <span class="fs-7 text-muted fw-normal">/ Ay</span></div>
                    
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-circle-check text-info"></i> Sabit Gün ve Saat Garantisi</li>
                        <li><i class="fa-solid fa-circle-check text-info"></i> %10 Abonman İndirimi</li>
                        <li><i class="fa-solid fa-circle-check text-info"></i> 1 Gün Önceden İptal Hakkı</li>
                    </ul>
                </div>
                <button class="btn btn-outline-info w-100 rounded-3 py-2 fw-bold mt-3" onclick="selectAbonmanPlan('Aylık Fix', '4.000 TL')">
                    Hemen Katıl <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </div>

            <!-- 2. Sezonluk Efsane (Red/Gold VIP) -->
            <div class="sub-card sub-card-vip">
                <div class="vip-banner">
                    <i class="fa-solid fa-star me-1"></i> MOST POPULAR
                </div>
                <div>
                    <div class="icon-box">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h3 class="fw-bold text-white fs-3 mb-1">SEZONLUK EFSANE</h3>
                    <div class="text-warning fs-7 fw-semibold">6 AY SÜRELİ - VIP</div>
                    <div class="sub-price text-warning fs-2">21.500 TL <span class="fs-7 text-muted fw-normal">/ Sezon</span></div>
                    
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-circle-check text-warning"></i> VIP Sabit Saat Garantisi</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> Drone & Kamera Maç Özeti</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> Eksik Oyuncu Bulma Desteği</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> Turnuvalara Öncelikli Kayıt</li>
                    </ul>
                </div>
                <button class="btn btn-warning text-dark w-100 rounded-3 py-2.5 fw-extrabold mt-3 shadow-lg" onclick="selectAbonmanPlan('Sezonluk Efsane', '21.500 TL')">
                    <i class="fa-solid fa-crown me-1"></i> VIP ÜYE OL
                </button>
            </div>

            <!-- 3. Kemik Kadro (Yellow/Black) -->
            <div class="sub-card sub-card-yellow">
                <div>
                    <div class="icon-box">
                        <i class="fa-solid fa-video"></i>
                    </div>
                    <h3 class="fw-bold text-white fs-4 mb-1">KEMİK KADRO</h3>
                    <div class="text-muted fs-7 fw-semibold">3 AY SÜRELİ</div>
                    <div class="sub-price text-warning">11.000 TL <span class="fs-7 text-muted fw-normal">/ 3 Ay</span></div>
                    
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-circle-check text-warning"></i> Sabit Gün ve Saat Garantisi</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> %15 Abonman İndirimi</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> HD Maç Kaydı</li>
                        <li><i class="fa-solid fa-circle-check text-warning"></i> Dönem Sonu 1 Bedava Maç</li>
                    </ul>
                </div>
                <button class="btn btn-outline-warning w-100 rounded-3 py-2 fw-bold mt-3" onclick="selectAbonmanPlan('Kemik Kadro', '11.000 TL')">
                    Paketi Seç <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- SAHA DOLULUK MATRİSİ (CANLI TAKVİM SAATLERİ) -->
    <section id="matrix" class="glass-card p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-white mb-0">
                    <i class="fa-solid fa-clock text-info me-2"></i> Bugünkü Saha & Saat Doluluk Matrisi
                </h4>
                <span class="text-muted fs-7">Boş saatlere tıklayarak anında randevu oluşturabilirsiniz.</span>
            </div>
            <button class="btn btn-sm btn-outline-light rounded-3" onclick="loadStats()">
                <i class="fa-solid fa-rotate me-1"></i> Yenile
            </button>
        </div>

        <div class="matrix-container">
            <table class="table table-borderless text-center align-middle m-0">
                <thead>
                    <tr class="text-muted fs-7 border-bottom border-secondary border-opacity-25">
                        <th class="text-start">SAHA</th>
                        <th>17:00</th>
                        <th>18:00</th>
                        <th>19:00</th>
                        <th>20:00</th>
                        <th>21:00</th>
                        <th>22:00</th>
                        <th>23:00</th>
                    </tr>
                </thead>
                <tbody id="matrixBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- RANDEVU LİSTESİ VE GELİŞMİŞ FİLTRELER SECTION -->
    <section id="reservationsTable" class="glass-card p-4">
        
        <!-- Filter & Search Toolbar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-white mb-0">
                    <i class="fa-solid fa-list-check text-primary me-2"></i> Randevu Takip Listesi
                </h3>
                <span class="text-muted fs-7">Tüm randevuları arayın, filtreleyin ve yönetin.</span>
            </div>

            <!-- Filters -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Input -->
                <div class="input-group input-group-sm max-w-220">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Takım veya Yetkili Ara...">
                </div>

                <!-- Şehir Filtresi -->
                <select class="form-select form-select-sm max-w-140" id="filterCity">
                    <option value="">Tüm İller</option>
                    <option value="İstanbul">İstanbul</option>
                    <option value="Ankara">Ankara</option>
                    <option value="İzmir">İzmir</option>
                </select>

                <!-- Saha Filtresi -->
                <select class="form-select form-select-sm max-w-150" id="filterField">
                    <option value="">Tüm Sahalar</option>
                    <option value="Saha 1">Saha 1</option>
                    <option value="Saha 2">Saha 2</option>
                    <option value="Saha 3">Saha 3</option>
                </select>

                <!-- Durum Filtresi -->
                <select class="form-select form-select-sm max-w-140" id="filterStatus">
                    <option value="">Tüm Durumlar</option>
                    <option value="Bekliyor">Bekliyor</option>
                    <option value="Onaylandı">Onaylandı</option>
                    <option value="Tamamlandı">Tamamlandı</option>
                    <option value="İptal">İptal</option>
                </select>

                <!-- Print Report Button -->
                <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="window.print()" title="Yazdır / Rapor Al">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table-dark-glass">
                <thead>
                    <tr>
                        <th>TAKIM ADI</th>
                        <th>YETKİLİ KİŞİ</th>
                        <th>TELEFON / WHATSAPP</th>
                        <th>ŞEHİR / İLÇE</th>
                        <th>TARİH</th>
                        <th>SAAT</th>
                        <th>SAHA</th>
                        <th>ÜCRET</th>
                        <th>DURUM</th>
                        <th class="text-end">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>
        </div>
    </section>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
