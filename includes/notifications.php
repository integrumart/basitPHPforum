<?php
/**
 * Bildirim Sistemi
 * Gelişmiş Forum Sistemi
 */

require_once __DIR__ . '/db.php';

/**
 * Bildirim oluştur
 */
function bildirimOlustur($kullanici_id, $tip, $baslik, $mesaj, $link = null, $gonderen_id = null, $db) {
    try {
        // Site içi bildirim oluştur
        $sorgu = $db->prepare("
            INSERT INTO notifications (kullanici_id, tip, baslik, mesaj, link, gonderen_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $sorgu->execute([$kullanici_id, $tip, $baslik, $mesaj, $link, $gonderen_id]);
        
        // E-posta bildirimi gönder (tercihler kontrol edilerek)
        if (emailBildirimiGonderilsinMi($kullanici_id, $tip, $db)) {
            emailBildirimiGonder($kullanici_id, $baslik, $mesaj, $link, $db);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Bildirim oluşturma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * E-posta bildirimi gönderilsin mi kontrol et
 */
function emailBildirimiGonderilsinMi($kullanici_id, $tip, $db) {
    $sorgu = $db->prepare("SELECT * FROM notification_prefs WHERE kullanici_id = ?");
    $sorgu->execute([$kullanici_id]);
    $tercih = $sorgu->fetch();
    
    if (!$tercih) {
        // Varsayılan tercihler: tüm bildirimler açık
        return true;
    }
    
    $tip_map = [
        'reply' => 'email_reply',
        'like' => 'email_like',
        'follow' => 'email_follow',
        'message' => 'email_message',
        'mention' => 'email_reply',
        'subscription' => 'email_reply'
    ];
    
    $alan = $tip_map[$tip] ?? 'email_reply';
    return isset($tercih[$alan]) && $tercih[$alan] == 1;
}

/**
 * E-posta bildirimi gönder
 */
function emailBildirimiGonder($kullanici_id, $baslik, $mesaj, $link = null, $db) {
    // Kullanıcı bilgilerini al
    $sorgu = $db->prepare("SELECT email, kullanici_adi FROM kullanicilar WHERE id = ?");
    $sorgu->execute([$kullanici_id]);
    $kullanici = $sorgu->fetch();
    
    if (!$kullanici) {
        return false;
    }
    
    // E-posta içeriği
    $email_baslik = "[" . SITE_NAME . "] " . $baslik;
    $email_mesaj = emailSablonuOlustur($kullanici['kullanici_adi'], $mesaj, $link);
    
    // E-posta gönder
    return emailGonder($kullanici['email'], $email_baslik, $email_mesaj, $db);
}

/**
 * E-posta şablonu oluştur
 */
function emailSablonuOlustur($kullanici_adi, $mesaj, $link = null) {
    $html = '
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .header {
                background-color: #2c3e50;
                color: #ffffff;
                padding: 20px;
                text-align: center;
            }
            .content {
                padding: 30px;
                color: #333333;
                line-height: 1.6;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background-color: #3498db;
                color: #ffffff;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
            }
            .footer {
                background-color: #ecf0f1;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                color: #7f8c8d;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin: 0;">' . SITE_NAME . '</h1>
            </div>
            <div class="content">
                <p>Merhaba <strong>' . temizle($kullanici_adi) . '</strong>,</p>
                <p>' . nl2br(temizle($mesaj)) . '</p>';
    
    if ($link) {
        $tam_link = SITE_URL . '/' . $link;
        $html .= '<a href="' . $tam_link . '" class="button">İçeriği Görüntüle</a>';
    }
    
    $html .= '
            </div>
            <div class="footer">
                <p>Bu e-posta ' . SITE_NAME . ' tarafından otomatik olarak gönderilmiştir.</p>
                <p>Bildirim tercihlerinizi profilinizden değiştirebilirsiniz.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * E-posta gönder (basit mail() fonksiyonu veya PHPMailer)
 */
function emailGonder($alici, $baslik, $mesaj, $db) {
    // SMTP ayarlarını al
    $smtp_host = adminAyarCek('smtp_host', $db);
    $smtp_port = adminAyarCek('smtp_port', $db, '587');
    $smtp_user = adminAyarCek('smtp_user', $db);
    $smtp_pass = adminAyarCek('smtp_pass', $db);
    $from_email = adminAyarCek('smtp_from_email', $db, 'noreply@forum.com');
    $from_name = adminAyarCek('smtp_from_name', $db, SITE_NAME);
    
    // SMTP ayarları yoksa basit mail() kullan
    if (empty($smtp_host) || empty($smtp_user)) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
        
        return mail($alici, $baslik, $mesaj, $headers);
    }
    
    // PHPMailer kullanımı (opsiyonel - kuruluysa)
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($alici);
            
            $mail->isHTML(true);
            $mail->Subject = $baslik;
            $mail->Body = $mesaj;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer hatası: " . $e->getMessage());
            return false;
        }
    }
    
    // Fallback: basit mail()
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
    
    return mail($alici, $baslik, $mesaj, $headers);
}

/**
 * Konu yanıtı bildirimi oluştur
 */
function konuYanitBildirimi($konu_id, $yanit_veren_id, $db) {
    // Konu sahibine bildir
    $sorgu = $db->prepare("
        SELECT k.kullanici_id, k.baslik, u.kullanici_adi as yanit_veren
        FROM konular k
        LEFT JOIN kullanicilar u ON u.id = ?
        WHERE k.id = ?
    ");
    $sorgu->execute([$yanit_veren_id, $konu_id]);
    $konu = $sorgu->fetch();
    
    if ($konu && $konu['kullanici_id'] != $yanit_veren_id) {
        $baslik = "Konunuza Yeni Yanıt";
        $mesaj = $konu['yanit_veren'] . " \"" . $konu['baslik'] . "\" konunuza yanıt verdi.";
        $link = "konu.php?id=" . $konu_id;
        
        bildirimOlustur($konu['kullanici_id'], 'reply', $baslik, $mesaj, $link, $yanit_veren_id, $db);
    }
    
    // Abonelere bildir
    $sorgu = $db->prepare("
        SELECT s.kullanici_id, u.kullanici_adi as yanit_veren, k.baslik
        FROM subscriptions s
        LEFT JOIN kullanicilar u ON u.id = ?
        LEFT JOIN konular k ON k.id = s.konu_id
        WHERE s.konu_id = ? AND s.kullanici_id != ? AND s.kullanici_id != ?
    ");
    $sorgu->execute([$yanit_veren_id, $konu_id, $yanit_veren_id, $konu['kullanici_id'] ?? 0]);
    
    while ($abone = $sorgu->fetch()) {
        $baslik = "Abone Olduğunuz Konuya Yeni Yanıt";
        $mesaj = $abone['yanit_veren'] . " abone olduğunuz \"" . $abone['baslik'] . "\" konusuna yanıt verdi.";
        $link = "konu.php?id=" . $konu_id;
        
        bildirimOlustur($abone['kullanici_id'], 'subscription', $baslik, $mesaj, $link, $yanit_veren_id, $db);
    }
}

/**
 * Beğeni bildirimi oluştur
 */
function begeniBildirimi($tip, $hedef_id, $begenen_id, $db) {
    if ($tip == 'mesaj') {
        $sorgu = $db->prepare("
            SELECT m.kullanici_id, u.kullanici_adi as begenen, k.baslik
            FROM mesajlar m
            LEFT JOIN kullanicilar u ON u.id = ?
            LEFT JOIN konular k ON k.id = m.konu_id
            WHERE m.id = ?
        ");
        $sorgu->execute([$begenen_id, $hedef_id]);
        $mesaj = $sorgu->fetch();
        
        if ($mesaj && $mesaj['kullanici_id'] != $begenen_id) {
            $baslik = "Mesajınız Beğenildi";
            $mesaj_text = $mesaj['begenen'] . " \"" . $mesaj['baslik'] . "\" konusundaki mesajınızı beğendi.";
            $link = "konu.php?id=" . $hedef_id;
            
            bildirimOlustur($mesaj['kullanici_id'], 'like', $baslik, $mesaj_text, $link, $begenen_id, $db);
        }
    }
}

/**
 * Takip bildirimi oluştur
 */
function takipBildirimi($takip_edilen_id, $takip_eden_id, $db) {
    $sorgu = $db->prepare("SELECT kullanici_adi FROM kullanicilar WHERE id = ?");
    $sorgu->execute([$takip_eden_id]);
    $takip_eden = $sorgu->fetch();
    
    if ($takip_eden) {
        $baslik = "Yeni Takipçi";
        $mesaj = $takip_eden['kullanici_adi'] . " sizi takip etmeye başladı.";
        $link = "profile.php?id=" . $takip_eden_id;
        
        bildirimOlustur($takip_edilen_id, 'follow', $baslik, $mesaj, $link, $takip_eden_id, $db);
    }
}

/**
 * Özel mesaj bildirimi oluştur
 */
function ozelMesajBildirimi($alici_id, $gonderen_id, $db) {
    $sorgu = $db->prepare("SELECT kullanici_adi FROM kullanicilar WHERE id = ?");
    $sorgu->execute([$gonderen_id]);
    $gonderen = $sorgu->fetch();
    
    if ($gonderen) {
        $baslik = "Yeni Özel Mesaj";
        $mesaj = $gonderen['kullanici_adi'] . " size özel bir mesaj gönderdi.";
        $link = "messages.php";
        
        bildirimOlustur($alici_id, 'message', $baslik, $mesaj, $link, $gonderen_id, $db);
    }
}

/**
 * Bildirimleri okundu olarak işaretle
 */
function bildirimleriOkunduIsaretle($kullanici_id, $db) {
    $sorgu = $db->prepare("UPDATE notifications SET okundu = 1 WHERE kullanici_id = ?");
    return $sorgu->execute([$kullanici_id]);
}

/**
 * Tek bir bildirimi okundu işaretle
 */
function bildirimOkunduIsaretle($bildirim_id, $kullanici_id, $db) {
    $sorgu = $db->prepare("UPDATE notifications SET okundu = 1 WHERE id = ? AND kullanici_id = ?");
    return $sorgu->execute([$bildirim_id, $kullanici_id]);
}

/**
 * Son bildirimleri getir
 */
function sonBildirimleriGetir($kullanici_id, $limit = 10, $db) {
    $sorgu = $db->prepare("
        SELECT n.*, u.kullanici_adi as gonderen_adi
        FROM notifications n
        LEFT JOIN kullanicilar u ON n.gonderen_id = u.id
        WHERE n.kullanici_id = ?
        ORDER BY n.tarih DESC
        LIMIT ?
    ");
    $sorgu->execute([$kullanici_id, $limit]);
    return $sorgu->fetchAll();
}
?>
