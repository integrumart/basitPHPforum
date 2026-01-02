<?php
require_once 'config.php';

if (!girisKontrol()) {
    yonlendir('giris.php');
}

$kategori_id = intval($_GET['kategori'] ?? 0);

// Kategori var mı kontrol et
$sorgu = $db->prepare("SELECT * FROM kategoriler WHERE id = ?");
$sorgu->execute([$kategori_id]);
$kategori = $sorgu->fetch();

if (!$kategori) {
    die('Kategori bulunamadı!');
}

$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = trim($_POST['baslik'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');
    
    if (empty($baslik) || empty($mesaj)) {
        $hata = 'Başlık ve mesaj alanları zorunludur!';
    } elseif (strlen($baslik) < 5) {
        $hata = 'Başlık en az 5 karakter olmalıdır!';
    } elseif (strlen($mesaj) < 10) {
        $hata = 'Mesaj en az 10 karakter olmalıdır!';
    } else {
        try {
            $db->beginTransaction();
            
            // Yeni konu oluştur
            $sorgu = $db->prepare("INSERT INTO konular (kategori_id, kullanici_id, baslik) VALUES (?, ?, ?)");
            $sorgu->execute([$kategori_id, $_SESSION['kullanici_id'], $baslik]);
            $konu_id = $db->lastInsertId();
            
            // İlk mesajı ekle
            $sorgu = $db->prepare("INSERT INTO mesajlar (konu_id, kullanici_id, mesaj) VALUES (?, ?, ?)");
            $sorgu->execute([$konu_id, $_SESSION['kullanici_id'], $mesaj]);
            
            $db->commit();
            yonlendir("konu.php?id=$konu_id");
        } catch(PDOException $e) {
            $db->rollBack();
            $hata = 'Konu oluşturma hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Konu Aç - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="kategori.php?id=<?php echo $kategori_id; ?>">Geri Dön</a>
                <a href="cikis.php">Çıkış</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 style="margin-bottom: 20px;">Yeni Konu Aç: <?php echo temizle($kategori['baslik']); ?></h2>
            
            <?php if ($hata): ?>
                <div class="hata"><?php echo temizle($hata); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Konu Başlığı:</label>
                    <input type="text" name="baslik" required 
                           value="<?php echo temizle($_POST['baslik'] ?? ''); ?>"
                           placeholder="Konunuzu açıklayan kısa bir başlık">
                </div>
                
                <div class="form-group">
                    <label>Mesajınız:</label>
                    <textarea name="mesaj" required placeholder="Mesajınızı buraya yazın..."><?php echo temizle($_POST['mesaj'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn">Konuyu Aç</button>
                <a href="kategori.php?id=<?php echo $kategori_id; ?>" class="btn btn-secondary">İptal</a>
            </form>
        </div>
    </div>
</body>
</html>
