# basitPHPforum

Basit, etkili ve güvenli PHP tabanlı forum scripti. MySQL veritabanı kullanır ve temel forum özelliklerini içerir.

## 🚀 Özellikler

- 📝 Kullanıcı kayıt ve giriş sistemi
- 📁 Kategori tabanlı forum yapısı
- 💬 Konu açma ve yanıtlama
- 👁️ Görüntülenme sayacı
- 🔒 Güvenli (PDO, password_hash, XSS koruması)
- 📱 Responsive tasarım
- 🎨 Temiz ve modern arayüz

## 📋 Gereksinimler

- PHP 7.0+
- MySQL 5.6+ veya MariaDB 10.0+
- Apache/Nginx
- PHP PDO MySQL eklentisi

## 💾 Kurulum

Detaylı kurulum talimatları için [KURULUM.md](KURULUM.md) dosyasına bakınız.

### Hızlı Kurulum

1. Dosyaları web sunucunuza yükleyin
2. MySQL'de `database.sql` dosyasını içe aktarın
3. `config.php` dosyasındaki veritabanı ayarlarını düzenleyin
4. Tarayıcıdan sitenizi açın ve kayıt olun

## 📁 Dosya Yapısı

```
basitPHPforum/
├── index.php          # Ana sayfa (kategori listesi)
├── kategori.php       # Kategori içindeki konular
├── konu.php           # Konu detayı ve mesajlar
├── yeni-konu.php      # Yeni konu açma
├── giris.php          # Kullanıcı girişi
├── kayit.php          # Kullanıcı kaydı
├── cikis.php          # Çıkış işlemi
├── config.php         # Yapılandırma ve veritabanı
├── style.css          # Stil dosyası
├── database.sql       # Veritabanı şeması
└── KURULUM.md         # Detaylı kurulum talimatları
```

## 🔐 Güvenlik

- SQL Injection koruması (PDO Prepared Statements)
- XSS koruması (htmlspecialchars)
- Güvenli şifre saklama (password_hash)
- Session tabanlı kimlik doğrulama

## 📝 Lisans

MIT License - Detaylar için [LICENSE](LICENSE) dosyasına bakınız.

## 🤝 Katkıda Bulunma

Pull request'ler kabul edilir. Büyük değişiklikler için önce bir issue açarak neyi değiştirmek istediğinizi tartışınız.
