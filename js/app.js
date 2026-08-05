// SahaNet PRO - Application JavaScript Engine

document.addEventListener('DOMContentLoaded', () => {
    // Set default date in modal to today
    const todayStr = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('res_date');
    if (dateInput && !dateInput.value) {
        dateInput.value = todayStr;
    }

    // Initial Data Loads
    loadStats();
    loadReservations();

    // Event Listeners for Filters
    document.getElementById('filterSearch')?.addEventListener('input', debounce(loadReservations, 300));
    document.getElementById('filterField')?.addEventListener('change', loadReservations);
    document.getElementById('filterStatus')?.addEventListener('change', loadReservations);
    document.getElementById('filterCity')?.addEventListener('change', loadReservations);

    // Form Submit Event
    document.getElementById('reservationForm')?.addEventListener('submit', handleFormSubmit);
});

// Debounce helper for smooth real-time typing search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load Real-time Stats & Hourly Matrix
async function loadStats() {
    try {
        const res = await fetch('api/stats.php');
        const json = await res.json();

        if (json.status === 'success') {
            document.getElementById('statTotal').innerText = json.metrics.total;
            document.getElementById('statToday').innerText = json.metrics.today;
            document.getElementById('statApproved').innerText = json.metrics.approved;
            document.getElementById('statIncome').innerText = json.metrics.daily_income;

            renderHourlyMatrix(json.matrix);
        }
    } catch (err) {
        console.error('Stats load error:', err);
    }
}

// Render Hourly Matrix for Saha 1, 2, 3
function renderHourlyMatrix(matrixData) {
    const fields = ['Saha 1', 'Saha 2', 'Saha 3'];
    const times = ['17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
    const container = document.getElementById('matrixBody');

    if (!container) return;

    let html = '';
    fields.forEach(field => {
        html += `<tr>
            <td class="fw-bold text-white fs-7"><i class="fa-solid fa-vector-square text-primary me-2"></i>${field}</td>`;
        times.forEach(t => {
            const teamName = matrixData && matrixData[field] && matrixData[field][t];
            if (teamName) {
                html += `<td>
                    <div class="slot-badge slot-busy" title="Dolu: ${escapeHtml(teamName)}">
                        <i class="fa-solid fa-lock me-1"></i>${t}
                    </div>
                </td>`;
            } else {
                html += `<td>
                    <div class="slot-badge slot-free" onclick="quickBookSlot('${field}', '${t}')" title="Boş - Tıkla Randevu Al">
                        <i class="fa-solid fa-plus me-1"></i>${t}
                    </div>
                </td>`;
            }
        });
        html += `</tr>`;
    });

    container.innerHTML = html;
}

// Quick Book from Hourly Matrix
function quickBookSlot(field, time) {
    prepareAddModal();
    document.getElementById('res_field').value = field;
    document.getElementById('res_time').value = time;
    const modalEl = document.getElementById('reservationModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    checkRealtimeConflict();
}

// Select Subscription Plan from Abonman Cards
function selectAbonmanPlan(planName, price) {
    prepareAddModal();
    document.getElementById('res_sub_plan').value = planName;
    document.getElementById('res_notes').value = `[${planName} Üyesi - Fiyat: ${price}]`;
    document.getElementById('modalTitle').innerHTML = `<i class="fa-solid fa-gem text-warning me-2"></i> Abonman Kaydı: ${planName}`;
    
    const modalEl = document.getElementById('reservationModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Load Reservations Table via AJAX
async function loadReservations() {
    const search = document.getElementById('filterSearch')?.value || '';
    const field = document.getElementById('filterField')?.value || '';
    const status = document.getElementById('filterStatus')?.value || '';
    const city = document.getElementById('filterCity')?.value || '';

    const url = `api/reservations.php?action=list&search=${encodeURIComponent(search)}&field=${encodeURIComponent(field)}&status=${encodeURIComponent(status)}&city=${encodeURIComponent(city)}`;

    try {
        const res = await fetch(url);
        const json = await res.json();
        const tbody = document.getElementById('reservationsTableBody');

        if (!tbody) return;

        if (json.status === 'success') {
            if (json.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">Kriterlere uygun randevu bulunamadı.</td></tr>`;
                return;
            }

            let html = '';
            json.data.forEach(r => {
                const statusBadge = getStatusBadge(r.status);
                const subBadge = r.subscription_plan && r.subscription_plan !== 'Standart' 
                    ? `<span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 fs-8"><i class="fa-solid fa-crown me-1"></i>${escapeHtml(r.subscription_plan)}</span>`
                    : '';
                
                const needsPlayerBadge = r.needs_player == 1 
                    ? `<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 fs-8 ms-1"><i class="fa-solid fa-user-plus me-1"></i>Eksik Oyuncu</span>`
                    : '';

                // Clean phone number for WhatsApp wa.me link
                const cleanPhone = r.phone.replace(/[^0-9]/g, '');

                html += `<tr>
                    <td class="fw-bold text-white">
                        <div class="d-flex align-items-center gap-2">
                            <span>${escapeHtml(r.team_name)}</span>
                            ${subBadge}
                            ${needsPlayerBadge}
                        </div>
                    </td>
                    <td><i class="fa-solid fa-user text-muted me-1 fs-7"></i>${escapeHtml(r.contact_name)}</td>
                    <td>
                        <a href="https://wa.me/90${cleanPhone}?text=${encodeURIComponent('Merhaba ' + r.contact_name + ', SahaNet PRO üzerinden ' + r.reservation_date + ' tarihindeki randevunuz hakkında:')}" 
                           target="_blank" class="btn btn-whatsapp" title="WhatsApp ile İletişime Geç">
                            <i class="fa-brands fa-whatsapp me-1"></i>${escapeHtml(r.phone)}
                        </a>
                    </td>
                    <td><span class="badge bg-secondary bg-opacity-50">${escapeHtml(r.city)} / ${escapeHtml(r.district)}</span></td>
                    <td class="fw-semibold text-info">${formatDate(r.reservation_date)}</td>
                    <td class="fw-bold text-warning">${r.reservation_time}</td>
                    <td><span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">${escapeHtml(r.field_name)}</span></td>
                    <td class="fw-extrabold text-success">₺${parseFloat(r.fee).toLocaleString('tr-TR', {minimumFractionDigits:2})}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-info rounded-2 me-1" onclick="editReservation(${r.id})" title="Düzenle">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-2" onclick="confirmDelete(${r.id})" title="Sil">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;
        }
    } catch (err) {
        console.error('Reservation list load error:', err);
    }
}

// Real-time Conflict Validation inside Modal
async function checkRealtimeConflict() {
    const field = document.getElementById('res_field')?.value;
    const date = document.getElementById('res_date')?.value;
    const time = document.getElementById('res_time')?.value;
    const excludeId = document.getElementById('res_id')?.value || 0;

    const alertBox = document.getElementById('conflictAlert');
    const alertMsg = document.getElementById('conflictMessage');
    const saveBtn = document.getElementById('btnSaveReservation');

    if (!field || !date || !time) return;

    try {
        const res = await fetch(`api/check_conflict.php?field=${encodeURIComponent(field)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&exclude_id=${excludeId}`);
        const json = await res.json();

        if (json.has_conflict) {
            alertBox.classList.remove('d-none');
            alertMsg.innerText = json.message;
            saveBtn.disabled = true;
            saveBtn.classList.add('btn-secondary');
            saveBtn.classList.remove('btn-uefa');
        } else {
            alertBox.classList.add('d-none');
            saveBtn.disabled = false;
            saveBtn.classList.remove('btn-secondary');
            saveBtn.classList.add('btn-uefa');
        }
    } catch (err) {
        console.error('Conflict check error:', err);
    }
}

// Handle Form Submission
async function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const res = await fetch('api/reservations.php?action=save', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();

        if (json.status === 'success') {
            const modalEl = document.getElementById('reservationModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            loadReservations();
            loadStats();
        } else {
            alert(json.message);
        }
    } catch (err) {
        alert('Kaydedilirken bir hata oluştu.');
    }
}

// Prepare Modal for New Entry
function prepareAddModal() {
    const form = document.getElementById('reservationForm');
    if (form) form.reset();
    document.getElementById('res_id').value = '0';
    document.getElementById('res_sub_plan').value = 'Standart';
    document.getElementById('modalTitle').innerHTML = `<i class="fa-solid fa-calendar-plus text-primary me-2"></i> Yeni Halı Saha Randevusu`;
    document.getElementById('conflictAlert').classList.add('d-none');
    document.getElementById('btnSaveReservation').disabled = false;

    // Reset date to today
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('res_date').value = todayStr;
}

// Edit Reservation
async function editReservation(id) {
    try {
        const res = await fetch(`api/reservations.php?action=get_one&id=${id}`);
        const json = await res.json();

        if (json.status === 'success') {
            const d = json.data;
            document.getElementById('res_id').value = d.id;
            document.getElementById('res_team_name').value = d.team_name;
            document.getElementById('res_contact_name').value = d.contact_name;
            document.getElementById('res_phone').value = d.phone;
            document.getElementById('res_city').value = d.city;
            document.getElementById('res_district').value = d.district;
            document.getElementById('res_date').value = d.reservation_date;
            document.getElementById('res_time').value = d.reservation_time;
            document.getElementById('res_field').value = d.field_name;
            document.getElementById('res_fee').value = d.fee;
            document.getElementById('res_status').value = d.status;
            document.getElementById('res_sub_plan').value = d.subscription_plan || 'Standart';
            document.getElementById('res_needs_player').checked = d.needs_player == 1;
            document.getElementById('res_notes').value = d.notes || '';

            document.getElementById('modalTitle').innerHTML = `<i class="fa-solid fa-pen-to-square text-info me-2"></i> Randevu Düzenle #${d.id}`;

            const modalEl = document.getElementById('reservationModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            checkRealtimeConflict();
        }
    } catch (err) {
        alert('Kayıt çekilirken hata oluştu.');
    }
}

// Confirmation Dialog before Delete (Exam Requirement)
function confirmDelete(id) {
    document.getElementById('deleteTargetId').value = id;
    const modalEl = document.getElementById('deleteConfirmModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Execute Delete
async function executeDelete() {
    const id = document.getElementById('deleteTargetId').value;
    const formData = new FormData();
    formData.append('id', id);

    try {
        const res = await fetch('api/reservations.php?action=delete', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();

        if (json.status === 'success') {
            const modalEl = document.getElementById('deleteConfirmModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            loadReservations();
            loadStats();
        } else {
            alert(json.message);
        }
    } catch (err) {
        alert('Silinirken bir hata oluştu.');
    }
}

// Helper Functions
function getStatusBadge(status) {
    switch (status) {
        case 'Onaylandı': return `<span class="badge badge-status badge-onaylandi"><i class="fa-solid fa-circle-check me-1"></i>Onaylandı</span>`;
        case 'Tamamlandı': return `<span class="badge badge-status badge-tamamlandi"><i class="fa-solid fa-trophy me-1"></i>Tamamlandı</span>`;
        case 'İptal': return `<span class="badge badge-status badge-iptal"><i class="fa-solid fa-circle-xmark me-1"></i>İptal</span>`;
        default: return `<span class="badge badge-status badge-bekliyor"><i class="fa-solid fa-clock me-1"></i>Bekliyor</span>`;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    return `${parts[2]}.${parts[1]}.${parts[3]}`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function scrollToSection(id) {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}
