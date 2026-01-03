<?php
require_once 'config.php';

// Kategorileri ve istatistikleri çek
$sorgu = $db->prepare("
    SELECT k.*, 
           COUNT(DISTINCT ko.id) as konu_sayisi,
           COUNT(DISTINCT m.id) as mesaj_sayisi
    FROM kategoriler k
    LEFT JOIN konular ko ON k.id = ko.kategori_id
    LEFT JOIN mesajlar m ON ko.id = m.konu_id
    GROUP BY k.id
    ORDER BY k.id ASC
");
$sorgu->execute();
$kategoriler = $sorgu->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <?php if (girisKontrol()): ?>
                    <span style="color: #ecf0f1;">Hoşgeldin, <?php echo temizle($_SESSION['kullanici_adi']); ?></span>
                    <?php if (adminKontrol()): ?>
                        <a href="admin.php">Admin Paneli</a>
                    <?php endif; ?>
                    <a href="cikis.php">Çıkış</a>
                <?php else: ?>
                    <a href="giris.php">Giriş</a>
                    <a href="kayit.php">Kayıt</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2 style="margin-bottom: 20px;">Kategoriler</h2>
        
        <div class="kategori-liste">
            <?php foreach ($kategoriler as $kategori): ?>
                <div class="kategori-item">
                    <h2>
                        <a href="kategori.php?id=<?php echo $kategori['id']; ?>">
                            <?php echo temizle($kategori['baslik']); ?>
                        </a>
                    </h2>
                    <p><?php echo temizle($kategori['aciklama']); ?></p>
                    <div class="istatistik">
                        <span>📝 <?php echo $kategori['konu_sayisi']; ?> Konu</span>
                        <span>💬 <?php echo $kategori['mesaj_sayisi']; ?> Mesaj</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
