<?php
/**
 * Kullanıcı Profil Sayfası
 * Gelişmiş Forum Sistemi
 */
require_once 'config.php';

// Kullanıcı ID'sini al
$profil_id = intval($_GET['id'] ?? $_SESSION['kullanici_id'] ?? 0);

if ($profil_id == 0) {
    yonlendir('index.php');
}

// Kullanıcı bilgilerini çek
$sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$sorgu->execute([$profil_id]);
$profil = $sorgu->fetch();

if (!$profil) {
    die('Kullanıcı bulunamadı!');
}

// Kullanıcı istatistikleri
$stats = kullaniciIstatistikleri($profil_id, $db);

// Takip durumu kontrolü
$takip_ediyor = false;
if (girisKontrol() && $_SESSION['kullanici_id'] != $profil_id) {
    $sorgu = $db->prepare("SELECT id FROM followers WHERE takip_eden_id = ? AND takip_edilen_id = ?");
    $sorgu->execute([$_SESSION['kullanici_id'], $profil_id]);
    $takip_ediyor = $sorgu->rowCount() > 0;
}

// Takip/Takipten Çık İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && girisKontrol() && isset($_POST['takip_action'])) {
    if (!csrfTokenDogrula($_POST['csrf_token'] ?? '')) {
        $hata = 'Güvenlik hatası!';
    } else {
        try {
            if ($takip_ediyor) {
                // Takipten çık
                $sorgu = $db->prepare("DELETE FROM followers WHERE takip_eden_id = ? AND takip_edilen_id = ?");
                $sorgu->execute([$_SESSION['kullanici_id'], $profil_id]);
                $takip_ediyor = false;
                $basari = 'Takipten çıktınız.';
            } else {
                // Takip et
                $sorgu = $db->prepare("INSERT INTO followers (takip_eden_id, takip_edilen_id) VALUES (?, ?)");
                $sorgu->execute([$_SESSION['kullanici_id'], $profil_id]);
                $takip_ediyor = true;
                $basari = 'Takip ediyorsunuz.';
                
                // Bildirim oluştur
                takipBildirimi($profil_id, $_SESSION['kullanici_id'], $db);
            }
            
            // İstatistikleri yenile
            $stats = kullaniciIstatistikleri($profil_id, $db);
        } catch (PDOException $e) {
            $hata = 'İşlem hatası!';
        }
    }
}

// Son konular
$sorgu = $db->prepare("
    SELECT k.*, kat.baslik as kategori_baslik, kat.id as kategori_id
    FROM konular k
    LEFT JOIN kategoriler kat ON k.kategori_id = kat.id
    WHERE k.kullanici_id = ?
    ORDER BY k.olusturma_tarihi DESC
    LIMIT 10
");
$sorgu->execute([$profil_id]);
$son_konular = $sorgu->fetchAll();

// Son mesajlar
$sorgu = $db->prepare("
    SELECT m.*, k.baslik as konu_baslik, k.id as konu_id
    FROM mesajlar m
    LEFT JOIN konular k ON m.konu_id = k.id
    WHERE m.kullanici_id = ?
    ORDER BY m.olusturma_tarihi DESC
    LIMIT 10
");
$sorgu->execute([$profil_id]);
$son_mesajlar = $sorgu->fetchAll();

$sayfa_basligi = temizle($profil['kullanici_adi']) . ' - Profil';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sayfa_basligi; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="#main-content" class="skip-to-content">Ana içeriğe atla</a>
    
    <header role="banner">
        <div class="container">
            <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
            <nav role="navigation" aria-label="Ana menü">
                <a href="index.php">Ana Sayfa</a>
                <?php if (girisKontrol()): ?>
                    <div class="notification-center">
                        <button class="notification-bell" aria-label="Bildirimler" onclick="toggleNotifications()">
                            🔔
                            <?php 
                            $bildirim_sayisi = okunmamisBildirimSayisi($_SESSION['kullanici_id'], $db);
                            if ($bildirim_sayisi > 0): 
                            ?>
                                <span class="notification-badge" aria-label="<?php echo $bildirim_sayisi; ?> okunmamış bildirim">
                                    <?php echo $bildirim_sayisi; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <a href="messages.php">Mesajlar</a>
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
        <?php if (isset($hata)): ?>
            <div class="hata" role="alert"><?php echo temizle($hata); ?></div>
        <?php endif; ?>
        
        <?php if (isset($basari)): ?>
            <div class="basari" role="alert"><?php echo temizle($basari); ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-header card">
                <div class="card-body">
                    <div class="profile-main">
                        <div class="profile-avatar">
                            <?php if ($profil['profil_resmi']): ?>
                                <img src="uploads/<?php echo temizle($profil['profil_resmi']); ?>" 
                                     alt="<?php echo temizle($profil['kullanici_adi']); ?> profil resmi"
                                     class="avatar-img">
                            <?php else: ?>
                                <div class="avatar-placeholder" aria-label="Varsayılan profil resmi">
                                    <?php echo strtoupper(mb_substr($profil['kullanici_adi'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-info">
                            <h2><?php echo temizle($profil['kullanici_adi']); ?></h2>
                            <?php if ($profil['is_admin']): ?>
                                <span class="badge badge-admin" role="text">Yönetici</span>
                            <?php endif; ?>
                            
                            <p class="profile-join-date">
                                📅 Katılım: <?php echo tarihFormatla($profil['kayit_tarihi'], 'd.m.Y'); ?>
                            </p>
                            
                            <?php if ($profil['biyografi']): ?>
                                <p class="profile-bio"><?php echo nl2br(temizle($profil['biyografi'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($profil['website'] || $profil['twitter'] || $profil['facebook'] || $profil['linkedin'] || $profil['github']): ?>
                                <div class="profile-social">
                                    <?php if ($profil['website']): ?>
                                        <a href="<?php echo temizle($profil['website']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Web sitesi">
                                            🌐 Website
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profil['twitter']): ?>
                                        <a href="https://twitter.com/<?php echo temizle($profil['twitter']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter profili">
                                            🐦 Twitter
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profil['facebook']): ?>
                                        <a href="https://facebook.com/<?php echo temizle($profil['facebook']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook profili">
                                            📘 Facebook
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profil['linkedin']): ?>
                                        <a href="https://linkedin.com/in/<?php echo temizle($profil['linkedin']); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn profili">
                                            💼 LinkedIn
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profil['github']): ?>
                                        <a href="https://github.com/<?php echo temizle($profil['github']); ?>" target="_blank" rel="noopener noreferrer" aria-label="GitHub profili">
                                            💻 GitHub
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (girisKontrol() && $_SESSION['kullanici_id'] != $profil_id): ?>
                                <form method="POST" class="profile-actions">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                                    <button type="submit" name="takip_action" class="btn <?php echo $takip_ediyor ? 'btn-secondary' : 'btn-success'; ?>">
                                        <?php echo $takip_ediyor ? '✓ Takip Ediliyor' : '+ Takip Et'; ?>
                                    </button>
                                    <a href="messages.php?to=<?php echo $profil_id; ?>" class="btn btn-secondary">
                                        ✉️ Mesaj Gönder
                                    </a>
                                </form>
                            <?php elseif (girisKontrol() && $_SESSION['kullanici_id'] == $profil_id): ?>
                                <a href="profile-edit.php" class="btn btn-secondary">
                                    ✏️ Profili Düzenle
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['konu_sayisi']; ?></div>
                            <div class="stat-label">Konu</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['mesaj_sayisi']; ?></div>
                            <div class="stat-label">Mesaj</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['begeni_sayisi']; ?></div>
                            <div class="stat-label">Beğeni</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['takipci_sayisi']; ?></div>
                            <div class="stat-label">Takipçi</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['takip_sayisi']; ?></div>
                            <div class="stat-label">Takip</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="card">
                    <div class="card-header">
                        <h3>Son Konular</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($son_konular) > 0): ?>
                            <div class="konu-liste">
                                <?php foreach ($son_konular as $konu): ?>
                                    <div class="konu-item">
                                        <h4>
                                            <a href="konu.php?id=<?php echo $konu['id']; ?>">
                                                <?php echo temizle($konu['baslik']); ?>
                                            </a>
                                        </h4>
                                        <p>
                                            <a href="kategori.php?id=<?php echo $konu['kategori_id']; ?>">
                                                <?php echo temizle($konu['kategori_baslik']); ?>
                                            </a>
                                            · <?php echo goreciliZaman($konu['olusturma_tarihi']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="bilgi">Henüz konu açılmamış.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Son Mesajlar</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($son_mesajlar) > 0): ?>
                            <div class="mesaj-liste">
                                <?php foreach ($son_mesajlar as $mesaj): ?>
                                    <div class="mesaj-item">
                                        <div class="mesaj-content">
                                            <?php echo metinKisalt(temizle($mesaj['mesaj']), 150); ?>
                                        </div>
                                        <p>
                                            <a href="konu.php?id=<?php echo $mesaj['konu_id']; ?>">
                                                <?php echo temizle($mesaj['konu_baslik']); ?>
                                            </a>
                                            · <?php echo goreciliZaman($mesaj['olusturma_tarihi']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="bilgi">Henüz mesaj gönderilmemiş.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .profile-main {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            flex-shrink: 0;
        }
        
        .avatar-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary-color);
        }
        
        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-info h2 {
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-admin {
            background-color: var(--danger-color);
            color: white;
        }
        
        .profile-join-date {
            color: var(--text-muted);
            margin: 10px 0;
        }
        
        .profile-bio {
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .profile-social {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        
        .profile-social a {
            color: var(--secondary-color);
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .profile-social a:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .profile-content {
            margin-top: 20px;
        }
        
        .konu-liste .konu-item,
        .mesaj-liste .mesaj-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .konu-liste .konu-item:last-child,
        .mesaj-liste .mesaj-item:last-child {
            border-bottom: none;
        }
        
        .konu-liste h4 {
            margin-bottom: 5px;
        }
        
        .konu-liste h4 a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .konu-liste h4 a:hover {
            color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .profile-main {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-avatar {
                margin: 0 auto;
            }
            
            .profile-actions {
                flex-direction: column;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
    
    <script>
        function toggleNotifications() {
            // Bu fonksiyon daha sonra JavaScript dosyasında genişletilecek
            alert('Bildirim sistemi yakında aktif olacak!');
        }
    </script>
</body>
</html>
