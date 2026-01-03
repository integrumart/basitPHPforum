<?php
require_once 'config.php';

// Admin girişi kontrolü
if (!adminKontrol()) {
    yonlendir('index.php');
}

// Kategori ekleme
if (isset($_POST['kategori_ekle'])) {
    $kategori_baslik = trim($_POST['kategori_baslik']);
    $kategori_aciklama = trim($_POST['kategori_aciklama']);
    
    if (!empty($kategori_baslik) && !empty($kategori_aciklama)) {
        $sorgu = $db->prepare("INSERT INTO kategoriler (baslik, aciklama) VALUES (?, ?)");
        $sorgu->execute([$kategori_baslik, $kategori_aciklama]);
        $mesaj = "Kategori başarıyla eklendi!";
    } else {
        $hata = "Tüm alanları doldurmanız gereklidir!";
    }
}

// Kategori silme
if (isset($_GET['kategori_sil'])) {
    $kategori_id = intval($_GET['kategori_sil']);
    $sorgu = $db->prepare("DELETE FROM kategoriler WHERE id = ?");
    $sorgu->execute([$kategori_id]);
    $mesaj = "Kategori başarıyla silindi!";
}

// Kullanıcıları Listele
$sorgu = $db->query("SELECT * FROM kullanicilar ORDER BY id ASC");
$kullanicilar = $sorgu->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Paneli</h1>
            <nav>
                <a href="index.php">Anasayfa</a>
                <a href="cikis.php">Çıkış</a>
            </nav>
        </div>
    </header>
    <div class="container">
        <div class="admin-panel">
            <h2>Kategori İşlemleri</h2>
            <?php if (isset($mesaj)): ?>
                <div class="basari"><?php echo temizle($mesaj); ?></div>
            <?php endif; ?>
            <?php if (isset($hata)): ?>
                <div class="hata"><?php echo temizle($hata); ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h3>Yeni Kategori Ekle</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="kategori_baslik">Kategori Başlık:</label>
                        <input type="text" name="kategori_baslik" id="kategori_baslik" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori_aciklama">Kategori Açıklama:</label>
                        <textarea name="kategori_aciklama" id="kategori_aciklama" required></textarea>
                    </div>
                    <button type="submit" name="kategori_ekle" class="btn">Kategori Ekle</button>
                </form>
            </div>
            
            <h2 style="margin-top: 30px;">Mevcut Kategoriler</h2>
            <div class="kategori-liste admin-liste">
                <?php
                $kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY id ASC")->fetchAll();
                foreach ($kategoriler as $kategori): ?>
                    <div class="kategori-item">
                        <div class="kategori-bilgi">
                            <strong><?php echo temizle($kategori['baslik']); ?></strong>
                            <p><?php echo temizle($kategori['aciklama']); ?></p>
                        </div>
                        <a href="?kategori_sil=<?php echo $kategori['id']; ?>" 
                           class="btn btn-sil" 
                           onclick="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?');">Sil</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2 style="margin-top: 30px;">Kullanıcı Listesi</h2>
            <div class="tablo-container">
                <table class="admin-tablo">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>E-posta</th>
                            <th>Kayıt Tarihi</th>
                            <th>Admin mi?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kullanicilar as $kullanici): ?>
                            <tr>
                                <td><?php echo $kullanici['id']; ?></td>
                                <td><?php echo temizle($kullanici['kullanici_adi']); ?></td>
                                <td><?php echo temizle($kullanici['email']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                <td><?php echo $kullanici['is_admin'] ? '✓ Evet' : '✗ Hayır'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>