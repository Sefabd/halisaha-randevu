# ⚽ SahaNet PRO - Online Spor Tesisleri & Halı Saha Randevu Yönetim Portalı

**SahaNet PRO**, sporcular ile spor tesisi işletmecilerini tek bir çatı altında buluşturan; modern, dinamik, esnek abonman ve vardiya yönetimli **yeni nesil online halı saha rezervasyon ve tesis yönetim platformudur.**

![SahaNet PRO Banner](https://img.shields.io/badge/SahaNet-PRO-059669?style=for-the-badge&logo=soccer&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-PDO-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)

---

## 🚀 Öne Çıkan Özellikler

### ⚽ 1. Müşteri / Oyuncu Portalı (`index.php`)
* **Gelişmiş Arama Motoru:** Spor tipi (*Halı Saha, Basketbol, Tenis, Voleybol*), İl, İlçe ve Saha Tipi (*Kapalı/Açık*) kriterlerine göre 4 kolonlu arama.
* **Tesis İmkanları Filtreleme:** HD Kamera Kaydı, Ücretsiz Su & İkram, Soyunma Odası & Duş, Krampon/Ayakkabı Kiralama gibi tesis imkanlarına göre anlık filtreleme ve dinamik rozet gösterimi.
* **İnline Akordeon Saat Matrisi:** Her tesis için açılabilir, eşit boyutlu saat butonları ile anlık doluluk ve vardiya takibi.
* **Canlı İstemci Saati (`new Date()`) Entegrasyonu:** Geçmiş saatler anında gri **`GEÇTİ`** rozetiyle kilitlenir ve geçmişe randevu alınması engellenir.
* **Süper Lig Takım Temaları:** Galatasaray (🟡🔴), Fenerbahçe (🔵🟡), Beşiktaş (⬛⚪), Trabzonspor (🟣🔴) ve Varsayılan Yeşil (🟢⚪) renk motoru seçeneği.
* **Genişletilmiş Profil Modalı (`modal-xl`):** `modal-xl` (1140px) genişliğinde profil yönetimi, şifre güncelleme, aktif randevular ve abonman kredisi takibi.
* **Benzersiz Telefon Doğrulaması:** Güvenlik ve sahte hesap engelleme için her kullanıcı tek bir telefon numarasıyla kayıt olabilir (`phone TEXT UNIQUE`).

---

### 🏟️ 2. Tesis İşletmecisi Paneli (`owner_dashboard.php`)
* **Birleşik Filtreleme Araç Çubuğu:** Randevu yönetiminde sekmeler (*Bugün, Gelecek, Geçmiş*), Tarih Seçici, Saha Seçimi, Arama Kutusu ve Sıfırlama Butonu tek bir şık araç çubuğunda.
* **Akıllı Abonman & Kredi Yönetimi:**
  * 💎 **6 Aylık VIP PRO (24 Maç):** Altın sarısı soft pastel arka plan tonu (`bg-warning bg-opacity-10`).
  * 👑 **3 Aylık VIP Paket (12 Maç):** Zümrüt yeşili soft pastel arka plan tonu (`bg-success bg-opacity-10`).
  * 📅 **Aylık Paket (4 Maç):** Safir mavisi soft pastel arka plan tonu (`bg-primary bg-opacity-10`).
* **Elden Müşteriler İçin Benzersiz Kodu (`#ELDEN-101`):** Üyesiz / Elden abonman müşterilerine otomatik olarak çakışmasız `#ELDEN-101`, `#ELDEN-102` kodları üretilir.
* **Gelişmiş Çalışma Saatleri & Vardiya Yönetimi:** Hafta içi ve Hafta sonu açılış/kapanış saatlerini ayrı ayrı belirleme (Gece yarısını geçen vardiyalar desteklenir: örn. `13:00 - 03:00`).
* **Kapalı Tarih Aralığı Engeli:** Tesis bakımı veya özel izinler için başlangıç-bitiş tarihi girilerek tesis geçici olarak kapatılabilir.
* **Canlı Doluluk Matrisi:**
  * 🟢 **Boş (Elden Kayıt):** Tıklanarak anında elden hızlı randevu ekleme (Walk-in).
  * 🔴 **Dolu / Alınan Randevu:** Detay pop-up'ı ile müşteri ve ücret bilgisi görüntüleme.
  * 🟡 **Abonmanlı Randevu:** VIP paket sahibi takımların sabit periyodik saatleri.
  * 🕒 **Geçti:** Canlı saat takibi ile geçen saatler.
  * 🚫 **Kapalı:** İşletmeci tarafından kapatılan saat aralıkları.

---

## 🔐 Demo Hesaplar & Giriş Bilgileri

Sistemi farklı iller ve roller üzerinden anında test etmek için aşağıdaki hazır demo hesapları kullanabilirsiniz:

### 👤 Demo Oyuncu Hesabı:
* **Kullanıcı Adı:** `oyuncu1`
* **Şifre:** `123`

### 🏟️ Demo Tesis İşletmecisi Hesapları:

| Tesis Adı | İl / İlçe | Kullanıcı Adı | Şifre | Sahalar |
| :--- | :--- | :--- | :--- | :--- |
| **Kadıköy Şampiyonlar Spor Kompleksi** | İstanbul / Kadıköy | `kadikoy_arena` | `123` | Saha 1, Saha 2, Basketbol, Tenis |
| **Moda Park VIP Spor Tesisleri** | İstanbul / Kadıköy | `moda_vip` | `123` | VIP Arena 1, VIP Arena 2 |
| **Beşiktaş Yıldızlar Halı Saha & Arena** | İstanbul / Beşiktaş | `besiktas_arena` | `123` | Saha A (Çim), Saha B (Açık) |
| **Çankaya Premier Futbol Kompleksi** | Ankara / Çankaya | `cankaya_spor` | `123` | Ankara VIP Arena |
| **Karşıyaka Sahil Arena Spor Tesisleri** | İzmir / Karşıyaka | `karsiyaka_arena` | `123` | Ege Futbol Sahası |
| **Nilüfer Olimpik Halı Saha Tesisleri** | Bursa / Nilüfer | `nilufer_spor` | `123` | Bursa Çim Saha 1 |

---

## 🛠️ Teknoloji Yığını

| Katman | Teknoloji / Kütüphane |
| :--- | :--- |
| **Backend** | Native PHP 8.3 (PDO SQLite3 Driver) |
| **Veritabanı** | SQLite 3 (`db/database.sqlite`) |
| **Frontend Framework** | HTML5, JavaScript (ES6+ Vanilla JS) |
| **UI & Tasarım** | Bootstrap 5.3.3, Custom CSS Design System, FontAwesome 6.5.1 |
| **Tipografi** | Google Fonts (Plus Jakarta Sans, Outfit) |

---

## 💻 Kurulum ve Çalıştırma

### 1. Gereksinimler
* PHP 8.0 veya üzeri (SQLite PDO eklentisi aktif olmalıdır).

### 2. Projeyi İndirin / Klonlayın
```bash
git clone https://github.com/Sefabd/halisaha-randevu.git
cd halisaha-randevu
```

### 3. Veritabanını Oluşturun ve Tohumlayın (db_init.php)
Veritabanı tablolarını sıfırlayıp 6 şehirdeki zengin demo tesisleri oluşturmak için:
```bash
php db_init.php
```

### 4. Yerel Web Sunucusunu Başlatın
PHP dahili web sunucusunu kullanarak projeyi ayağa kaldırın:
```bash
php -S 127.0.0.1:8000
```

### 5. Tarayıcıda Açın
* **Müşteri Kiralama Portalı:** 👉 [http://127.0.0.1:8000/index.php](http://127.0.0.1:8000/index.php)
* **Giriş / Kayıt Portalı:** 👉 [http://127.0.0.1:8000/login.php](http://127.0.0.1:8000/login.php)
* **İşletme Yönetim Paneli:** 👉 [http://127.0.0.1:8000/owner_dashboard.php](http://127.0.0.1:8000/owner_dashboard.php)

---

## 📁 Proje Dizin Yapısı

```
halisaha-randevu/
├── api/
│   ├── auth.php            # Oyuncu & İşletmeci kayıt, benzersiz tel doğrulama ve giriş API
│   ├── facility.php        # Tesis, abonman, çalışma saatleri ve saha yönetimi API
│   ├── reservations.php    # Randevu oluşturma, çakışma kontrolü ve iptal API
│   └── stats.php           # Anlık istatistikler ve saatlik matris API
├── config/
│   └── db.php              # SQLite PDO veritabanı bağlantı konfigürasyonu
├── css/
│   └── style.css           # Tema motoru, kart renk tonları ve özel scrollbar CSS
├── db/
│   └── database.sqlite     # SQLite veritabanı dosyası
├── db_init.php             # Veritabanı şeması ve 6 şehirli demo veri kurucu script
├── index.php               # Müşteri / Oyuncu arama ve rezervasyon portalı
├── login.php               # Giriş & Kayıt portalı
├── owner_dashboard.php     # Tesis İşletmecisi yönetim paneli
└── README.md               # Proje dokümantasyonu
```

---

## 📜 Lisans

Bu proje **MIT Lisansı** ile lisanslanmıştır. Özgürce geliştirebilir ve kullanabilirsiniz.
