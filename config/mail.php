<?php
// config/mail.php - Gerçek Gmail SMTP Konfigürasyonu
return [
    // Gmail SMTP Sunucu Ayarları
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // tls or ssl
    
    // BURAYA GERÇEK GMAİL ADRESİNİZİ VE GMAİL UYGULAMA ŞİFRENİZİ (16 HANELİ) GİREBİLİRSİNİZ
    // Gmail Hesabınız > Güvenlik > 2 Adımlı Doğrulama > Uygulama Şifreleri (App Passwords)
    'smtp_user' => 'sahanetpro.destek@gmail.com', // Gerçek Gmail Adresiniz
    'smtp_pass' => '', // 16 Haneli Gmail Uygulama Şifreniz (Örn: abcd efgh ijkl mnop)

    // Gönderici Bilgileri
    'from_email' => 'sahanetpro.destek@gmail.com',
    'from_name'  => 'SahaNet PRO Spor Tesisleri'
];
