<?php
require_once 'config.php';

$kategori_id = intval($_GET['id'] ?? 0);

// Kategori bilgilerini çek
$sorgu = $db->prepare("SELECT * FROM kategoriler WHERE id = ?");
$sorgu->execute([$kategori_id]);
$kategori = $sorgu->fetch();

if (!$kategori) {
    die('Kategori bulunamadı!');
}

// Konuları çek
$sorgu = $db->prepare("
    SELECT ko.*, 
           k.kullanici_adi,
           COUNT(m.id) as mesaj_sayisi,
           (SELECT kullanici_adi FROM kullanicilar WHERE id = (SELECT kullanici_id FROM mesajlar WHERE konu_id = ko.id ORDER BY olusturma_tarihi DESC LIMIT 1)) as son_yazar,
           (SELECT olusturma_tarihi FROM mesajlar WHERE konu_id = ko.id ORDER BY olusturma_tarihi DESC LIMIT 1) as son_mesaj_tarihi
    FROM konular ko
    LEFT JOIN kullanicilar k ON ko.kullanici_id = k.id
    LEFT JOIN mesajlar m ON ko.id = m.konu_id
    WHERE ko.kategori_id = ?
    GROUP BY ko.id
    ORDER BY ko.son_mesaj_tarihi DESC
");
$sorgu->execute([$kategori_id]);
$konular = $sorgu->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo temizle($kategori['baslik']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <a href="index.php">Ana Sayfa</a>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><?php echo temizle($kategori['baslik']); ?></h2>
            <?php if (girisKontrol()): ?>
                <a href="yeni-konu.php?kategori=<?php echo $kategori_id; ?>" class="btn">Yeni Konu Aç</a>
            <?php endif; ?>
        </div>
        
        <div class="bilgi" style="margin-bottom: 20px;">
            <?php echo temizle($kategori['aciklama']); ?>
        </div>
        
        <?php if (count($konular) > 0): ?>
            <div class="konu-liste">
                <?php foreach ($konular as $konu): ?>
                    <div class="konu-item">
                        <h3>
                            <a href="konu.php?id=<?php echo $konu['id']; ?>">
                                <?php echo temizle($konu['baslik']); ?>
                            </a>
                        </h3>
                        <div class="istatistik">
                            <span>👤 <?php echo temizle($konu['kullanici_adi']); ?></span>
                            <span>💬 <?php echo $konu['mesaj_sayisi']; ?> Yanıt</span>
                            <span>👁️ <?php echo $konu['goruntulenme']; ?> Görüntülenme</span>
                            <?php if ($konu['son_yazar']): ?>
                                <span>Son: <?php echo temizle($konu['son_yazar']); ?> - 
                                    <?php echo date('d.m.Y H:i', strtotime($konu['son_mesaj_tarihi'] ?? $konu['olusturma_tarihi'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bilgi">
                Bu kategoride henüz konu yok. İlk konuyu siz açabilirsiniz!
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
