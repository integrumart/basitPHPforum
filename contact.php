<?php
/**
 * İletişim Formu
 * Gelişmiş Forum Sistemi
 */
require_once 'config.php';

$hata = '';
$basari = '';
$dogrulama_hatasi = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $konu = trim($_POST['konu'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');
    $cevap = intval($_POST['cevap'] ?? 0);
    $beklenen = intval($_POST['beklenen'] ?? 0);
    
    // Doğrulamalar
    if (empty($ad) || empty($email) || empty($konu) || empty($mesaj)) {
        $hata = 'Tüm alanları doldurunuz!';
    } elseif (!emailDogrula($email)) {
        $hata = 'Geçersiz e-posta adresi!';
    } elseif ($cevap !== $beklenen) {
        $hata = 'Matematik sorusunu yanlış cevapladınız!';
        $dogrulama_hatasi = true;
    } else {
        try {
            $sorgu = $db->prepare("
                INSERT INTO contact_messages (ad, email, konu, mesaj)
                VALUES (?, ?, ?, ?)
            ");
            $sorgu->execute([$ad, $email, $konu, $mesaj]);
            
            $basari = 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.';
            
            // Formu temizle
            $_POST = [];
        } catch (PDOException $e) {
            $hata = 'Mesaj gönderme hatası!';
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Basit matematik sorusu oluştur (spam koruması)
$sayi1 = rand(1, 10);
$sayi2 = rand(1, 10);
$beklenen_cevap = $sayi1 + $sayi2;

$sayfa_basligi = 'İletişim';
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
                    <a href="profile.php">Profilim</a>
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
        <h2><?php echo $sayfa_basligi; ?></h2>
        
        <div class="contact-intro card">
            <div class="card-body">
                <p>
                    Bizimle iletişime geçmek için aşağıdaki formu kullanabilirsiniz. 
                    Tüm mesajlarınız en kısa sürede değerlendirilecek ve size dönüş yapılacaktır.
                </p>
                <p>
                    <strong>İletişim konuları:</strong>
                </p>
                <ul>
                    <li>Teknik destek ve sorunlar</li>
                    <li>Öneri ve şikayetler</li>
                    <li>İş birlikleri</li>
                    <li>Genel sorular</li>
                </ul>
            </div>
        </div>
        
        <?php if ($hata): ?>
            <div class="hata" role="alert" aria-live="polite"><?php echo temizle($hata); ?></div>
        <?php endif; ?>
        
        <?php if ($basari): ?>
            <div class="basari" role="alert" aria-live="polite"><?php echo temizle($basari); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" aria-label="İletişim formu">
                <div class="form-group">
                    <label for="ad" class="required">Adınız Soyadınız</label>
                    <input type="text" 
                           id="ad" 
                           name="ad" 
                           required 
                           aria-required="true"
                           value="<?php echo temizle($_POST['ad'] ?? ''); ?>"
                           placeholder="Adınız ve soyadınız">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">E-posta Adresiniz</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           aria-required="true"
                           value="<?php echo temizle($_POST['email'] ?? ''); ?>"
                           placeholder="ornek@email.com">
                </div>
                
                <div class="form-group">
                    <label for="konu" class="required">Konu</label>
                    <input type="text" 
                           id="konu" 
                           name="konu" 
                           required 
                           aria-required="true"
                           value="<?php echo temizle($_POST['konu'] ?? ''); ?>"
                           placeholder="Mesaj konusu">
                </div>
                
                <div class="form-group">
                    <label for="mesaj" class="required">Mesajınız</label>
                    <textarea id="mesaj" 
                              name="mesaj" 
                              required 
                              aria-required="true"
                              placeholder="Mesajınızı buraya yazın..."
                              style="min-height: 200px;"><?php echo temizle($_POST['mesaj'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="cevap" class="required">
                        Spam Koruması: <?php echo $sayi1; ?> + <?php echo $sayi2; ?> = ?
                    </label>
                    <input type="number" 
                           id="cevap" 
                           name="cevap" 
                           required 
                           aria-required="true"
                           aria-describedby="cevap_hint"
                           placeholder="Cevabı girin"
                           style="max-width: 150px;">
                    <input type="hidden" name="beklenen" value="<?php echo $beklenen_cevap; ?>">
                    <span class="form-hint" id="cevap_hint">
                        Lütfen yukarıdaki basit matematik sorusunu cevaplayın.
                    </span>
                </div>
                
                <button type="submit" class="btn btn-success">
                    ✉️ Mesaj Gönder
                </button>
            </form>
        </div>
        
        <div class="contact-info card" style="margin-top: 30px;">
            <div class="card-header">
                <h3>Diğer İletişim Yolları</h3>
            </div>
            <div class="card-body">
                <p>
                    <strong>E-posta:</strong> <a href="mailto:info@forumsite.com">info@forumsite.com</a><br>
                    <strong>Telefon:</strong> +90 (XXX) XXX XX XX<br>
                    <strong>Adres:</strong> İstanbul, Türkiye
                </p>
                <p>
                    <strong>Çalışma Saatleri:</strong><br>
                    Pazartesi - Cuma: 09:00 - 18:00<br>
                    Cumartesi - Pazar: Kapalı
                </p>
            </div>
        </div>
    </main>
    
    <style>
        .contact-intro ul {
            list-style: none;
            padding-left: 0;
            margin-top: 10px;
        }
        
        .contact-intro li {
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .contact-intro li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-weight: bold;
        }
        
        .contact-info a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>
