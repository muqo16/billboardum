<?php
require_once __DIR__ . '/includes/functions.php';

$citySlug = $_GET['city'] ?? '';
$ownerId = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 0;
$bidId = isset($_GET['bid_id']) ? (int)$_GET['bid_id'] : 0;

$targetUrl = null;
$cityId = null;
$targetOwnerId = null;

$db = get_db();

if ($bidId > 0) {
    $stmt = $db->prepare("SELECT b.city_id, b.owner_id, o.target_url FROM bids b JOIN owners o ON b.owner_id = o.id WHERE b.id = ?");
    $stmt->execute([$bidId]);
    $row = $stmt->fetch();
    if ($row && !empty($row['target_url'])) {
        $targetUrl = $row['target_url'];
        $cityId = (int)$row['city_id'];
        $targetOwnerId = (int)$row['owner_id'];
    }
} elseif ($ownerId > 0) {
    $stmt = $db->prepare("SELECT id, target_url FROM owners WHERE id = ?");
    $stmt->execute([$ownerId]);
    $row = $stmt->fetch();
    if ($row && !empty($row['target_url'])) {
        $targetUrl = $row['target_url'];
        $targetOwnerId = (int)$row['id'];
        if (!empty($citySlug)) {
            $c = get_city_by_slug($citySlug);
            if ($c) $cityId = (int)$c['id'];
        }
    }
} elseif (!empty($citySlug)) {
    $city = get_city_by_slug($citySlug);
    if ($city && !empty($city['target_url'])) {
        $targetUrl = $city['target_url'];
        $cityId = (int)$city['id'];
        $targetOwnerId = (int)$city['current_owner_id'];
    }
}

// Strict protocol and URL validation (Prevents Open Redirects, CRLF Injection, javascript: schemes)
if (!$targetUrl || !filter_var($targetUrl, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $targetUrl)) {
    header("Location: /");
    exit;
}

try {
    // 1. Increment city clicks if city is known
    if ($cityId) {
        $cStmt = $db->prepare("UPDATE cities SET total_clicks = total_clicks + 1 WHERE id = ?");
        $cStmt->execute([$cityId]);

        // Twin city sync for Istanbul
        if (!empty($citySlug) && in_array($citySlug, ['istanbul-avrupa', 'istanbul-asya'])) {
            $twin = ($citySlug === 'istanbul-avrupa') ? 'istanbul-asya' : 'istanbul-avrupa';
            $db->prepare("UPDATE cities SET total_clicks = total_clicks + 1 WHERE slug = ?")->execute([$twin]);
        }
    }

    // 2. Increment owner clicks
    if ($targetOwnerId) {
        $oStmt = $db->prepare("UPDATE owners SET total_clicks = total_clicks + 1 WHERE id = ?");
        $oStmt->execute([$targetOwnerId]);
    }

    // 3. Log click event
    $log = $db->prepare("INSERT INTO clicks (city_id, owner_id, ip_address, user_agent, referer) VALUES (?, ?, ?, ?, ?)");
    $log->execute([
        $cityId,
        $targetOwnerId,
        get_client_ip(),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255)
    ]);
} catch (Exception $e) {}

// Safe redirect
header("Location: " . $targetUrl);
exit;
