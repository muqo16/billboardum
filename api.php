<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_cities':
        echo json_encode(get_all_cities(), JSON_UNESCAPED_UNICODE);
        break;

    case 'get_city_details':
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            echo json_encode(['success' => false, 'error' => 'Şehir slug belirtilmedi.']);
            exit;
        }
        $data = get_city_details_full($slug);
        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'Şehir bulunamadı.']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'city' => $data['city'],
            'leader' => $data['leader'],
            'leader_total_cities' => $data['leader_total_cities'],
            'alt_ilanlar' => $data['alt_ilanlar']
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'get_stats':
        echo json_encode(get_site_stats(), JSON_UNESCAPED_UNICODE);
        break;

    case 'get_leaderboard':
        $filter = $_GET['filter'] ?? null;
        echo json_encode(get_leaderboard(50, $filter), JSON_UNESCAPED_UNICODE);
        break;

    case 'place_ad':
    case 'bid':
        // Optional CSRF check if token is sent
        if (!empty($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Güvenlik doğrulaması (CSRF) başarısız oldu.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $city = $_POST['city'] ?? '';
        $platform = $_POST['platform'] ?? 'site';
        $username = $_POST['username'] ?? '';
        $amount = (int)($_POST['amount'] ?? 0);
        $displayName = $_POST['display_name'] ?? null;
        $targetUrl = $_POST['target_url'] ?? null;
        
        // Handle Logo: Uploaded file > Provided URL > Standard Logo SVG
        $avatarUrl = null;
        if (!empty($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploaded = handle_logo_upload($_FILES['logo_file']);
            if ($uploaded) {
                $avatarUrl = $uploaded;
            }
        }
        if (empty($avatarUrl) && !empty($_POST['avatar_url'])) {
            $avatarUrl = trim($_POST['avatar_url']);
        }
        if (empty($avatarUrl) || $avatarUrl === '/assets/images/default_logo.svg') {
            $avatarUrl = get_default_logo_for_platform($platform);
        }

        $paymentMethod = $_POST['payment_method'] ?? 'iban';
        $paymentNote = trim($_POST['payment_note'] ?? '');
        $senderName = trim($_POST['sender_name'] ?? '');
        $senderPhone = trim($_POST['sender_phone'] ?? '');
        $receiptNo = trim($_POST['receipt_no'] ?? '');

        $receiptFile = null;
        if (!empty($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
            $receiptFile = handle_receipt_upload($_FILES['receipt_file']);
        }

        if (empty($paymentNote) && ($senderName || $senderPhone || $receiptNo)) {
            $parts = [];
            if ($senderName) $parts[] = "Gönderen: $senderName";
            if ($senderPhone) $parts[] = "Tel: $senderPhone";
            if ($receiptNo) $parts[] = "Dekont No: $receiptNo";
            $paymentNote = implode(' | ', $parts);
        }

        $userId = get_current_user_id();

        // Optional Social & Maps links
        $instagramUrl = trim($_POST['instagram_url'] ?? '') ?: null;
        $mapsUrl = trim($_POST['maps_url'] ?? '') ?: null;
        $youtubeUrl = trim($_POST['youtube_url'] ?? '') ?: null;
        $xUrl = trim($_POST['x_url'] ?? '') ?: null;
        $facebookUrl = trim($_POST['facebook_url'] ?? '') ?: null;

        $res = place_bid_advanced(
            $city, $platform, $username, $amount, 
            $displayName, $targetUrl, $avatarUrl, $userId, 
            $paymentMethod, $paymentNote,
            $instagramUrl, $mapsUrl, $youtubeUrl, $xUrl, $facebookUrl,
            $senderName ?: null, $senderPhone ?: null, $receiptNo ?: null, $receiptFile
        );
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        break;

    case 'update_socials':
        if (!empty($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Güvenlik doğrulaması (CSRF) başarısız oldu.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $userId = get_current_user_id();
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Giriş yapmanız gerekiyor.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $ownerId = (int)($_POST['owner_id'] ?? 0);
        $db = get_db();
        $chk = $db->prepare("SELECT id FROM owners WHERE id = ? AND user_id = ?");
        $chk->execute([$ownerId, $userId]);
        if (!$chk->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Bu profil üzerinde işlem yetkiniz yok.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $ok = update_owner_links($ownerId, [
            'instagram_url' => $_POST['instagram_url'] ?? '',
            'maps_url' => $_POST['maps_url'] ?? '',
            'youtube_url' => $_POST['youtube_url'] ?? '',
            'x_url' => $_POST['x_url'] ?? '',
            'facebook_url' => $_POST['facebook_url'] ?? ''
        ]);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Sosyal medya ve harita linkleriniz güncellendi!' : 'Güncelleme başarısız.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => 'Geçersiz API eylemi.']);
        break;
}
