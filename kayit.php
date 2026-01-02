<?php
require_once 'config.php';

$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';
    
    if (empty($kullanici_adi) || empty($email) || empty($sifre)) {
        $hata = 'Tüm alanları doldurunuz!';
    } elseif ($sifre !== $sifre_tekrar) {
        $hata = 'Şifreler eşleşmiyor!';
    } elseif (strlen($sifre) < 6) {
        $hata = 'Şifre en az 6 karakter olmalıdır!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = 'Geçersiz e-posta adresi!';
    } else {
        try {
            // Kullanıcı adı veya email zaten var mı kontrol et
            $sorgu = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ? OR email = ?");
            $sorgu->execute([$kullanici_adi, $email]);
            
            if ($sorgu->rowCount() > 0) {
                $hata = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor!';
            } else {
                // Yeni kullanıcı oluştur
                $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
                $sorgu = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
                $sorgu->execute([$kullanici_adi, $email, $sifre_hash]);
                
                // Kayıt başarılı, giriş sayfasına yönlendir
                yonlendir('giris.php?kayit=basarili');
            }
        } catch(PDOException $e) {
            $hata = 'Kayıt hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="giris.php">Giriş</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container" style="max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px;">Kayıt Ol</h2>
            
            <?php if ($hata): ?>
                <div class="hata"><?php echo temizle($hata); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Kullanıcı Adı:</label>
                    <input type="text" name="kullanici_adi" required 
                           value="<?php echo temizle($_POST['kullanici_adi'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>E-posta:</label>
                    <input type="email" name="email" required 
                           value="<?php echo temizle($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Şifre:</label>
                    <input type="password" name="sifre" required>
                </div>
                
                <div class="form-group">
                    <label>Şifre Tekrar:</label>
                    <input type="password" name="sifre_tekrar" required>
                </div>
                
                <button type="submit" class="btn">Kayıt Ol</button>
            </form>
        </div>
    </div>
</body>
</html>
