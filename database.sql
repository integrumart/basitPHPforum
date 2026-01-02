-- Basit Forum Veritabanı Şeması

CREATE DATABASE IF NOT EXISTS basitforum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE basitforum;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kullanici_adi (kullanici_adi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategoriler tablosu
CREATE TABLE IF NOT EXISTS kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(100) NOT NULL,
    aciklama TEXT,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_baslik (baslik)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Konular (Threads) tablosu
CREATE TABLE IF NOT EXISTS konular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    baslik VARCHAR(200) NOT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    son_mesaj_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    goruntulenme INT DEFAULT 0,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_kategori_id (kategori_id),
    INDEX idx_son_mesaj (son_mesaj_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesajlar (Posts) tablosu
CREATE TABLE IF NOT EXISTS mesajlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    konu_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    mesaj TEXT NOT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konu_id) REFERENCES konular(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    INDEX idx_konu_id (konu_id),
    INDEX idx_olusturma_tarihi (olusturma_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan kategoriler ekle
INSERT INTO kategoriler (baslik, aciklama) VALUES
('Genel Tartışma', 'Genel konular hakkında konuşabileceğiniz bölüm'),
('Teknoloji', 'Teknoloji, yazılım ve donanım hakkında tartışmalar'),
('Duyurular', 'Önemli duyurular ve haberler');
