<?php
require_once 'config.php';

// Admin girişi kontrolü
if (!girisKontrol() || $_SESSION['is_admin'] != 1) {
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
        <h2>Kategori İşlemleri</h2>
        <?php if (isset($mesaj)): ?>
            <div class="mesaj"><?php echo $mesaj; ?></div>
        <?php endif; ?>
        <?php if (isset($hata)): ?>
            <div class="hata"><?php echo $hata; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="kategori_baslik">Kategori Başlık:</label>
            <input type="text" name="kategori_baslik" id="kategori_baslik" required>
            <label for="kategori_aciklama">Kategori Açıklama:</label>
            <textarea name="kategori_aciklama" id="kategori_aciklama" required></textarea>
            <button type="submit" name="kategori_ekle">Kategori Ekle</button>
        </form>
        <h2>Mevcut Kategoriler</h2>
        <ul>
            <?php
            $kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY id ASC")->fetchAll();
            foreach ($kategoriler as $kategori) {
                echo "<li>{$kategori['baslik']} <a href='?kategori_sil={$kategori['id']}' style='color:red;'>Sil</a></li>";
            }
            ?>
        </ul>
        <h2>Kullanıcı Listesi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Admin mi?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kullanicilar as $kullanici): ?>
                    <tr>
                        <td><?php echo $kullanici['id']; ?></td>
                        <td><?php echo htmlspecialchars($kullanici['kullanici_adi']); ?></td>
                        <td><?php echo htmlspecialchars($kullanici['email']); ?></td>
                        <td><?php echo $kullanici['is_admin'] ? 'Evet' : 'Hayır'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>