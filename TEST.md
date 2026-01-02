# Test ve Doğrulama Kılavuzu

Bu kılavuz, forum sisteminin doğru çalıştığını doğrulamak için adım adım test prosedürü sağlar.

## Ön Koşullar

1. Veritabanı kurulumu tamamlanmış olmalı (`database.sql` içe aktarılmış)
2. `config.php` dosyası doğru yapılandırılmış olmalı
3. Web sunucusu (Apache/Nginx) çalışır durumda olmalı
4. PHP 7.0+ yüklü olmalı

## Test Senaryoları

### 1. Ana Sayfa Testi

**Adımlar:**
1. Tarayıcıda `http://localhost/basitPHPforum/index.php` adresini açın
2. Sayfa yüklenmeli ve 3 kategori görüntülenmeli:
   - Genel Tartışma
   - Teknoloji
   - Duyurular

**Beklenen Sonuç:**
- ✅ Kategoriler listeleniyor
- ✅ Her kategorinin açıklaması görünüyor
- ✅ Konu ve mesaj sayıları görünüyor (başlangıçta 0)
- ✅ Giriş ve Kayıt linkleri görünüyor

### 2. Kullanıcı Kaydı Testi

**Adımlar:**
1. "Kayıt" linkine tıklayın
2. Formu doldurun:
   - Kullanıcı Adı: `test_kullanici`
   - E-posta: `test@example.com`
   - Şifre: `test123`
   - Şifre Tekrar: `test123`
3. "Kayıt Ol" butonuna tıklayın

**Beklenen Sonuç:**
- ✅ Giriş sayfasına yönlendiriliyorsunuz
- ✅ "Kayıt başarılı" mesajı görünüyor

**Hata Testleri:**
- Aynı kullanıcı adıyla tekrar kayıt deneyin → Hata mesajı görünmeli
- Şifreler eşleşmiyorsa → "Şifreler eşleşmiyor" hatası
- Şifre 6 karakterden azsa → Hata mesajı

### 3. Kullanıcı Girişi Testi

**Adımlar:**
1. Giriş sayfasında (`giris.php`) formu doldurun:
   - Kullanıcı Adı: `test_kullanici`
   - Şifre: `test123`
2. "Giriş Yap" butonuna tıklayın

**Beklenen Sonuç:**
- ✅ Ana sayfaya yönlendiriliyorsunuz
- ✅ Üst menüde "Hoşgeldin, test_kullanici" yazısı görünüyor
- ✅ "Çıkış" linki görünüyor

**Hata Testleri:**
- Yanlış şifre ile giriş deneyin → "Kullanıcı adı veya şifre hatalı" mesajı

### 4. Yeni Konu Açma Testi

**Adımlar:**
1. Giriş yapmış olduğunuzdan emin olun
2. Ana sayfadan bir kategori seçin (örn: "Genel Tartışma")
3. "Yeni Konu Aç" butonuna tıklayın
4. Formu doldurun:
   - Konu Başlığı: `İlk Test Konusu`
   - Mesaj: `Bu bir test mesajıdır. Forum sistemi çalışıyor!`
5. "Konuyu Aç" butonuna tıklayın

**Beklenen Sonuç:**
- ✅ Konu detay sayfasına yönlendiriliyorsunuz
- ✅ Mesajınız görünüyor
- ✅ Kullanıcı adınız ve tarih bilgisi görünüyor

### 5. Konuya Yanıt Verme Testi

**Adımlar:**
1. Açtığınız konunun sayfasında kalın
2. Sayfanın altındaki "Yanıt Yaz" formunu doldurun:
   - Mesaj: `Bu bir yanıt mesajıdır.`
3. "Yanıt Gönder" butonuna tıklayın

**Beklenen Sonuç:**
- ✅ Sayfa yenileniyor
- ✅ Yeni mesajınız listeye ekleniyor
- ✅ Mesajlar kronolojik sırada görünüyor

### 6. Görüntülenme Sayacı Testi

**Adımlar:**
1. Bir konuyu açın, görüntülenme sayısını not edin
2. Sayfayı yenileyin (F5)
3. Görüntülenme sayısının aynı kalmasını kontrol edin (session sayesinde)
4. Başka bir tarayıcı veya gizli pencerede aynı konuyu açın

**Beklenen Sonuç:**
- ✅ İlk açılışta görüntülenme sayısı artıyor
- ✅ Aynı oturumda yenileme yapınca artmıyor
- ✅ Farklı oturumda (tarayıcı) artıyor

### 7. Kategori Listesi Testi

**Adımlar:**
1. Ana sayfadan bir kategori seçin
2. Kategorideki konuların listelendiğini kontrol edin

**Beklenen Sonuç:**
- ✅ Konular listeleniyor
- ✅ Her konunun yazarı, yanıt sayısı ve görüntülenme görünüyor
- ✅ Son mesaj bilgisi görünüyor
- ✅ Konuya tıklandığında detay sayfası açılıyor

### 8. Çıkış Testi

**Adımlar:**
1. Üst menüden "Çıkış" linkine tıklayın

**Beklenen Sonuç:**
- ✅ Ana sayfaya yönlendiriliyorsunuz
- ✅ "Giriş" ve "Kayıt" linkleri tekrar görünüyor
- ✅ Kullanıcı adı görünmüyor

### 9. Güvenlik Testi

**XSS Koruması Testi:**
1. Yeni bir konu açın veya yanıt verin
2. Mesaj içine şunu yazın: `<script>alert('XSS')</script>`
3. Mesajı gönderin

**Beklenen Sonuç:**
- ✅ Script çalışmıyor
- ✅ Metin olarak görünüyor (HTML escape edilmiş)

**SQL Injection Testi:**
1. Giriş formunda kullanıcı adı alanına şunu yazın: `' OR '1'='1`
2. Giriş yapmayı deneyin

**Beklenen Sonuç:**
- ✅ Giriş başarısız oluyor
- ✅ Hata mesajı normal bir şekilde görünüyor

## Performans ve Kullanılabilirlik

### Responsive Tasarım Testi
1. Tarayıcı penceresini küçültün veya mobil görünümde açın
2. Tüm elemanların düzgün görünmesini kontrol edin

**Beklenen Sonuç:**
- ✅ Sayfa mobilde de okunabilir
- ✅ Butonlar ve linkler tıklanabilir
- ✅ Formlar düzgün çalışıyor

## Veritabanı Doğrulaması

SQL sorgularıyla veritabanını kontrol edebilirsiniz:

```sql
-- Kullanıcı sayısını kontrol et
SELECT COUNT(*) FROM kullanicilar;

-- Kategori sayısını kontrol et
SELECT COUNT(*) FROM kategoriler;

-- Konu sayısını kontrol et
SELECT COUNT(*) FROM konular;

-- Mesaj sayısını kontrol et
SELECT COUNT(*) FROM mesajlar;

-- En son eklenen konuları listele
SELECT * FROM konular ORDER BY olusturma_tarihi DESC LIMIT 5;
```

## Sorun Giderme

### Beyaz Sayfa Görünüyorsa
```php
// config.php dosyasının başına ekleyin:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Veritabanı Bağlantı Hatası
- `config.php` dosyasındaki DB_* sabitlerini kontrol edin
- MySQL sunucusunun çalıştığından emin olun
- Kullanıcı izinlerini kontrol edin

### CSS Yüklenmiyor
- `style.css` dosyasının doğru yolda olduğundan emin olun
- Tarayıcı konsolunu açın (F12) ve hataları kontrol edin

## Test Sonucu

Tüm testler başarıyla geçerse, forum sisteminiz kullanıma hazırdır! 🎉

## Önerilen Ek Testler

- [ ] Çok sayıda konu ve mesaj ekleyerek performans testi
- [ ] Farklı tarayıcılarda (Chrome, Firefox, Safari, Edge) test
- [ ] Özel karakterler ve Türkçe harflerle test
- [ ] Uzun başlık ve mesajlarla test
- [ ] Aynı anda birden fazla kullanıcıyla test
