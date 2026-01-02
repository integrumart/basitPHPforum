<?php
require_once 'config.php';

$hata = '';
$basari = '';

// Kayıt başarılı mesajı
if (isset($_GET['kayit']) && $_GET['kayit'] == 'basarili') {
    $basari = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    
    if (empty($kullanici_adi) || empty($sifre)) {
        $hata = 'Kullanıcı adı ve şifre gereklidir!';
    } else {
        try {
            $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
            $sorgu->execute([$kullanici_adi]);
            $kullanici = $sorgu->fetch();
            
            if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
                yonlendir('index.php');
            } else {
                $hata = 'Kullanıcı adı veya şifre hatalı!';
            }
        } catch(PDOException $e) {
            $hata = 'Giriş hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="kayit.php">Kayıt</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container" style="max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px;">Giriş Yap</h2>
            
            <?php if ($hata): ?>
                <div class="hata"><?php echo temizle($hata); ?></div>
            <?php endif; ?>
            
            <?php if ($basari): ?>
                <div class="basari"><?php echo temizle($basari); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Kullanıcı Adı:</label>
                    <input type="text" name="kullanici_adi" required 
                           value="<?php echo temizle($_POST['kullanici_adi'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Şifre:</label>
                    <input type="password" name="sifre" required>
                </div>
                
                <button type="submit" class="btn">Giriş Yap</button>
            </form>
        </div>
    </div>
</body>
</html>
