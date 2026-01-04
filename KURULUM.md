# Gelişmiş Forum Sistemi - Detaylı Kurulum Talimatları

## 📋 Gereksinimler

### Minimum Gereksinimler
- **PHP**: 7.4 veya üzeri (PHP 8.x önerilir)
- **Veritabanı**: MySQL 5.7+ veya MariaDB 10.2+
- **Web Sunucu**: Apache 2.4+ veya Nginx 1.18+
- **Disk Alanı**: En az 50 MB

### Gerekli PHP Eklentileri
- `pdo`
- `pdo_mysql`
- `openssl`
- `mbstring`
- `json`
- `gd` veya `imagick` (profil resimleri için)

### Opsiyonel Ama Önerilen
- SSL sertifikası (HTTPS)
- PHPMailer (gelişmiş e-posta özellikleri için)
- Opcache (performans için)
- mod_rewrite (Apache için)

## 🚀 Hızlı Kurulum

### 1. Dosyaları İndirin

```bash
git clone https://github.com/integrumart/basitPHPforum.git
cd basitPHPforum
```

Ya da ZIP dosyasını indirip açın.

### 2. Veritabanını Oluşturun

#### MySQL/MariaDB Komut Satırı:

```bash
mysql -u root -p
```

Ardından:

```sql
CREATE DATABASE basitforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE basitforum;
SOURCE database_enhanced.sql;
exit;
```

#### phpMyAdmin ile:

1. phpMyAdmin'e giriş yapın
2. Sol menüden "Yeni" tıklayın
3. Veritabanı adı: `basitforum`
4. Karakter kümesi: `utf8mb4_unicode_ci`
5. "Oluştur" düğmesine tıklayın
6. "İçe Aktar" sekmesine gidin
7. `database_enhanced.sql` dosyasını seçin
8. "Git" düğmesine tıklayın

### 3. Yapılandırma

`includes/db.php` dosyasını düzenleyin:

```php
<?php
// Veritabanı Ayarları
define('DB_HOST', 'localhost');           // Veritabanı sunucusu
define('DB_USER', 'your_db_username');    // Veritabanı kullanıcı adı
define('DB_PASS', 'your_db_password');    // Veritabanı şifresi
define('DB_NAME', 'basitforum');          // Veritabanı adı

// Site Ayarları
define('SITE_NAME', 'Forum Adınız');
define('SITE_URL', 'https://yoursite.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
?>
```

### 4. Dizin İzinleri (Linux/Unix)

```bash
# Uploads dizinine yazma izni
chmod 755 uploads
chmod 755 assets

# Güvenlik için
chmod 644 includes/*.php
chmod 644 *.php
```

### 5. Apache .htaccess (Opsiyonel)

`.htaccess` dosyası oluşturun:

```apache
# Dizin listelemeyi engelle
Options -Indexes

# Hata sayfaları
ErrorDocument 404 /404.php
ErrorDocument 403 /403.php

# PHP ayarları
php_flag display_errors Off
php_value upload_max_filesize 5M
php_value post_max_size 10M

# Güvenlik başlıkları
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# HTTPS yönlendirmesi (SSL varsa)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 6. Nginx Yapılandırması (Opsiyonel)

`/etc/nginx/sites-available/forum` dosyası:

```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /var/www/forum;
    index index.php;

    # Maksimum dosya yükleme boyutu
    client_max_body_size 5M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Hassas dosyaları engelle
    location ~ /\. {
        deny all;
    }

    location ~ /(includes|config\.php) {
        deny all;
    }
}
```

### 7. İlk Erişim

Tarayıcınızda sitenize gidin:
```
http://yoursite.com
```

## 👤 Varsayılan Admin Girişi

Kurulumdan sonra admin paneline giriş yapabilirsiniz:

- **URL**: `http://yoursite.com/admin/index.php`
- **Kullanıcı Adı**: `demo`
- **Şifre**: `demo`

⚠️ **ÖNEMLİ**: İlk girişten sonra mutlaka şifrenizi değiştirin!

## ⚙️ Gelişmiş Yapılandırma

### SMTP E-posta Ayarları

Admin panelinden veya doğrudan veritabanından ayarlayın:

```sql
UPDATE admin_settings SET deger = 'smtp.gmail.com' WHERE anahtar = 'smtp_host';
UPDATE admin_settings SET deger = '587' WHERE anahtar = 'smtp_port';
UPDATE admin_settings SET deger = 'your-email@gmail.com' WHERE anahtar = 'smtp_user';
UPDATE admin_settings SET deger = 'your-app-password' WHERE anahtar = 'smtp_pass';
UPDATE admin_settings SET deger = 'noreply@yoursite.com' WHERE anahtar = 'smtp_from_email';
```

#### Gmail için Uygulama Şifresi Alma:

1. Google hesabınıza gidin
2. Güvenlik > 2 Adımlı Doğrulama
3. Uygulama şifreleri
4. "Mail" ve cihazınızı seçin
5. Oluşturulan şifreyi kopyalayın

### PHPMailer Kurulumu (Opsiyonel)

Daha gelişmiş e-posta özellikleri için:

```bash
cd /path/to/forum
composer require phpmailer/phpmailer
```

### Performans Optimizasyonu

#### Opcache Etkinleştirme

`php.ini` dosyasında:

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

#### Gzip Sıkıştırma

`.htaccess` dosyasına ekleyin:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

## 🔒 Güvenlik Önerileri

### 1. Dosya İzinleri

```bash
# Hiçbir dosya çalıştırılabilir olmamalı
find . -type f -exec chmod 644 {} \;

# Sadece dizinler 755
find . -type d -exec chmod 755 {} \;

# Hassas dosyalar
chmod 600 includes/db.php
```

### 2. Veritabanı Güvenliği

- Güçlü veritabanı şifresi kullanın
- Root kullanıcısı kullanmayın
- Sadece gerekli izinleri verin

```sql
CREATE USER 'forum_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON basitforum.* TO 'forum_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. PHP Güvenlik Ayarları

`php.ini` dosyasında:

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
session.cookie_httponly = 1
session.cookie_secure = 1
```

### 4. SSL/HTTPS Kullanımı

Let's Encrypt ile ücretsiz SSL:

```bash
sudo apt-get install certbot
sudo certbot --apache -d yoursite.com
```

### 5. Güvenlik Duvarı

```bash
# UFW ile (Ubuntu/Debian)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 🐛 Sorun Giderme

### Veritabanı Bağlantı Hatası

**Belirtiler**: "Veritabanı bağlantı hatası" mesajı

**Çözümler**:
1. `includes/db.php` dosyasındaki bilgileri kontrol edin
2. MySQL servisini kontrol edin: `sudo service mysql status`
3. Kullanıcı izinlerini kontrol edin
4. Veritabanının var olduğunu doğrulayın

### Upload Dizini Yazılamıyor

**Belirtiler**: "Dosya yükleme hatası" mesajı

**Çözümler**:
```bash
chmod 755 uploads
chown www-data:www-data uploads  # Apache/Nginx kullanıcısı
```

### E-posta Gönderilmiyor

**Belirtiler**: Bildirimlerde e-posta gelmiyor

**Çözümler**:
1. SMTP ayarlarını kontrol edin
2. Port 587'nin açık olduğunu doğrulayın
3. Gmail için "Daha az güvenli uygulamalara" erişim açın
4. PHP mail() fonksiyonunun çalıştığını test edin

### CSS/JS Dosyaları Yüklenmiyor

**Belirtiler**: Sayfa stilleri bozuk

**Çözümler**:
1. Dosya yollarını kontrol edin
2. Tarayıcı önbelleğini temizleyin (Ctrl+Shift+R)
3. Konsol hatalarını kontrol edin (F12)
4. Apache/Nginx erişim loglarını kontrol edin

### 500 Internal Server Error

**Belirtiler**: Sayfa açılmıyor

**Çözümler**:
1. Apache/Nginx hata loglarını kontrol edin
2. PHP hata loglarını kontrol edin
3. `.htaccess` dosyasını kontrol edin
4. PHP versiyonunu kontrol edin

## 📱 Test ve Doğrulama

### 1. Fonksiyonel Test

- [ ] Kullanıcı kaydı çalışıyor
- [ ] Giriş/Çıkış çalışıyor
- [ ] Konu açma çalışıyor
- [ ] Mesaj gönderme çalışıyor
- [ ] Profil düzenleme çalışıyor
- [ ] Özel mesajlaşma çalışıyor
- [ ] İletişim formu çalışıyor

### 2. Erişilebilirlik Testi

Lighthouse ile test edin:
1. Chrome DevTools aç (F12)
2. Lighthouse sekmesine git
3. Accessibility seçeneğini işaretle
4. "Generate report" tıkla
5. **Hedef**: 90+ skor

### 3. Tarayıcı Uyumluluğu

Test edilmesi gerekenler:
- [ ] Chrome/Edge (son 2 versiyon)
- [ ] Firefox (son 2 versiyon)
- [ ] Safari (son 2 versiyon)
- [ ] Mobil Chrome (Android)
- [ ] Mobil Safari (iOS)

### 4. Performans Testi

```bash
# Apache Bench ile
ab -n 1000 -c 10 http://yoursite.com/

# PageSpeed Insights
# https://pagespeed.web.dev/
```

## 🔄 Güncelleme

Yeni versiyona güncelleme:

```bash
# Yedek alın
mysqldump -u root -p basitforum > backup_$(date +%Y%m%d).sql
tar -czf forum_backup_$(date +%Y%m%d).tar.gz /path/to/forum

# Güncellemeleri çekin
git pull origin main

# Veritabanı migrasyonlarını çalıştırın (varsa)
mysql -u root -p basitforum < migrations/update_xxx.sql
```

## 📞 Destek

Sorun yaşıyorsanız:

1. [GitHub Issues](https://github.com/integrumart/basitPHPforum/issues)
2. [Dokümantasyon](https://github.com/integrumart/basitPHPforum/wiki)
3. [FAQ](https://github.com/integrumart/basitPHPforum/wiki/FAQ)

## 📝 Sonraki Adımlar

Kurulumdan sonra:

1. ✅ Admin şifresini değiştirin
2. ✅ SMTP ayarlarını yapılandırın
3. ✅ Site başlığını ve açıklamasını güncelleyin
4. ✅ İlk kategorileri oluşturun
5. ✅ SSL sertifikası kurulumunu yapın
6. ✅ Düzenli yedekleme sistemini kurun
7. ✅ Güvenlik taramalarını yapın

## 🎉 Başarılı Kurulum!

Forum sisteminiz artık hazır! İyi kullanımlar dileriz.

---

💡 **İpucu**: Bu kurulum kılavuzunu daha sonra başvurmak üzere saklayın.

