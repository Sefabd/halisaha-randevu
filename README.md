# ⚽ SahaNet PRO - Online Spor Tesisleri & Halı Saha Randevu Yönetim Portalı

**SahaNet PRO**, sporcular ile halı saha ve spor tesisi işletmecilerini buluşturan, modern, dinamik ve tam kapsamlı bir online rezervasyon ve tesis yönetim platformudur.

![SahaNet PRO Banner](https://img.shields.php.net/badge/SahaNet-PRO-059669?style=for-the-badge&logo=soccer&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-PDO-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

---

## 🚀 Öne Çıkan Özellikler

### ⚽ 1. Müşteri / Oyuncu Portalı (`index.php`)
* **Gelişmiş Arama Motoru:** Spor tipi (Halı Saha, Basketbol, Tenis, Voleybol), İl, İlçe ve Saha Tipi (Kapalı/Açık) kriterlerine göre 4 kolonlu arama.
* **Tesis İmkanları Filtreleme:** HD Kamera Kaydı, Ücretsiz Su & İkram, Soyunma Odası & Duş, Krampon/Ayakkabı Kiralama gibi tesis seviyesindeki imkanlara göre anlık arama ve dinamik badge gösterimi.
* **Canlı Tesis Adı Arama:** Listelenen tesisler arasında isim veya adres ile canlı arama.
* **İnline Akordeon Saat Matrisi:** Her tesis için açılabilir, eşit boyutlu (`38px`) saat butonları ile anlık doluluk kontrolü.
* **Canlı İstemci Saati (`new Date()`) Entegrasyonu:** Geçmiş saatler anında gri **`GEÇTİ`** rozetiyle kapatılır ve geçmiş saatlere randevu alınması engellenir.
* **Süper Lig Takım Temaları:** Galatasaray (🟡🔴), Fenerbahçe (🔵🟡), Beşiktaş (⬛⚪), Trabzonspor (🟣🔴) ve Varsayılan Yeşil (🟢⚪) renk motoru seçeneği.
* **Profil ve Randevularım Yönetimi:** Kullanıcı profil bilgilerini güncelleme, şifre değiştirme ve geçmiş/gelecek randevularını görüntüleyip iptal edebilme.
* **Abonmanlık Modülü:** Tesis bazında Aylık Fix, Sezonluk Efsane VIP ve Kemik Kadro paketlerine başvuru yapabilme.

---

### 🏟️ 2. Tesis İşletmecisi Paneli (`owner_dashboard.php`)
* **Anlık Sistem Saati Widget'ı:** Saniyeyi gösteren canlı dijital saat.
* **Canlı İstatistik Kartları:** Toplam Randevu, Bugünkü Randevu, Tamamlanan Maç Sayısı ve Bugünkü Kazanç (₺) özetleri.
* **Canlı Doluluk Matrisi:**
  * 🟢 **Boş (Elden Kayıt):** Tıklanarak anında elden hızlı randevu ekleme (Walk-in).
  * 🔴 **Dolu / Alınan Randevu:** Detay pop-up'ı ile müşteri ve ücret bilgisi görüntüleme.
  * 🟡 **Abonmanlı Randevu:** VIP paket sahibi takımların saatleri.
  * 🕒 **Geçti:** Canlı saat takibi ile geçen saatler.
  * 🚫 **Kapalı:** İşletmeci tarafından kapatılan saat aralıkları.
* **Gelişmiş Çalışma Saatleri Ayarı:** Hafta içi ve Hafta sonu açılış/kapanış saatlerini ayrı ayrı belirleme (Gece yarısını geçen vardiyalar desteklenir: örn. 13:00 - 03:00).
* **Tesis Seviyesi İmkan Yönetimi:** Kamera, Su, Duş ve Krampon kiralama imkanlarını işaretleyip güncelleme.
* **Kapalı Tarih Aralığı Engeli:** Tesis bakımı veya özel izinler için tarih aralığı girerek tesisi geçici kapalı konuma getirme.
* **Saha Yönetimi:** Yeni saha ekleme, düzenleme, silme ve sahalara özel kapalı kalınacak tarih/saat aralıklarını (`Closed Range`) belirleme.
* **İşletme Randevu Yönetimi Tablosu:**
  * **İçten Kaydırılabilir Liste:** Tablo `max-height: 420px; overflow-y: auto;` ile sabit tutulur, tüm sayfa yerine sadece liste içi kayar.
  * **Yapışkan Başlıklar (`sticky-top`):** Aşağı kaydırıldıkça kolon başlıkları en üstte sabit kalır.
  * **Sıralanabilir Kolonlar:** Takım Adı, Yetkili Kişi, Tarih, Saat ve Ücret kolonlarına tıklayarak A-Z / Z-A sıralama.
  * **Otomatik Maç Durumu:** ⚽ *Başladı (Maç Oynanıyor)*, ⏳ *Bekliyor*, 🏁 *Bitti* rozetleri.

---

### 🔑 3. Kimlik Doğrulama & Güvenlik (`login.php` & `api/auth.php`)
* **Çift Portal Girişi:** Oyuncu ve Tesis İşletmecisi için ayrı sekmeler ve kayıt formları.
* **Şifre Güvenliği:** PHP `password_hash()` (BCRYPT) ile şifrelenmiş parola saklama.
* **Demo Hesaplar:**
  * 👤 **Demo Oyuncu Girişi:** `oyuncu1` / `123`
  * 🏟️ **Demo İşletmeci Girişi:** `kadikoy_arena` / `123`

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

### 3. Veritabanını Oluşturun (db_init.php)
Veritabanı tablolarını ve demo verileri otomatik oluşturmak için:
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
│   ├── auth.php            # Kullanıcı / İşletmeci giriş, kayıt, profil işlemleri
│   ├── facility.php        # Tesis, imkanlar, çalışma saatleri ve saha API
│   └── reservations.php    # Randevu oluşturma, listeleme, silme API
├── config/
│   └── db.php              # SQLite PDO veritabanı bağlantısı
├── css/
│   └── style.css           # Tema motoru, buton boyutları ve özel scrollbar CSS
├── db/
│   └── database.sqlite     # SQLite veritabanı dosyası
├── db_init.php             # Veritabanı şema ve demo veri oluşturucu script
├── index.php               # Müşteri / Oyuncu arama ve rezervasyon portalı
├── login.php               # Giriş & Kayıt portalı
├── owner_dashboard.php     # Tesis İşletmecisi yönetim paneli
└── README.md               # Proje dokümantasyonu
```

---

## 📜 Lisans

Bu proje **MIT Lisansı** ile lisanslanmıştır. Özgürce geliştirebilir ve kullanabilirsiniz.
