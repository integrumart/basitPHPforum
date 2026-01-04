<?php
/**
 * Profil Düzenleme Sayfası
 * Gelişmiş Forum Sistemi
 */
require_once 'config.php';

// Giriş kontrolü
if (!girisKontrol()) {
    yonlendir('giris.php');
}

$kullanici_id = $_SESSION['kullanici_id'];

// Kullanıcı bilgilerini çek
$sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$sorgu->execute([$kullanici_id]);
$kullanici = $sorgu->fetch();

$hata = '';
$basari = '';

// Form gönderildi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrfTokenDogrula($_POST['csrf_token'] ?? '')) {
        $hata = 'Güvenlik hatası!';
    } else {
        $biyografi = trim($_POST['biyografi'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');
        $facebook = trim($_POST['facebook'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $github = trim($_POST['github'] ?? '');
        
        // Profil resmi yükleme
        $profil_resmi = $kullanici['profil_resmi'];
        if (isset($_FILES['profil_resmi']) && $_FILES['profil_resmi']['error'] === UPLOAD_ERR_OK) {
            $upload_result = dosyaYukle($_FILES['profil_resmi'], 'uploads', ['jpg', 'jpeg', 'png', 'gif']);
            if ($upload_result['success']) {
                // Eski resmi sil
                if ($profil_resmi && file_exists(__DIR__ . '/uploads/' . $profil_resmi)) {
                    unlink(__DIR__ . '/uploads/' . $profil_resmi);
                }
                $profil_resmi = $upload_result['filename'];
            } else {
                $hata = $upload_result['message'];
            }
        }
        
        if (empty($hata)) {
            try {
                $sorgu = $db->prepare("
                    UPDATE kullanicilar 
                    SET biyografi = ?, profil_resmi = ?, website = ?, twitter = ?, 
                        facebook = ?, linkedin = ?, github = ?
                    WHERE id = ?
                ");
                $sorgu->execute([
                    $biyografi, $profil_resmi, $website, $twitter, 
                    $facebook, $linkedin, $github, $kullanici_id
                ]);
                
                $basari = 'Profiliniz başarıyla güncellendi!';
                
                // Kullanıcı bilgilerini yenile
                $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
                $sorgu->execute([$kullanici_id]);
                $kullanici = $sorgu->fetch();
            } catch (PDOException $e) {
                $hata = 'Güncelleme hatası: ' . $e->getMessage();
            }
        }
    }
}

$sayfa_basligi = 'Profil Düzenle';
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
                <a href="profile.php">Profilim</a>
                <a href="messages.php">Mesajlar</a>
                <span style="color: #ecf0f1;">Hoşgeldin, <?php echo temizle($_SESSION['kullanici_adi']); ?></span>
                <?php if (adminKontrol()): ?>
                    <a href="admin/index.php">Admin Paneli</a>
                <?php endif; ?>
                <a href="cikis.php">Çıkış</a>
            </nav>
        </div>
    </header>

    <main id="main-content" class="container" role="main">
        <h2><?php echo $sayfa_basligi; ?></h2>
        
        <?php if ($hata): ?>
            <div class="hata" role="alert"><?php echo temizle($hata); ?></div>
        <?php endif; ?>
        
        <?php if ($basari): ?>
            <div class="basari" role="alert"><?php echo temizle($basari); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                
                <div class="form-group">
                    <label for="profil_resmi">Profil Resmi</label>
                    <?php if ($kullanici['profil_resmi']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="uploads/<?php echo temizle($kullanici['profil_resmi']); ?>" 
                                 alt="Mevcut profil resmi"
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                        </div>
                    <?php endif; ?>
                    <input type="file" 
                           id="profil_resmi" 
                           name="profil_resmi" 
                           accept="image/jpeg,image/png,image/gif"
                           aria-describedby="profil_resmi_hint">
                    <span class="form-hint" id="profil_resmi_hint">
                        Desteklenen formatlar: JPG, PNG, GIF (Maks: 5MB)
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="biyografi">Biyografi</label>
                    <textarea id="biyografi" 
                              name="biyografi" 
                              placeholder="Kendiniz hakkında kısa bir bilgi..."
                              aria-describedby="biyografi_hint"><?php echo temizle($kullanici['biyografi'] ?? ''); ?></textarea>
                    <span class="form-hint" id="biyografi_hint">
                        Profilinizde görünecek kısa bir tanıtım metni.
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" 
                           id="website" 
                           name="website" 
                           placeholder="https://ornek.com"
                           value="<?php echo temizle($kullanici['website'] ?? ''); ?>"
                           aria-describedby="website_hint">
                    <span class="form-hint" id="website_hint">
                        Kişisel veya iş web siteniz.
                    </span>
                </div>
                
                <h3 style="margin-top: 30px; margin-bottom: 20px;">Sosyal Medya Hesapları</h3>
                
                <div class="form-group">
                    <label for="twitter">Twitter Kullanıcı Adı</label>
                    <input type="text" 
                           id="twitter" 
                           name="twitter" 
                           placeholder="kullaniciadi"
                           value="<?php echo temizle($kullanici['twitter'] ?? ''); ?>"
                           aria-describedby="twitter_hint">
                    <span class="form-hint" id="twitter_hint">
                        @ işareti olmadan sadece kullanıcı adınız.
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="facebook">Facebook Kullanıcı Adı</label>
                    <input type="text" 
                           id="facebook" 
                           name="facebook" 
                           placeholder="kullaniciadi"
                           value="<?php echo temizle($kullanici['facebook'] ?? ''); ?>"
                           aria-describedby="facebook_hint">
                    <span class="form-hint" id="facebook_hint">
                        Facebook profil URL'inizdeki kullanıcı adı.
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn Kullanıcı Adı</label>
                    <input type="text" 
                           id="linkedin" 
                           name="linkedin" 
                           placeholder="kullaniciadi"
                           value="<?php echo temizle($kullanici['linkedin'] ?? ''); ?>"
                           aria-describedby="linkedin_hint">
                    <span class="form-hint" id="linkedin_hint">
                        LinkedIn profil URL'inizdeki kullanıcı adı.
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="github">GitHub Kullanıcı Adı</label>
                    <input type="text" 
                           id="github" 
                           name="github" 
                           placeholder="kullaniciadi"
                           value="<?php echo temizle($kullanici['github'] ?? ''); ?>"
                           aria-describedby="github_hint">
                    <span class="form-hint" id="github_hint">
                        GitHub profil URL'inizdeki kullanıcı adı.
                    </span>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn btn-success">Kaydet</button>
                    <a href="profile.php" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
