<?php
// Veritabanı Bağlantı Ayarları
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'basitforum');

// Site Ayarları
define('SITE_NAME', 'Basit Forum');
define('SITE_URL', 'http://localhost/basitPHPforum');

// Oturum başlat
session_start();

// Veritabanı bağlantısı
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yardımcı fonksiyonlar
function temizle($veri) {
    return htmlspecialchars($veri, ENT_QUOTES, 'UTF-8');
}

function girisKontrol() {
    return isset($_SESSION['kullanici_id']);
}

function yonlendir($sayfa) {
    header("Location: $sayfa");
    exit();
}
?>
