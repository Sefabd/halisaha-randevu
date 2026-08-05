<!-- Modal: Randevu Ekle / Düzenle -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-glass">
            <div class="modal-header modal-header-glass">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="fa-solid fa-calendar-plus text-primary me-2"></i> Yeni Halı Saha Randevusu
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="reservationForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="res_id" value="0">
                    <input type="hidden" name="subscription_plan" id="res_sub_plan" value="Standart">

                    <!-- Conflict Warning Box -->
                    <div id="conflictAlert" class="alert alert-danger bg-danger bg-opacity-25 border border-danger border-opacity-50 text-white d-none rounded-3 mb-4 fs-7">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <span id="conflictMessage"></span>
                    </div>

                    <div class="row g-3">
                        <!-- Takım Adı -->
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TAKIM ADI *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-muted border-secondary"><i class="fa-solid fa-shield-halved"></i></span>
                                <input type="text" class="form-control" name="team_name" id="res_team_name" required placeholder="Örn: Kadıköy İdman Yurdu">
                            </div>
                        </div>

                        <!-- Yetkili Kişi -->
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">YETKİLİ KİŞİ (AD SOYAD) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-muted border-secondary"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" name="contact_name" id="res_contact_name" required placeholder="Örn: Ahmet Yılmaz">
                            </div>
                        </div>

                        <!-- Telefon -->
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">TELEFON *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-muted border-secondary"><i class="fa-solid fa-phone"></i></span>
                                <input type="text" class="form-control" name="phone" id="res_phone" required placeholder="Örn: 0532 555 12 34">
                            </div>
                        </div>

                        <!-- Şehir & İlçe -->
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-7 fw-semibold">İL</label>
                            <select class="form-select" name="city" id="res_city">
                                <option value="İstanbul">İstanbul</option>
                                <option value="Ankara">Ankara</option>
                                <option value="İzmir">İzmir</option>
                                <option value="Bursa">Bursa</option>
                                <option value="Antalya">Antalya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-7 fw-semibold">İLÇE</label>
                            <input type="text" class="form-control" name="district" id="res_district" value="Kadıköy" placeholder="İlçe">
                        </div>

                        <!-- Tarih -->
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-7 fw-semibold">RANDEVU TARİHİ *</label>
                            <input type="date" class="form-control" name="reservation_date" id="res_date" required onchange="checkRealtimeConflict()">
                        </div>

                        <!-- Saat -->
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-7 fw-semibold">RANDEVU SAATİ *</label>
                            <select class="form-select" name="reservation_time" id="res_time" required onchange="checkRealtimeConflict()">
                                <option value="17:00">17:00 - 18:00</option>
                                <option value="18:00">18:00 - 19:00</option>
                                <option value="19:00">19:00 - 20:00</option>
                                <option value="20:00" selected>20:00 - 21:00</option>
                                <option value="21:00">21:00 - 22:00</option>
                                <option value="22:00">22:00 - 23:00</option>
                                <option value="23:00">23:00 - 00:00</option>
                            </select>
                        </div>

                        <!-- Saha Adı -->
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-7 fw-semibold">SAHA SEÇİMİ *</label>
                            <select class="form-select" name="field_name" id="res_field" required onchange="checkRealtimeConflict()">
                                <option value="Saha 1">Saha 1 (UEFA Çim - Kapalı)</option>
                                <option value="Saha 2">Saha 2 (Açık Hibrit)</option>
                                <option value="Saha 3">Saha 3 (VIP Pro Saha)</option>
                            </select>
                        </div>

                        <!-- Ücret -->
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">ÜCRET (TL) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-muted border-secondary">₺</span>
                                <input type="number" step="0.01" class="form-control" name="fee" id="res_fee" required value="1200.00">
                            </div>
                        </div>

                        <!-- Durum -->
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-7 fw-semibold">RANDEVU DURUMU *</label>
                            <select class="form-select" name="status" id="res_status">
                                <option value="Bekliyor">⏳ Bekliyor</option>
                                <option value="Onaylandı" selected>✅ Onaylandı</option>
                                <option value="Tamamlandı">🏆 Tamamlandı</option>
                                <option value="İptal">❌ İptal</option>
                            </select>
                        </div>

                        <!-- Extra Options -->
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="needs_player" id="res_needs_player" value="1">
                                <label class="form-check-label text-warning fs-7 fw-semibold" for="res_needs_player">
                                    <i class="fa-solid fa-user-plus me-1"></i> Eksik Oyuncu / Rakip İlanı Yayınlansın (Eksik Oyuncu Bulma Desteği)
                                </label>
                            </div>
                        </div>

                        <!-- Notlar -->
                        <div class="col-md-12">
                            <label class="form-label text-muted fs-7 fw-semibold">ÖZEL NOTLAR</label>
                            <textarea class="form-control" name="notes" id="res_notes" rows="2" placeholder="Ek talepler, kamera kaydı vs."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-glass">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-uefa rounded-3" id="btnSaveReservation">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Randevuyu Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Silme Onayı Modal (Exam Requirement #4 & Bonus #7) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-content-glass text-center p-4">
            <div class="text-danger mb-3">
                <i class="fa-solid fa-triangle-exclamation display-4"></i>
            </div>
            <h5 class="fw-bold mb-2">Randevuyu Sil?</h5>
            <p class="text-muted fs-7 mb-4">Bu işlem geri alınamaz. Seçilen randevu veritabanından kalıcı olarak silinecektir.</p>
            <input type="hidden" id="deleteTargetId" value="0">
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3 fs-7" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-danger rounded-3 px-4 fs-7 fw-bold" onclick="executeDelete()">
                    <i class="fa-solid fa-trash me-1"></i> Evet, Sil
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile & App Floating Bottom Navigation -->
<nav class="bottom-nav">
    <a href="#" class="nav-item-btn active" onclick="scrollToSection('hero')">
        <i class="fa-solid fa-house"></i>
        <span>Ana Sayfa</span>
    </a>
    <a href="#" class="nav-item-btn" onclick="scrollToSection('abonman')">
        <i class="fa-solid fa-gem text-warning"></i>
        <span>Abonman</span>
    </a>
    <a href="#" class="nav-item-btn" data-bs-toggle="modal" data-bs-target="#reservationModal" onclick="prepareAddModal()">
        <i class="fa-solid fa-circle-plus text-primary fs-4"></i>
        <span>Randevu Al</span>
    </a>
    <a href="#" class="nav-item-btn" onclick="scrollToSection('matrix')">
        <i class="fa-solid fa-calendar-days text-info"></i>
        <span>Sahalar</span>
    </a>
    <a href="#" class="nav-item-btn" onclick="scrollToSection('reservationsTable')">
        <i class="fa-solid fa-list-check"></i>
        <span>Liste</span>
    </a>
</nav>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- App JS Engine -->
<script src="js/app.js"></script>

</body>
</html>
