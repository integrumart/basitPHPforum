# Basit PHP Forum - Kurulum Talimatları

## Gereksinimler

- PHP 7.0 veya üzeri
- MySQL 5.6 veya üzeri / MariaDB 10.0 veya üzeri
- Apache veya Nginx web sunucusu
- PHP PDO MySQL eklentisi

## Kurulum Adımları

### 1. Dosyaları Yükleyin

Tüm dosyaları web sunucunuzun kök dizinine (örneğin: `htdocs`, `www`, `public_html`) yükleyin.

### 2. Veritabanını Oluşturun

MySQL veritabanınıza erişin ve `database.sql` dosyasını içe aktarın:

```bash
mysql -u root -p < database.sql
```

Veya phpMyAdmin kullanarak:
1. phpMyAdmin'e giriş yapın
2. Yeni bir veritabanı oluşturun (örneğin: `basitforum`)
3. İçe Aktar sekmesine gidin
4. `database.sql` dosyasını seçin ve içe aktarın

### 3. Yapılandırma Dosyasını Düzenleyin

`config.php` dosyasını açın ve veritabanı ayarlarınızı güncelleyin:

```php
define('DB_HOST', 'localhost');      // Veritabanı sunucusu
define('DB_USER', 'root');           // Veritabanı kullanıcı adı
define('DB_PASS', '');               // Veritabanı şifresi
define('DB_NAME', 'basitforum');     // Veritabanı adı

define('SITE_NAME', 'Basit Forum');  // Site adı
define('SITE_URL', 'http://localhost/basitPHPforum'); // Site URL'si
```

### 4. Dosya İzinlerini Ayarlayın (Linux/Unix)

```bash
chmod 644 *.php
chmod 644 *.css
chmod 755 .
```

### 5. Web Tarayıcıda Açın

Tarayıcınızda sitenize gidin:
```
http://localhost/basitPHPforum
```

### 6. İlk Kullanıcıyı Oluşturun

1. "Kayıt" linkine tıklayın
2. Kullanıcı bilgilerinizi girin
3. Kayıt olduktan sonra giriş yapın

## Özellikler

- ✅ Kullanıcı kayıt ve giriş sistemi
- ✅ Kategori bazlı forum yapısı
- ✅ Konu oluşturma ve yanıtlama
- ✅ Görüntülenme sayacı
- ✅ Güvenli şifre saklama (password_hash)
- ✅ SQL injection koruması (PDO prepared statements)
- ✅ XSS koruması (htmlspecialchars)
- ✅ Temiz ve responsive tasarım

## Güvenlik Notları

- Canlı sunucuda `config.php` dosyasındaki veritabanı bilgilerini mutlaka değiştirin
- Güçlü veritabanı şifreleri kullanın
- PHP hata raporlamayı canlı sunucuda kapatın
- HTTPS kullanın

## Sorun Giderme

### "Veritabanı bağlantı hatası" alıyorum
- `config.php` dosyasındaki veritabanı bilgilerini kontrol edin
- MySQL sunucusunun çalıştığından emin olun
- Veritabanının oluşturulduğundan emin olun

### Sayfalar boş görünüyor
- PHP hata raporlamayı açın: `error_reporting(E_ALL);`
- Apache/Nginx hata loglarını kontrol edin
- PHP PDO MySQL eklentisinin yüklü olduğundan emin olun

### CSS stilleri yüklenmiyor
- `style.css` dosyasının doğru yolda olduğundan emin olun
- Tarayıcı önbelleğini temizleyin (Ctrl+F5)

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.
