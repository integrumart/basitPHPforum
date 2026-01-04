-- Gelişmiş Forum Sistemi - Kapsamlı Veritabanı Şeması
-- Bu dosya mevcut basit forum sistemini tam özellikli bir platforma dönüştürür

CREATE DATABASE IF NOT EXISTS basitforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE basitforum;

-- Mevcut tabloları düşür (temiz kurulum için)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS poll_votes;
DROP TABLE IF EXISTS poll_options;
DROP TABLE IF EXISTS polls;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS notification_prefs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS message_participants;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS followers;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS blog_comments;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS mesajlar;
DROP TABLE IF EXISTS konular;
DROP TABLE IF EXISTS kategoriler;
DROP TABLE IF EXISTS kullanicilar;
DROP TABLE IF EXISTS admin_settings;
DROP TABLE IF EXISTS login_attempts;
SET FOREIGN_KEY_CHECKS = 1;

-- Geliştirilmiş Kullanıcılar Tablosu
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    onay_durumu TINYINT(1) NOT NULL DEFAULT 1,
    yasakli TINYINT(1) NOT NULL DEFAULT 0,
    profil_resmi VARCHAR(255) DEFAULT NULL,
    biyografi TEXT DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(100) DEFAULT NULL,
    facebook VARCHAR(100) DEFAULT NULL,
    linkedin VARCHAR(100) DEFAULT NULL,
    github VARCHAR(100) DEFAULT NULL,
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    son_giris DATETIME DEFAULT NULL,
    INDEX idx_kullanici_adi (kullanici_adi),
    INDEX idx_email (email),
    INDEX idx_onay_durumu (onay_durumu),
    INDEX idx_yasakli (yasakli)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategoriler Tablosu (Geliştirilmiş)
CREATE TABLE kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(100) NOT NULL,
    aciklama TEXT,
    ust_kategori_id INT DEFAULT NULL,
    sira INT DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ust_kategori_id) REFERENCES kategoriler(id) ON DELETE SET NULL,
    INDEX idx_baslik (baslik),
    INDEX idx_ust_kategori (ust_kategori_id),
    INDEX idx_sira (sira)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Konular Tablosu (Geliştirilmiş)
CREATE TABLE konular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    baslik VARCHAR(200) NOT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    son_mesaj_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    goruntulenme INT DEFAULT 0,
    sabitlendi TINYINT(1) DEFAULT 0,
    kilitlendi TINYINT(1) DEFAULT 0,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_kategori_id (kategori_id),
    INDEX idx_kullanici_id (kullanici_id),
    INDEX idx_son_mesaj (son_mesaj_tarihi),
    INDEX idx_sabitlendi (sabitlendi),
    INDEX idx_goruntulenme (goruntulenme)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesajlar (Yanıtlar) Tablosu (Geliştirilmiş)
CREATE TABLE mesajlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    konu_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    mesaj TEXT NOT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    duzenleme_tarihi DATETIME DEFAULT NULL,
    duzenleyen_id INT DEFAULT NULL,
    FOREIGN KEY (konu_id) REFERENCES konular(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (duzenleyen_id) REFERENCES kullanicilar(id) ON DELETE SET NULL,
    INDEX idx_konu_id (konu_id),
    INDEX idx_kullanici_id (kullanici_id),
    INDEX idx_olusturma_tarihi (olusturma_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beğeni/Beğenmeme Sistemi
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tip ENUM('konu', 'mesaj') NOT NULL,
    hedef_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    deger TINYINT(1) NOT NULL COMMENT '1=beğeni, -1=beğenmeme',
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (tip, hedef_id, kullanici_id),
    INDEX idx_tip_hedef (tip, hedef_id),
    INDEX idx_kullanici_id (kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anket Sistemi
CREATE TABLE polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    konu_id INT NOT NULL,
    soru VARCHAR(255) NOT NULL,
    coklu_secim TINYINT(1) DEFAULT 0,
    bitis_tarihi DATETIME DEFAULT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konu_id) REFERENCES konular(id) ON DELETE CASCADE,
    INDEX idx_konu_id (konu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anket Seçenekleri
CREATE TABLE poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    secenek VARCHAR(255) NOT NULL,
    sira INT DEFAULT 0,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    INDEX idx_poll_id (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anket Oyları
CREATE TABLE poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (poll_id, kullanici_id, option_id),
    INDEX idx_poll_id (poll_id),
    INDEX idx_kullanici_id (kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Konu Abonelikleri
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    konu_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konu_id) REFERENCES konular(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subscription (konu_id, kullanici_id),
    INDEX idx_konu_id (konu_id),
    INDEX idx_kullanici_id (kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bildirimler
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    tip ENUM('reply', 'like', 'follow', 'message', 'mention', 'subscription') NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    mesaj TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    okundu TINYINT(1) DEFAULT 0,
    gonderen_id INT DEFAULT NULL,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (gonderen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_kullanici_id (kullanici_id),
    INDEX idx_okundu (okundu),
    INDEX idx_tarih (tarih)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bildirim Tercihleri
CREATE TABLE notification_prefs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    email_reply TINYINT(1) DEFAULT 1,
    email_like TINYINT(1) DEFAULT 1,
    email_follow TINYINT(1) DEFAULT 1,
    email_message TINYINT(1) DEFAULT 1,
    email_digest TINYINT(1) DEFAULT 0,
    site_reply TINYINT(1) DEFAULT 1,
    site_like TINYINT(1) DEFAULT 1,
    site_follow TINYINT(1) DEFAULT 1,
    site_message TINYINT(1) DEFAULT 1,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_prefs (kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Özel Mesajlar
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gonderen_id INT NOT NULL,
    alici_id INT NOT NULL,
    mesaj TEXT NOT NULL,
    okundu TINYINT(1) DEFAULT 0,
    sifreli TINYINT(1) DEFAULT 0,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gonderen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (alici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_gonderen_id (gonderen_id),
    INDEX idx_alici_id (alici_id),
    INDEX idx_okundu (okundu),
    INDEX idx_tarih (tarih)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Takipçi Sistemi
CREATE TABLE followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takip_eden_id INT NOT NULL,
    takip_edilen_id INT NOT NULL,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takip_eden_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (takip_edilen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (takip_eden_id, takip_edilen_id),
    INDEX idx_takip_eden (takip_eden_id),
    INDEX idx_takip_edilen (takip_edilen_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Haberler
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    gorsel VARCHAR(255) DEFAULT NULL,
    yazar_id INT NOT NULL,
    yayinlanma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    zamanlanmis_yayin DATETIME DEFAULT NULL,
    one_cikan TINYINT(1) DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1,
    goruntulenme INT DEFAULT 0,
    FOREIGN KEY (yazar_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_yayinlanma_tarihi (yayinlanma_tarihi),
    INDEX idx_one_cikan (one_cikan),
    INDEX idx_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Sistemi
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    gorsel VARCHAR(255) DEFAULT NULL,
    yazar_id INT NOT NULL,
    kategori VARCHAR(100) DEFAULT NULL,
    etiketler VARCHAR(255) DEFAULT NULL,
    yayinlanma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    zamanlanmis_yayin DATETIME DEFAULT NULL,
    aktif TINYINT(1) DEFAULT 1,
    goruntulenme INT DEFAULT 0,
    FOREIGN KEY (yazar_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_yayinlanma_tarihi (yayinlanma_tarihi),
    INDEX idx_kategori (kategori),
    INDEX idx_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İletişim Mesajları
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    konu VARCHAR(200) NOT NULL,
    mesaj TEXT NOT NULL,
    okundu TINYINT(1) DEFAULT 0,
    arsivlendi TINYINT(1) DEFAULT 0,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_okundu (okundu),
    INDEX idx_tarih (tarih)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Ayarları
CREATE TABLE admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anahtar VARCHAR(100) UNIQUE NOT NULL,
    deger TEXT,
    aciklama TEXT DEFAULT NULL,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_anahtar (anahtar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brute Force Koruması için Giriş Denemeleri
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_adresi VARCHAR(45) NOT NULL,
    kullanici_adi VARCHAR(50) DEFAULT NULL,
    basarili TINYINT(1) DEFAULT 0,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_adresi (ip_adresi),
    INDEX idx_tarih (tarih)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan Verileri Ekle

-- Varsayılan Admin Kullanıcısı (demo/demo)
INSERT INTO kullanicilar (kullanici_adi, email, sifre, is_admin, onay_durumu) VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);
-- Şifre: demo (bcrypt hash)

-- Varsayılan Kategoriler
INSERT INTO kategoriler (baslik, aciklama, sira) VALUES
('Genel Tartışma', 'Genel konular hakkında konuşabileceğiniz bölüm', 1),
('Teknoloji', 'Teknoloji, yazılım ve donanım hakkında tartışmalar', 2),
('Duyurular', 'Önemli duyurular ve haberler', 3),
('Destek', 'Yardım ve destek talepleriniz için', 4),
('Öneri ve Şikayetler', 'Görüş ve önerileriniz', 5);

-- Varsayılan Admin Ayarları
INSERT INTO admin_settings (anahtar, deger, aciklama) VALUES
('site_title', 'Gelişmiş Forum Sistemi', 'Site başlığı'),
('site_description', 'Modern ve erişilebilir PHP tabanlı forum platformu', 'Site açıklaması'),
('smtp_host', '', 'SMTP sunucu adresi'),
('smtp_port', '587', 'SMTP port numarası'),
('smtp_user', '', 'SMTP kullanıcı adı'),
('smtp_pass', '', 'SMTP şifresi'),
('smtp_from_email', '', 'Gönderen e-posta adresi'),
('smtp_from_name', 'Forum Sistemi', 'Gönderen adı'),
('admin_url', 'admin', 'Admin paneli URL (güvenlik için değiştirilebilir)'),
('allow_registration', '1', 'Yeni kullanıcı kaydına izin ver'),
('require_email_verification', '0', 'E-posta doğrulaması gerektir'),
('posts_per_page', '20', 'Sayfa başına gönderi sayısı'),
('max_login_attempts', '5', 'Maksimum giriş denemesi'),
('login_lockout_time', '15', 'Giriş kilitleme süresi (dakika)');

-- Hoş Geldiniz Konusu
INSERT INTO konular (kategori_id, kullanici_id, baslik) VALUES
(3, 1, 'Forum Sistemine Hoş Geldiniz!');

INSERT INTO mesajlar (konu_id, kullanici_id, mesaj) VALUES
(1, 1, 'Gelişmiş forum sistemimize hoş geldiniz! Bu platform modern özelliklerle donatılmış, erişilebilir ve kullanıcı dostu bir topluluk platformudur.

Özellikler:
✅ Gelişmiş kullanıcı profilleri
✅ Bildirim sistemi
✅ Özel mesajlaşma
✅ Anket sistemi
✅ Beğeni/Beğenmeme
✅ Takip sistemi
✅ Haber ve blog yönetimi
✅ Tam erişilebilirlik desteği
✅ Mobil uyumlu tasarım
✅ Güvenli ve hızlı

İyi eğlenceler!');

-- Varsayılan bildirim tercihlerini oluştur
INSERT INTO notification_prefs (kullanici_id) VALUES (1);
