# Gelişmiş Forum Sistemi

Modern, erişilebilir ve tam özellikli PHP tabanlı forum platformu. WCAG 2.1 AA standartlarına uygun, güvenli ve kullanıcı dostu topluluk çözümü.

## 📋 İçindekiler

- [Özellikler](#-özellikler)
- [Sistem Gereksinimleri](#-sistem-gereksinimleri)
- [Kurulum](#-kurulum)
- [Varsayılan Admin Bilgileri](#-varsayılan-admin-bilgileri)
- [Özellik Detayları](#-özellik-detayları)
- [Erişilebilirlik](#-erişilebilirlik)
- [Güvenlik](#-güvenlik)
- [Dosya Yapısı](#-dosya-yapısı)
- [Konfigürasyon](#-konfigürasyon)
- [API ve Entegrasyonlar](#-api-ve-entegrasyonlar)
- [Katkıda Bulunma](#-katkıda-bulunma)
- [Lisans](#-lisans)

## 🚀 Özellikler

### Forum Altyapısı
- ✅ **Kategori Yönetimi**: Çoklu kategori desteği, alt kategoriler
- ✅ **Konu Yönetimi**: Oluşturma, düzenleme, silme, sabitleme, kilitleme
- ✅ **Yanıt Sistemi**: Konu yanıtlama, düzenleme, alıntı yapma
- ✅ **Beğeni/Beğenmeme**: İçeriklere like/dislike sistemi
- ✅ **Anket Sistemi**: Konulara anket ekleme, grafik sonuçlar
- ✅ **Abone Olma**: Konulara abone olma ve bildirim alma
- ✅ **Sosyal Paylaşım**: WhatsApp, Telegram, Facebook, LinkedIn, X/Twitter
- ✅ **Görüntülenme Sayacı**: Konu ve içerik istatistikleri

### Kullanıcı Sistemi
- ✅ **Gelişmiş Profiller**: Profil resmi, biyografi, sosyal medya linkleri
- ✅ **Takip Sistemi**: Kullanıcıları takip etme ve takipçi yönetimi
- ✅ **Özel Mesajlaşma**: Kullanıcılar arası güvenli mesajlaşma
- ✅ **Kullanıcı İstatistikleri**: Konu, mesaj, beğeni, takipçi sayıları
- ✅ **Aktivite Geçmişi**: Son konular ve mesajlar

### Bildirim Sistemi
- ✅ **Site İçi Bildirimler**: Facebook tarzı bildirim merkezi
- ✅ **E-posta Bildirimleri**: SMTP entegrasyonu ile otomatik e-postalar
- ✅ **Bildirim Türleri**: Yanıt, beğeni, takip, mesaj, mention
- ✅ **Bildirim Tercihleri**: Kullanıcı bazlı özelleştirme
- ✅ **Okundu/Okunmadı**: Bildirim durumu takibi

### Yönetim Paneli
- ✅ **Kullanıcı Yönetimi**: Onaylama, yasaklama, silme
- ✅ **Kategori Yönetimi**: CRUD işlemleri, sıralama
- ✅ **Moderasyon Araçları**: Konu/mesaj yönetimi
- ✅ **Haber Yönetimi**: Duyuru ve haber sistemi
- ✅ **Blog Yönetimi**: Blog yazıları ve kategoriler
- ✅ **İletişim Mesajları**: Form mesajlarını görüntüleme
- ✅ **Site Ayarları**: SMTP, genel ayarlar, güvenlik
- ✅ **İstatistikler**: Detaylı site istatistikleri

### Haber ve Blog
- ✅ **Haber Sistemi**: Admin tarafından yönetilen haberler
- ✅ **Blog Sistemi**: Blog yazıları, kategoriler, etiketler
- ✅ **Zamanlama**: Yayın tarihi planlama
- ✅ **Görsel Desteği**: Haber ve bloglara resim ekleme
- ✅ **Öne Çıkanlar**: Manşet haber sistemi

### İletişim
- ✅ **İletişim Formu**: Ziyaretçi mesaj formu
- ✅ **Spam Koruması**: Matematik doğrulaması
- ✅ **Mesaj Yönetimi**: Admin panelinde görüntüleme
- ✅ **E-posta Yanıtlama**: Doğrudan yanıt gönderme

### Erişilebilirlik (WCAG 2.1 AA)
- ✅ **Ekran Okuyucu Desteği**: Tam ARIA etiketleri
- ✅ **Klavye Navigasyonu**: Tab ile tüm alanlara erişim
- ✅ **Yüksek Kontrast**: 4.5:1 kontrast oranı
- ✅ **Odak Göstergeleri**: Görünür focus outlines
- ✅ **Skip Links**: Ana içeriğe atlama linkleri
- ✅ **Responsive Tasarım**: Tüm cihazlarda erişilebilir
- ✅ **Karanlık Mod**: Göz yorgunluğunu azaltma

### Güvenlik
- ✅ **SQL Injection Koruması**: PDO Prepared Statements
- ✅ **XSS Koruması**: Input sanitization
- ✅ **CSRF Koruması**: Token tabanlı form güvenliği
- ✅ **Brute-Force Koruması**: Giriş deneme limiti
- ✅ **Şifre Güvenliği**: bcrypt hash algoritması
- ✅ **Session Güvenliği**: Güvenli oturum yönetimi
- ✅ **IP Engelleme**: Kötüye kullanım önleme
- ✅ **Mesaj Şifreleme**: Özel mesajlar için AES-256

## 📋 Sistem Gereksinimleri

- **PHP**: 7.4 veya üzeri (PHP 8.x önerilir)
- **Veritabanı**: MySQL 5.7+ veya MariaDB 10.2+
- **Web Sunucu**: Apache 2.4+ veya Nginx 1.18+
- **PHP Eklentileri**:
  - PDO
  - PDO_MySQL
  - OpenSSL
  - GD veya ImageMagick (profil resimleri için)
  - mbstring
  - JSON
- **Diğer**:
  - mod_rewrite (Apache için, opsiyonel)
  - SSL Sertifikası (HTTPS için önerilir)

## 💾 Kurulum

### 1. Dosyaları Yükleyin

```bash
git clone https://github.com/integrumart/basitPHPforum.git
cd basitPHPforum
```

### 2. Veritabanını Oluşturun

MySQL/MariaDB'ye giriş yapın ve veritabanını içe aktarın:

```sql
mysql -u root -p
CREATE DATABASE basitforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE basitforum;
SOURCE database_enhanced.sql;
```

Veya phpMyAdmin üzerinden:
1. Yeni veritabanı oluşturun: `basitforum`
2. `database_enhanced.sql` dosyasını içe aktarın

### 3. Yapılandırma Dosyasını Düzenleyin

`includes/db.php` dosyasını açın ve veritabanı bilgilerinizi girin:

```php
define('DB_HOST', 'localhost');      // Veritabanı sunucusu
define('DB_USER', 'root');           // Veritabanı kullanıcısı
define('DB_PASS', 'your_password');  // Veritabanı şifresi
define('DB_NAME', 'basitforum');     // Veritabanı adı
```

Site URL'ini güncelleyin:

```php
define('SITE_URL', 'http://localhost/basitPHPforum');
```

### 4. Dizin İzinlerini Ayarlayın

```bash
chmod 755 uploads
chmod 755 assets
```

### 5. Tarayıcıdan Erişin

```
http://localhost/basitPHPforum
```

## 🔐 Varsayılan Admin Bilgileri

Kurulumdan sonra admin paneline giriş yapmak için:

- **Kullanıcı Adı**: `demo`
- **Şifre**: `demo`
- **Admin Panel**: `http://yoursite.com/admin/index.php`

⚠️ **ÖNEMLİ**: İlk girişten sonra mutlaka şifrenizi değiştirin!

## 🎯 Özellik Detayları

### Forum Kullanımı

#### Konu Açma
1. Kategori seçin
2. "Yeni Konu" butonuna tıklayın
3. Başlık ve içerik girin
4. İsteğe bağlı anket ekleyin
5. Paylaşın

#### Anket Oluşturma
- Konu oluştururken "Anket Ekle" seçeneğini işaretleyin
- En fazla 10 seçenek ekleyebilirsiniz
- Bitiş tarihi belirleyebilirsiniz
- Tek veya çoklu seçim yapısı

#### Beğeni Sistemi
- Her mesaj için beğeni/beğenmeme butonu
- Kullanıcı başına bir beğeni
- Anlık istatistik güncelleme

### Kullanıcı İşlemleri

#### Profil Düzenleme
1. Sağ üst menüden "Profilim"
2. "Profili Düzenle" butonuna tıklayın
3. Bilgilerinizi güncelleyin:
   - Profil resmi
   - Biyografi
   - Sosyal medya hesapları
4. Kaydet

#### Takip Sistemi
- Herhangi bir kullanıcı profiline gidin
- "Takip Et" butonuna tıklayın
- Takip ettiğiniz kullanıcıların aktivitelerinden bildirim alın

#### Özel Mesajlaşma
1. Kullanıcı profilinde "Mesaj Gönder"
2. Mesajınızı yazın
3. Gönder
4. Tüm mesajlarınızı "Mesajlar" sayfasında görün

### Admin İşlemleri

#### Kullanıcı Yönetimi
- Kullanıcıları görüntüleme
- Onaylama/Yasaklama
- Admin yetkisi verme
- Kullanıcı silme

#### Kategori Yönetimi
- Yeni kategori ekleme
- Kategori düzenleme
- Sıralama
- Silme

#### Site Ayarları
- Site başlığı ve açıklaması
- SMTP ayarları (e-posta bildirimleri için)
- Güvenlik ayarları
- Admin panel URL'i

### SMTP Ayarları (E-posta Bildirimleri)

Admin panelinden ayarlayın:

```
SMTP Sunucu: smtp.gmail.com (örnek)
SMTP Port: 587
SMTP Kullanıcı: your-email@gmail.com
SMTP Şifre: your-app-password
Gönderen E-posta: noreply@yoursite.com
Gönderen Ad: Forum Sistemi
```

## ♿ Erişilebilirlik

Bu sistem WCAG 2.1 AA standartlarına tam uyumludur:

### Ekran Okuyucu Desteği
- Tüm elemanlarda ARIA etiketleri
- Anlamlı rol tanımlamaları
- Alt text tüm görsellerde
- Live regions dinamik içerik için

### Klavye Navigasyonu
- Tab/Shift+Tab ile gezinme
- Enter/Space ile aktivasyon
- ESC ile modal kapatma
- Arrow keys ile menü gezinme

### Görsel Erişilebilirlik
- Minimum 4.5:1 kontrast oranı
- Renk körü dostu
- Yazı boyutu ayarlama
- Karanlık mod desteği

### Test Edilmiş Ekran Okuyucular
- ✅ NVDA (Windows)
- ✅ JAWS (Windows)
- ✅ VoiceOver (macOS/iOS)
- ✅ TalkBack (Android)

### Erişilebilirlik Testi

Lighthouse ile test edin:

```bash
# Chrome DevTools > Lighthouse > Accessibility
# Hedef: 90+ skor
```

## 🔒 Güvenlik

### Güvenlik Önlemleri

1. **Veritabanı Güvenliği**
   - PDO Prepared Statements
   - Parameterized queries
   - SQL injection koruması

2. **Giriş Güvenliği**
   - Password hashing (bcrypt)
   - Brute-force koruması
   - IP bazlı engelleme
   - Session güvenliği

3. **Form Güvenliği**
   - CSRF token koruması
   - Input validation
   - XSS sanitization
   - File upload validation

4. **Özel Mesaj Güvenliği**
   - AES-256 şifreleme
   - End-to-end encryption
   - Güvenli storage

### Güvenlik Best Practices

```php
// CSRF Token kullanımı
<input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">

// Input sanitization
$clean_data = temizle($user_input);

// Secure file upload
$result = dosyaYukle($_FILES['file'], 'uploads', ['jpg', 'png']);
```

### Güvenlik Güncellemeleri

Düzenli olarak kontrol edin:
- PHP versiyonu
- Veritabanı versiyonu
- Bağımlılıklar
- Güvenlik yamalarını uygulayın

## 📁 Dosya Yapısı

```
basitPHPforum/
├── index.php                 # Ana sayfa
├── giris.php                 # Giriş sayfası
├── kayit.php                 # Kayıt sayfası
├── cikis.php                 # Çıkış işlemi
├── profile.php               # Kullanıcı profili
├── profile-edit.php          # Profil düzenleme
├── konu.php                  # Konu detayı
├── yeni-konu.php             # Yeni konu oluşturma
├── kategori.php              # Kategori konuları
├── messages.php              # Özel mesajlaşma
├── news.php                  # Haberler
├── blog.php                  # Blog yazıları
├── contact.php               # İletişim formu
├── config.php                # Ana konfigürasyon
├── database_enhanced.sql     # Veritabanı şeması
│
├── includes/                 # Core dosyalar
│   ├── db.php               # Veritabanı bağlantısı
│   ├── functions.php        # Yardımcı fonksiyonlar
│   └── notifications.php    # Bildirim sistemi
│
├── assets/                   # Frontend assets
│   ├── css/
│   │   └── main.css         # Ana CSS
│   ├── js/
│   │   └── main.js          # Ana JavaScript
│   └── images/              # Site görselleri
│
├── uploads/                  # Kullanıcı yüklemeleri
│   ├── profiles/            # Profil resimleri
│   ├── news/                # Haber görselleri
│   └── blogs/               # Blog görselleri
│
├── admin/                    # Admin paneli
│   ├── index.php            # Admin ana sayfa
│   ├── users.php            # Kullanıcı yönetimi
│   ├── categories.php       # Kategori yönetimi
│   ├── topics.php           # Konu yönetimi
│   ├── news.php             # Haber yönetimi
│   ├── blog.php             # Blog yönetimi
│   ├── messages.php         # İletişim mesajları
│   └── settings.php         # Site ayarları
│
└── README.md                 # Bu dosya
```

## ⚙️ Konfigürasyon

### Veritabanı Ayarları

`includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'basitforum');
```

### Site Ayarları

```php
define('SITE_NAME', 'Forum Adı');
define('SITE_URL', 'http://yoursite.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
```

### E-posta Ayarları

Admin panelinden veya doğrudan veritabanından:

```sql
UPDATE admin_settings SET deger = 'smtp.gmail.com' WHERE anahtar = 'smtp_host';
UPDATE admin_settings SET deger = '587' WHERE anahtar = 'smtp_port';
UPDATE admin_settings SET deger = 'your-email@gmail.com' WHERE anahtar = 'smtp_user';
```

## 🔌 API ve Entegrasyonlar

### Sosyal Medya Paylaşım

Otomatik olarak entegre edilmiş:
- WhatsApp Web/App
- Telegram
- Facebook
- LinkedIn
- X/Twitter

### PHPMailer (Opsiyonel)

Daha gelişmiş e-posta özellikleri için:

```bash
composer require phpmailer/phpmailer
```

### WebSocket (Gelecek Özellik)

Gerçek zamanlı mesajlaşma için:
- Ratchet (PHP)
- Socket.IO (Node.js)

## 🤝 Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen şu adımları izleyin:

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'inizi push edin (`git push origin feature/AmazingFeature`)
5. Pull Request açın

### Katkı Kuralları

- Kod standartlarına uyun (PSR-12)
- Erişilebilirlik standartlarını koruyun
- Güvenlik best practices uygulayın
- Test ekleyin
- Dokümantasyon güncelleyin

## 🐛 Hata Bildirimi

Hata bulursanız:
1. GitHub Issues'da yeni issue açın
2. Detaylı açıklama ve reproduce adımları ekleyin
3. Ekran görüntüsü ekleyin (mümkünse)
4. Sistem bilgilerinizi belirtin

## 📞 Destek

- **GitHub Issues**: [Issues](https://github.com/integrumart/basitPHPforum/issues)
- **Dokümantasyon**: Bu README dosyası
- **E-posta**: support@yoursite.com

## 📄 Lisans

MIT License - Detaylar için [LICENSE](LICENSE) dosyasına bakınız.

## 🙏 Teşekkürler

Bu proje aşağıdaki açık kaynak projelerden ilham almıştır:
- phpBB
- MyBB
- Discourse

## 📊 Versiyon Geçmişi

### v2.0.0 (2024) - Gelişmiş Forum Sistemi
- ✅ Kapsamlı kullanıcı profilleri
- ✅ Bildirim sistemi
- ✅ Özel mesajlaşma
- ✅ Anket sistemi
- ✅ Takip sistemi
- ✅ WCAG 2.1 AA erişilebilirlik
- ✅ Gelişmiş admin paneli
- ✅ Haber ve blog sistemi

### v1.0.0 (2023) - Basit Forum
- ✅ Temel forum özellikleri
- ✅ Kategori ve konu yönetimi
- ✅ Kullanıcı sistemi

---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!

🌐 [Demo](http://demo.yoursite.com) | 📚 [Dokümantasyon](https://github.com/integrumart/basitPHPforum/wiki) | 💬 [Forum](https://forum.yoursite.com)
