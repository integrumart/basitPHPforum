<?php
/**
 * Özel Mesajlaşma Sistemi
 * Gelişmiş Forum Sistemi
 */
require_once 'config.php';

// Giriş kontrolü
if (!girisKontrol()) {
    yonlendir('giris.php');
}

$kullanici_id = $_SESSION['kullanici_id'];
$hata = '';
$basari = '';

// Yeni mesaj gönder
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    if (!csrfTokenDogrula($_POST['csrf_token'] ?? '')) {
        $hata = 'Güvenlik hatası!';
    } else {
        $alici_id = intval($_POST['alici_id'] ?? 0);
        $mesaj = trim($_POST['mesaj'] ?? '');
        
        if (empty($mesaj)) {
            $hata = 'Mesaj boş olamaz!';
        } elseif ($alici_id == $kullanici_id) {
            $hata = 'Kendinize mesaj gönderemezsiniz!';
        } else {
            try {
                // Mesajı şifrele
                $sifreli_mesaj = mesajSifrele($mesaj);
                
                $sorgu = $db->prepare("
                    INSERT INTO messages (gonderen_id, alici_id, mesaj, sifreli)
                    VALUES (?, ?, ?, 1)
                ");
                $sorgu->execute([$kullanici_id, $alici_id, $sifreli_mesaj]);
                
                // Bildirim gönder
                ozelMesajBildirimi($alici_id, $kullanici_id, $db);
                
                $basari = 'Mesajınız gönderildi!';
                $_POST = [];
            } catch (PDOException $e) {
                $hata = 'Mesaj gönderme hatası!';
                error_log("Message send error: " . $e->getMessage());
            }
        }
    }
}

// Mesajı okundu işaretle
if (isset($_GET['mark_read'])) {
    $mesaj_id = intval($_GET['mark_read']);
    $sorgu = $db->prepare("UPDATE messages SET okundu = 1 WHERE id = ? AND alici_id = ?");
    $sorgu->execute([$mesaj_id, $kullanici_id]);
}

// Gelen mesajlar
$sorgu = $db->prepare("
    SELECT m.*, 
           u.kullanici_adi as gonderen_adi,
           u.profil_resmi as gonderen_resim
    FROM messages m
    LEFT JOIN kullanicilar u ON m.gonderen_id = u.id
    WHERE m.alici_id = ?
    ORDER BY m.tarih DESC
    LIMIT 50
");
$sorgu->execute([$kullanici_id]);
$gelen_mesajlar = $sorgu->fetchAll();

// Gönderilen mesajlar
$sorgu = $db->prepare("
    SELECT m.*, 
           u.kullanici_adi as alici_adi,
           u.profil_resmi as alici_resim
    FROM messages m
    LEFT JOIN kullanicilar u ON m.alici_id = u.id
    WHERE m.gonderen_id = ?
    ORDER BY m.tarih DESC
    LIMIT 50
");
$sorgu->execute([$kullanici_id]);
$giden_mesajlar = $sorgu->fetchAll();

// Mesaj göndermek için kullanıcı listesi
$sorgu = $db->prepare("
    SELECT id, kullanici_adi 
    FROM kullanicilar 
    WHERE id != ? AND yasakli = 0
    ORDER BY kullanici_adi ASC
");
$sorgu->execute([$kullanici_id]);
$kullanicilar = $sorgu->fetchAll();

// URL'den alıcı ID'si varsa
$varsayilan_alici = intval($_GET['to'] ?? 0);

$sayfa_basligi = 'Mesajlar';
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
                <a href="messages.php" aria-current="page">Mesajlar</a>
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
        
        <div class="messages-container">
            <!-- Yeni Mesaj Gönder -->
            <div class="card">
                <div class="card-header">
                    <h3>Yeni Mesaj Gönder</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                        
                        <div class="form-group">
                            <label for="alici_id" class="required">Alıcı</label>
                            <select id="alici_id" name="alici_id" required aria-required="true">
                                <option value="">Kullanıcı seçin...</option>
                                <?php foreach ($kullanicilar as $k): ?>
                                    <option value="<?php echo $k['id']; ?>" 
                                            <?php echo ($varsayilan_alici == $k['id']) ? 'selected' : ''; ?>>
                                        <?php echo temizle($k['kullanici_adi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mesaj" class="required">Mesaj</label>
                            <textarea id="mesaj" 
                                      name="mesaj" 
                                      required 
                                      aria-required="true"
                                      placeholder="Mesajınızı buraya yazın..."></textarea>
                        </div>
                        
                        <button type="submit" name="send_message" class="btn btn-success">
                            ✉️ Mesaj Gönder
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Mesaj Listesi -->
            <div class="message-tabs">
                <button class="tab-btn active" onclick="showTab('gelen')" id="tab-gelen">
                    Gelen Mesajlar (<?php echo count($gelen_mesajlar); ?>)
                </button>
                <button class="tab-btn" onclick="showTab('giden')" id="tab-giden">
                    Gönderilen Mesajlar (<?php echo count($giden_mesajlar); ?>)
                </button>
            </div>
            
            <!-- Gelen Mesajlar -->
            <div id="gelen-messages" class="tab-content active">
                <div class="card">
                    <div class="card-body">
                        <?php if (count($gelen_mesajlar) > 0): ?>
                            <div class="message-list">
                                <?php foreach ($gelen_mesajlar as $m): ?>
                                    <div class="message-item <?php echo $m['okundu'] ? '' : 'unread'; ?>">
                                        <div class="message-header">
                                            <div class="message-sender">
                                                <?php if ($m['gonderen_resim']): ?>
                                                    <img src="uploads/<?php echo temizle($m['gonderen_resim']); ?>" 
                                                         alt="<?php echo temizle($m['gonderen_adi']); ?>" 
                                                         class="message-avatar">
                                                <?php else: ?>
                                                    <div class="message-avatar-placeholder">
                                                        <?php echo strtoupper(mb_substr($m['gonderen_adi'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <strong>
                                                    <a href="profile.php?id=<?php echo $m['gonderen_id']; ?>">
                                                        <?php echo temizle($m['gonderen_adi']); ?>
                                                    </a>
                                                </strong>
                                            </div>
                                            <span class="message-date"><?php echo goreciliZaman($m['tarih']); ?></span>
                                        </div>
                                        <div class="message-content">
                                            <?php 
                                            $mesaj_icerik = $m['sifreli'] ? mesajCoz($m['mesaj']) : $m['mesaj'];
                                            echo nl2br(temizle($mesaj_icerik)); 
                                            ?>
                                        </div>
                                        <?php if (!$m['okundu']): ?>
                                            <div class="message-actions">
                                                <a href="?mark_read=<?php echo $m['id']; ?>" class="btn btn-sm">
                                                    ✓ Okundu İşaretle
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="bilgi">Henüz gelen mesaj yok.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Giden Mesajlar -->
            <div id="giden-messages" class="tab-content">
                <div class="card">
                    <div class="card-body">
                        <?php if (count($giden_mesajlar) > 0): ?>
                            <div class="message-list">
                                <?php foreach ($giden_mesajlar as $m): ?>
                                    <div class="message-item">
                                        <div class="message-header">
                                            <div class="message-sender">
                                                <strong>Alıcı: 
                                                    <a href="profile.php?id=<?php echo $m['alici_id']; ?>">
                                                        <?php echo temizle($m['alici_adi']); ?>
                                                    </a>
                                                </strong>
                                            </div>
                                            <span class="message-date">
                                                <?php echo goreciliZaman($m['tarih']); ?>
                                                <?php if ($m['okundu']): ?>
                                                    <span class="read-badge">✓ Okundu</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="message-content">
                                            <?php 
                                            $mesaj_icerik = $m['sifreli'] ? mesajCoz($m['mesaj']) : $m['mesaj'];
                                            echo nl2br(temizle($mesaj_icerik)); 
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="bilgi">Henüz mesaj göndermediniz.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <style>
        .messages-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .message-tabs {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab-btn:hover {
            background: var(--light-bg);
        }
        
        .tab-btn.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .message-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message-item {
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .message-item.unread {
            background: #e3f2fd;
            border-color: var(--secondary-color);
        }
        
        .message-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .message-sender {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .message-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .message-sender a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .message-sender a:hover {
            color: var(--secondary-color);
        }
        
        .message-date {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .read-badge {
            color: var(--success-color);
            font-weight: bold;
            margin-left: 5px;
        }
        
        .message-content {
            line-height: 1.6;
            margin-top: 10px;
        }
        
        .message-actions {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
    </style>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-messages').classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>
</body>
</html>
