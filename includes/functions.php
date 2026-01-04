<?php
/**
 * Yardımcı Fonksiyonlar
 * Gelişmiş Forum Sistemi
 */

/**
 * XSS koruması için veriyi temizle
 */
function temizle($veri) {
    return htmlspecialchars($veri, ENT_QUOTES, 'UTF-8');
}

/**
 * Kullanıcı giriş kontrolü
 */
function girisKontrol() {
    return isset($_SESSION['kullanici_id']);
}

/**
 * Admin kontrolü
 */
function adminKontrol() {
    return girisKontrol() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Sayfa yönlendirme
 */
function yonlendir($sayfa) {
    header("Location: $sayfa");
    exit();
}

/**
 * CSRF Token oluştur
 */
function csrfTokenOlustur() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token doğrula
 */
function csrfTokenDogrula($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Tarih formatla (Türkçe)
 */
function tarihFormatla($tarih, $format = 'd.m.Y H:i') {
    return date($format, strtotime($tarih));
}

/**
 * Göreceli zaman (2 saat önce, 3 gün önce, vb.)
 */
function goreciliZaman($tarih) {
    $zaman = strtotime($tarih);
    $fark = time() - $zaman;
    
    if ($fark < 60) {
        return 'Az önce';
    } elseif ($fark < 3600) {
        $dakika = floor($fark / 60);
        return $dakika . ' dakika önce';
    } elseif ($fark < 86400) {
        $saat = floor($fark / 3600);
        return $saat . ' saat önce';
    } elseif ($fark < 604800) {
        $gun = floor($fark / 86400);
        return $gun . ' gün önce';
    } elseif ($fark < 2592000) {
        $hafta = floor($fark / 604800);
        return $hafta . ' hafta önce';
    } elseif ($fark < 31536000) {
        $ay = floor($fark / 2592000);
        return $ay . ' ay önce';
    } else {
        $yil = floor($fark / 31536000);
        return $yil . ' yıl önce';
    }
}

/**
 * E-posta doğrulama
 */
function emailDogrula($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Dosya yükleme
 */
function dosyaYukle($file, $dizin = 'uploads', $izinli_uzantilar = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Dosya yükleme hatası'];
    }
    
    $dosya_adi = $file['name'];
    $dosya_boyutu = $file['size'];
    $dosya_tmp = $file['tmp_name'];
    $dosya_uzantisi = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
    
    if (!in_array($dosya_uzantisi, $izinli_uzantilar)) {
        return ['success' => false, 'message' => 'İzin verilmeyen dosya türü'];
    }
    
    if ($dosya_boyutu > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'Dosya boyutu çok büyük (Max: 5MB)'];
    }
    
    $yeni_dosya_adi = uniqid() . '_' . time() . '.' . $dosya_uzantisi;
    $hedef = __DIR__ . '/../' . $dizin . '/' . $yeni_dosya_adi;
    
    if (move_uploaded_file($dosya_tmp, $hedef)) {
        return ['success' => true, 'filename' => $yeni_dosya_adi];
    } else {
        return ['success' => false, 'message' => 'Dosya taşıma hatası'];
    }
}

/**
 * Kullanıcı istatistikleri
 */
function kullaniciIstatistikleri($kullanici_id, $db) {
    $stats = [];
    
    // Toplam konu sayısı
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM konular WHERE kullanici_id = ?");
    $sorgu->execute([$kullanici_id]);
    $stats['konu_sayisi'] = $sorgu->fetch()['sayi'];
    
    // Toplam mesaj sayısı
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM mesajlar WHERE kullanici_id = ?");
    $sorgu->execute([$kullanici_id]);
    $stats['mesaj_sayisi'] = $sorgu->fetch()['sayi'];
    
    // Alınan beğeni sayısı
    $sorgu = $db->prepare("
        SELECT COUNT(*) as sayi FROM likes l
        INNER JOIN mesajlar m ON l.hedef_id = m.id AND l.tip = 'mesaj'
        WHERE m.kullanici_id = ? AND l.deger = 1
    ");
    $sorgu->execute([$kullanici_id]);
    $stats['begeni_sayisi'] = $sorgu->fetch()['sayi'];
    
    // Takipçi sayısı
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM followers WHERE takip_edilen_id = ?");
    $sorgu->execute([$kullanici_id]);
    $stats['takipci_sayisi'] = $sorgu->fetch()['sayi'];
    
    // Takip ettikleri sayısı
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM followers WHERE takip_eden_id = ?");
    $sorgu->execute([$kullanici_id]);
    $stats['takip_sayisi'] = $sorgu->fetch()['sayi'];
    
    return $stats;
}

/**
 * Bildirim sayısı
 */
function okunmamisBildirimSayisi($kullanici_id, $db) {
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM notifications WHERE kullanici_id = ? AND okundu = 0");
    $sorgu->execute([$kullanici_id]);
    return $sorgu->fetch()['sayi'];
}

/**
 * Okunmamış mesaj sayısı
 */
function okunmamisMesajSayisi($kullanici_id, $db) {
    $sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM messages WHERE alici_id = ? AND okundu = 0");
    $sorgu->execute([$kullanici_id]);
    return $sorgu->fetch()['sayi'];
}

/**
 * Brute force koruması - IP kontrolü
 */
function ipEngelliMi($ip, $db) {
    $limit = 5; // Maksimum deneme
    $sure = 15; // Dakika
    
    $sorgu = $db->prepare("
        SELECT COUNT(*) as deneme 
        FROM login_attempts 
        WHERE ip_adresi = ? 
        AND basarili = 0 
        AND tarih > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $sorgu->execute([$ip, $sure]);
    $sonuc = $sorgu->fetch();
    
    return $sonuc['deneme'] >= $limit;
}

/**
 * Giriş denemesi kaydet
 */
function girisDenemesiKaydet($ip, $kullanici_adi, $basarili, $db) {
    $sorgu = $db->prepare("INSERT INTO login_attempts (ip_adresi, kullanici_adi, basarili) VALUES (?, ?, ?)");
    $sorgu->execute([$ip, $kullanici_adi, $basarili]);
}

/**
 * Metin kısalt
 */
function metinKisalt($metin, $uzunluk = 100) {
    if (mb_strlen($metin) > $uzunluk) {
        return mb_substr($metin, 0, $uzunluk) . '...';
    }
    return $metin;
}

/**
 * Sayfalama
 */
function sayfalama($toplam, $sayfa_basina, $mevcut_sayfa, $url) {
    $toplam_sayfa = ceil($toplam / $sayfa_basina);
    
    if ($toplam_sayfa <= 1) {
        return '';
    }
    
    $html = '<div class="sayfalama" role="navigation" aria-label="Sayfalama">';
    
    // Önceki sayfa
    if ($mevcut_sayfa > 1) {
        $html .= '<a href="' . $url . ($mevcut_sayfa - 1) . '" aria-label="Önceki sayfa">&laquo; Önceki</a>';
    }
    
    // Sayfa numaraları
    for ($i = 1; $i <= $toplam_sayfa; $i++) {
        if ($i == $mevcut_sayfa) {
            $html .= '<span class="aktif" aria-current="page">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . $i . '" aria-label="Sayfa ' . $i . '">' . $i . '</a>';
        }
    }
    
    // Sonraki sayfa
    if ($mevcut_sayfa < $toplam_sayfa) {
        $html .= '<a href="' . $url . ($mevcut_sayfa + 1) . '" aria-label="Sonraki sayfa">Sonraki &raquo;</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Admin ayarlarını çek
 */
function adminAyarCek($anahtar, $db, $varsayilan = '') {
    $sorgu = $db->prepare("SELECT deger FROM admin_settings WHERE anahtar = ?");
    $sorgu->execute([$anahtar]);
    $sonuc = $sorgu->fetch();
    return $sonuc ? $sonuc['deger'] : $varsayilan;
}

/**
 * Admin ayarını güncelle
 */
function adminAyarGuncelle($anahtar, $deger, $db) {
    $sorgu = $db->prepare("
        INSERT INTO admin_settings (anahtar, deger) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE deger = ?
    ");
    return $sorgu->execute([$anahtar, $deger, $deger]);
}

/**
 * IP adresini al
 */
function ipAdresiniAl() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Basit metin şifreleme (özel mesajlar için)
 */
function mesajSifrele($mesaj, $anahtar = 'forum_secret_key_2024') {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $sifreli = openssl_encrypt($mesaj, 'aes-256-cbc', $anahtar, 0, $iv);
    return base64_encode($sifreli . '::' . $iv);
}

/**
 * Şifreli mesajı çöz
 */
function mesajCoz($sifreli_mesaj, $anahtar = 'forum_secret_key_2024') {
    $parcalar = explode('::', base64_decode($sifreli_mesaj), 2);
    if (count($parcalar) < 2) {
        return $sifreli_mesaj;
    }
    list($sifreli_veri, $iv) = $parcalar;
    return openssl_decrypt($sifreli_veri, 'aes-256-cbc', $anahtar, 0, $iv);
}
?>
