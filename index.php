<?php
/**
 * Ana Sayfa - Kategori Listesi
 * Gelişmiş Forum Sistemi
 */
require_once 'config.php';

// Kategorileri ve istatistikleri çek
$sorgu = $db->prepare("
    SELECT k.*, 
           COUNT(DISTINCT ko.id) as konu_sayisi,
           COUNT(DISTINCT m.id) as mesaj_sayisi
    FROM kategoriler k
    LEFT JOIN konular ko ON k.id = ko.kategori_id AND ko.kilitlendi = 0
    LEFT JOIN mesajlar m ON ko.id = m.konu_id
    WHERE k.aktif = 1
    GROUP BY k.id
    ORDER BY k.sira ASC, k.id ASC
");
$sorgu->execute();
$kategoriler = $sorgu->fetchAll();

// Toplam istatistikler
$sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM kullanicilar WHERE yasakli = 0");
$sorgu->execute();
$toplam_kullanici = $sorgu->fetch()['sayi'];

$sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM konular");
$sorgu->execute();
$toplam_konu = $sorgu->fetch()['sayi'];

$sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM mesajlar");
$sorgu->execute();
$toplam_mesaj = $sorgu->fetch()['sayi'];

// Son aktiviteler
$sorgu = $db->prepare("
    SELECT k.*, u.kullanici_adi, kat.baslik as kategori_baslik
    FROM konular k
    LEFT JOIN kullanicilar u ON k.kullanici_id = u.id
    LEFT JOIN kategoriler kat ON k.kategori_id = kat.id
    ORDER BY k.son_mesaj_tarihi DESC
    LIMIT 5
");
$sorgu->execute();
$son_aktiviteler = $sorgu->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo adminAyarCek('site_description', $db, 'Modern ve erişilebilir forum platformu'); ?>">
    <title><?php echo SITE_NAME; ?> - Ana Sayfa</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="#main-content" class="skip-to-content">Ana içeriğe atla</a>
    
    <header role="banner">
        <div class="container">
            <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
            <button class="mobile-menu-toggle" aria-label="Menüyü aç/kapat" aria-expanded="false">
                ☰
            </button>
            <nav role="navigation" aria-label="Ana menü">
                <a href="index.php" aria-current="page">Ana Sayfa</a>
                <a href="contact.php">İletişim</a>
                <?php if (girisKontrol()): ?>
                    <a href="profile.php" aria-label="Profilim">Profilim</a>
                    <a href="messages.php" aria-label="Özel mesajlar">
                        Mesajlar
                        <?php 
                        $mesaj_sayisi = okunmamisMesajSayisi($_SESSION['kullanici_id'], $db);
                        if ($mesaj_sayisi > 0): 
                        ?>
                            <span class="badge" aria-label="<?php echo $mesaj_sayisi; ?> okunmamış mesaj">
                                <?php echo $mesaj_sayisi; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <span style="color: #ecf0f1;">Hoşgeldin, <?php echo temizle($_SESSION['kullanici_adi']); ?></span>
                    <?php if (adminKontrol()): ?>
                        <a href="admin/index.php">Admin Paneli</a>
                    <?php endif; ?>
                    <a href="cikis.php">Çıkış</a>
                <?php else: ?>
                    <a href="giris.php">Giriş</a>
                    <a href="kayit.php">Kayıt</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main id="main-content" class="container" role="main">
        <!-- İstatistik Kartları -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 36px; color: var(--secondary-color); font-weight: bold;">
                        <?php echo $toplam_kullanici; ?>
                    </div>
                    <div style="color: var(--text-muted); margin-top: 5px;">Kullanıcı</div>
                </div>
            </div>
            <div class="stat-card card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 36px; color: var(--success-color); font-weight: bold;">
                        <?php echo $toplam_konu; ?>
                    </div>
                    <div style="color: var(--text-muted); margin-top: 5px;">Konu</div>
                </div>
            </div>
            <div class="stat-card card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 36px; color: var(--info-color); font-weight: bold;">
                        <?php echo $toplam_mesaj; ?>
                    </div>
                    <div style="color: var(--text-muted); margin-top: 5px;">Mesaj</div>
                </div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;">Kategoriler</h2>
        
        <?php if (count($kategoriler) > 0): ?>
            <div class="kategori-liste" role="list">
                <?php foreach ($kategoriler as $kategori): ?>
                    <div class="kategori-item" role="listitem">
                        <h3>
                            <a href="kategori.php?id=<?php echo $kategori['id']; ?>">
                                <?php echo temizle($kategori['baslik']); ?>
                            </a>
                        </h3>
                        <p><?php echo temizle($kategori['aciklama']); ?></p>
                        <div class="istatistik">
                            <span aria-label="<?php echo $kategori['konu_sayisi']; ?> konu">
                                📝 <?php echo $kategori['konu_sayisi']; ?> Konu
                            </span>
                            <span aria-label="<?php echo $kategori['mesaj_sayisi']; ?> mesaj">
                                💬 <?php echo $kategori['mesaj_sayisi']; ?> Mesaj
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bilgi" role="status">
                Henüz kategori eklenmemiş. Admin panelinden kategori ekleyebilirsiniz.
            </div>
        <?php endif; ?>
        
        <!-- Son Aktiviteler -->
        <?php if (count($son_aktiviteler) > 0): ?>
            <div style="margin-top: 40px;">
                <h2 style="margin-bottom: 20px;">Son Aktiviteler</h2>
                <div class="card">
                    <div class="card-body">
                        <div class="activity-list">
                            <?php foreach ($son_aktiviteler as $aktivite): ?>
                                <div style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                                    <a href="konu.php?id=<?php echo $aktivite['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                        <?php echo temizle($aktivite['baslik']); ?>
                                    </a>
                                    <div style="font-size: 14px; color: var(--text-muted); margin-top: 5px;">
                                        <a href="kategori.php?id=<?php echo $aktivite['kategori_id']; ?>">
                                            <?php echo temizle($aktivite['kategori_baslik']); ?>
                                        </a>
                                        · 
                                        <a href="profile.php?id=<?php echo $aktivite['kullanici_id']; ?>">
                                            <?php echo temizle($aktivite['kullanici_adi']); ?>
                                        </a>
                                        · <?php echo goreciliZaman($aktivite['son_mesaj_tarihi']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <footer role="contentinfo" style="background: var(--primary-color); color: white; padding: 30px 0; margin-top: 50px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <div>
                    <h3 style="margin-bottom: 15px;"><?php echo SITE_NAME; ?></h3>
                    <p style="color: rgba(255,255,255,0.8);">
                        Modern, erişilebilir ve güvenli forum platformu.
                    </p>
                </div>
                <div>
                    <h3 style="margin-bottom: 15px;">Bağlantılar</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="index.php" style="color: rgba(255,255,255,0.8);">Ana Sayfa</a></li>
                        <li><a href="contact.php" style="color: rgba(255,255,255,0.8);">İletişim</a></li>
                        <?php if (!girisKontrol()): ?>
                            <li><a href="giris.php" style="color: rgba(255,255,255,0.8);">Giriş</a></li>
                            <li><a href="kayit.php" style="color: rgba(255,255,255,0.8);">Kayıt</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 style="margin-bottom: 15px;">Erişilebilirlik</h3>
                    <p style="color: rgba(255,255,255,0.8);">
                        Bu site WCAG 2.1 AA standartlarına uygundur ve ekran okuyucularla uyumludur.
                    </p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.6);">
                    © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </footer>
    
    <script src="assets/js/main.js"></script>
    
    <style>
        .badge {
            display: inline-block;
            background: var(--danger-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        footer a {
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: white !important;
        }
        
        footer ul li {
            margin: 8px 0;
        }
    </style>
</body>
</html>
