<?php
require_once 'config.php';

$konu_id = intval($_GET['id'] ?? 0);

// Görüntülenme sayısını artır
$sorgu = $db->prepare("UPDATE konular SET goruntulenme = goruntulenme + 1 WHERE id = ?");
$sorgu->execute([$konu_id]);

// Konu bilgilerini çek
$sorgu = $db->prepare("
    SELECT ko.*, k.kullanici_adi, kat.baslik as kategori_baslik, kat.id as kategori_id
    FROM konular ko
    LEFT JOIN kullanicilar k ON ko.kullanici_id = k.id
    LEFT JOIN kategoriler kat ON ko.kategori_id = kat.id
    WHERE ko.id = ?
");
$sorgu->execute([$konu_id]);
$konu = $sorgu->fetch();

if (!$konu) {
    die('Konu bulunamadı!');
}

// Mesajları çek
$sorgu = $db->prepare("
    SELECT m.*, k.kullanici_adi
    FROM mesajlar m
    LEFT JOIN kullanicilar k ON m.kullanici_id = k.id
    WHERE m.konu_id = ?
    ORDER BY m.olusturma_tarihi ASC
");
$sorgu->execute([$konu_id]);
$mesajlar = $sorgu->fetchAll();

$hata = '';
$basari = '';

// Yeni mesaj ekle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && girisKontrol()) {
    $mesaj = trim($_POST['mesaj'] ?? '');
    
    if (empty($mesaj)) {
        $hata = 'Mesaj alanı boş olamaz!';
    } elseif (strlen($mesaj) < 5) {
        $hata = 'Mesaj en az 5 karakter olmalıdır!';
    } else {
        try {
            $db->beginTransaction();
            
            // Yeni mesaj ekle
            $sorgu = $db->prepare("INSERT INTO mesajlar (konu_id, kullanici_id, mesaj) VALUES (?, ?, ?)");
            $sorgu->execute([$konu_id, $_SESSION['kullanici_id'], $mesaj]);
            
            // Konunun son mesaj tarihini güncelle
            $sorgu = $db->prepare("UPDATE konular SET son_mesaj_tarihi = NOW() WHERE id = ?");
            $sorgu->execute([$konu_id]);
            
            $db->commit();
            $basari = 'Mesajınız eklendi!';
            
            // Mesajları yeniden yükle
            $sorgu = $db->prepare("
                SELECT m.*, k.kullanici_adi
                FROM mesajlar m
                LEFT JOIN kullanicilar k ON m.kullanici_id = k.id
                WHERE m.konu_id = ?
                ORDER BY m.olusturma_tarihi ASC
            ");
            $sorgu->execute([$konu_id]);
            $mesajlar = $sorgu->fetchAll();
            
            $_POST['mesaj'] = '';
        } catch(PDOException $e) {
            $db->rollBack();
            $hata = 'Mesaj gönderme hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo temizle($konu['baslik']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="kategori.php?id=<?php echo $konu['kategori_id']; ?>">
                    <?php echo temizle($konu['kategori_baslik']); ?>
                </a>
                <?php if (girisKontrol()): ?>
                    <span style="color: #ecf0f1;">Hoşgeldin, <?php echo temizle($_SESSION['kullanici_adi']); ?></span>
                    <a href="cikis.php">Çıkış</a>
                <?php else: ?>
                    <a href="giris.php">Giriş</a>
                    <a href="kayit.php">Kayıt</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2 style="margin-bottom: 20px;"><?php echo temizle($konu['baslik']); ?></h2>
        
        <div class="mesaj-container">
            <?php foreach ($mesajlar as $index => $mesaj): ?>
                <div class="mesaj-item">
                    <div class="mesaj-baslik">
                        <span class="mesaj-yazar">👤 <?php echo temizle($mesaj['kullanici_adi']); ?></span>
                        <span class="mesaj-tarih">
                            📅 <?php echo date('d.m.Y H:i', strtotime($mesaj['olusturma_tarihi'])); ?>
                        </span>
                    </div>
                    <div class="mesaj-icerik">
                        <?php echo nl2br(temizle($mesaj['mesaj'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (girisKontrol()): ?>
            <div class="form-container">
                <h3 style="margin-bottom: 20px;">Yanıt Yaz</h3>
                
                <?php if ($hata): ?>
                    <div class="hata"><?php echo temizle($hata); ?></div>
                <?php endif; ?>
                
                <?php if ($basari): ?>
                    <div class="basari"><?php echo temizle($basari); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Mesajınız:</label>
                        <textarea name="mesaj" required placeholder="Yanıtınızı buraya yazın..."><?php echo temizle($_POST['mesaj'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Yanıt Gönder</button>
                </form>
            </div>
        <?php else: ?>
            <div class="bilgi">
                Yanıt yazmak için <a href="giris.php" style="color: #3498db;">giriş yapmalısınız</a>.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
