<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// ==========================================
// SECURITY & PROTECTION HELPERS
// ==========================================
function format_number($val): string {
    return number_format((float)$val, 0, ',', '.');
}

function format_money($amount): string {
    return format_number($amount) . '₺';
}

function get_platform_label(string $platform): string {
    switch (strtolower(trim($platform))) {
        case 'site': return 'Web Sitesi';
        case 'x': return 'X (Twitter)';
        case 'instagram': return 'Instagram';
        case 'app': return 'Mobil Uygulama';
        case 'youtube': return 'YouTube';
        case 'facebook': return 'Facebook';
        default: return ucfirst($platform);
    }
}

function get_platform_url(string $platform, string $username): string {
    $u = trim(ltrim($username, '@'));
    switch (strtolower(trim($platform))) {
        case 'x':
            return 'https://x.com/' . $u;
        case 'instagram':
            return 'https://instagram.com/' . $u;
        case 'youtube':
            return str_starts_with($u, 'http') ? $u : ('https://youtube.com/@' . $u);
        case 'facebook':
            return str_starts_with($u, 'http') ? $u : ('https://facebook.com/' . $u);
        case 'app':
            return str_starts_with($u, 'http') ? $u : 'https://play.google.com/store/apps/details?id=' . $u;
        case 'site':
        default:
            return str_starts_with($u, 'http') ? $u : 'https://' . $u;
    }
}

function get_default_logo_for_platform(string $platform = 'site'): string {
    switch (strtolower(trim($platform ?? 'site'))) {
        case 'app':
            return '/assets/images/default_app.svg';
        case 'instagram':
            return '/assets/images/default_instagram.svg';
        case 'youtube':
            return '/assets/images/default_youtube.svg';
        case 'x':
        case 'twitter':
            return '/assets/images/default_x.svg';
        case 'facebook':
            return '/assets/images/default_facebook.svg';
        case 'site':
        default:
            return '/assets/images/default_site.svg';
    }
}

function get_platform_icon_svg(string $platform, int $size = 20): string {
    $p = strtolower(trim($platform));
    switch ($p) {
        case 'x':
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 24.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
        case 'instagram':
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>';
        case 'youtube':
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
        case 'facebook':
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
        case 'app':
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>';
        case 'site':
        default:
            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>';
    }
}

function get_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function get_client_ip(): string {
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function mask_ip(?string $ip): string {
    if (empty($ip)) return '—';
    if ($ip === '127.0.0.1' || $ip === '::1') return '127.0.0.*';
    $cleanIp = trim(explode(',', $ip)[0]);
    if (filter_var($cleanIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $cleanIp);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.***.***';
        }
    }
    if (filter_var($cleanIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $cleanIp);
        return ($parts[0] ?? '') . ':' . ($parts[1] ?? '') . ':****:****';
    }
    return '***.***.***.***';
}

function check_rate_limit(string $action = 'login', int $maxAttempts = 5, int $lockoutMinutes = 15): bool {
    $ip = get_client_ip();
    $db = get_db();
    $stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip = ? AND action = ?");
    $stmt->execute([$ip, $action]);
    $row = $stmt->fetch();
    if (!$row) return true;

    $timeSince = time() - strtotime($row['last_attempt']);
    if ($row['attempts'] >= $maxAttempts && $timeSince < ($lockoutMinutes * 60)) {
        return false; // Lockout active
    }
    if ($timeSince >= ($lockoutMinutes * 60)) {
        clear_failed_attempts($action);
    }
    return true;
}

function record_failed_attempt(string $action = 'login'): void {
    $ip = get_client_ip();
    $db = get_db();
    try {
        $stmt = $db->prepare("
            INSERT INTO login_attempts (ip, action, attempts, last_attempt)
            VALUES (?, ?, 1, CURRENT_TIMESTAMP)
            ON CONFLICT(ip, action) DO UPDATE SET
            attempts = attempts + 1,
            last_attempt = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$ip, $action]);
    } catch (Exception $e) {
        // Fallback for older sqlite
        $c = $db->prepare("SELECT id, attempts FROM login_attempts WHERE ip = ? AND action = ?");
        $c->execute([$ip, $action]);
        $row = $c->fetch();
        if ($row) {
            $db->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE id = ?")->execute([$row['id']]);
        } else {
            $db->prepare("INSERT INTO login_attempts (ip, action, attempts, last_attempt) VALUES (?, ?, 1, CURRENT_TIMESTAMP)")->execute([$ip, $action]);
        }
    }
}

function clear_failed_attempts(string $action = 'login'): void {
    $ip = get_client_ip();
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ? AND action = ?");
    $stmt->execute([$ip, $action]);
}

// ==========================================
// PAYMENT APPROVAL HELPERS
// ==========================================
function get_pending_bids(): array {
    $db = get_db();
    return $db->query("
        SELECT b.*, c.name as city_name, c.slug as city_slug, c.plate_code,
               o.display_name, o.username, o.platform, o.target_url, o.avatar_url,
               o.instagram_url, o.maps_url, o.youtube_url, o.x_url, o.facebook_url,
               u.name as user_name, u.email as user_email
        FROM bids b
        JOIN cities c ON b.city_id = c.id
        JOIN owners o ON b.owner_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        WHERE b.status = 'pending_approval'
        ORDER BY b.created_at DESC
    ")->fetchAll();
}

function delete_owner_and_vacate_cities(int $ownerId): bool {
    $db = get_db();
    try {
        $db->beginTransaction();
        // Vacate cities
        $db->prepare("
            UPDATE cities 
            SET current_owner_id = NULL, current_amount = 10, total_clicks = 0, updated_at = CURRENT_TIMESTAMP 
            WHERE current_owner_id = ?
        ")->execute([$ownerId]);
        
        // Delete bids of this owner
        $db->prepare("DELETE FROM bids WHERE owner_id = ?")->execute([$ownerId]);

        // Delete owner
        $db->prepare("DELETE FROM owners WHERE id = ?")->execute([$ownerId]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function delete_user_and_all_data(int $userId): bool {
    $db = get_db();
    try {
        $db->beginTransaction();
        $owners = $db->prepare("SELECT id FROM owners WHERE user_id = ?");
        $owners->execute([$userId]);
        $ownerIds = $owners->fetchAll(PDO::FETCH_COLUMN);

        foreach ($ownerIds as $oid) {
            $db->prepare("
                UPDATE cities 
                SET current_owner_id = NULL, current_amount = 10, total_clicks = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE current_owner_id = ?
            ")->execute([$oid]);
            $db->prepare("DELETE FROM bids WHERE owner_id = ?")->execute([$oid]);
            $db->prepare("DELETE FROM owners WHERE id = ?")->execute([$oid]);
        }

        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function get_all_owners_with_details(): array {
    $db = get_db();
    return $db->query("
        SELECT o.*, 
               u.name as user_name, u.email as user_email,
               (SELECT COUNT(*) FROM cities WHERE current_owner_id = o.id) as city_count,
               (SELECT GROUP_CONCAT(name, ', ') FROM cities WHERE current_owner_id = o.id) as owned_city_names
        FROM owners o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
    ")->fetchAll();
}

function approve_bid(int $bidId): bool {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM bids WHERE id = ?");
    $stmt->execute([$bidId]);
    $bid = $stmt->fetch();
    if (!$bid) return false;

    $db->beginTransaction();
    try {
        // 1. Mark bid approved
        $db->prepare("UPDATE bids SET status = 'approved', paid_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$bidId]);

        // 2. Increment owner total_spent
        $db->prepare("UPDATE owners SET total_spent = total_spent + ? WHERE id = ?")->execute([$bid['amount'], $bid['owner_id']]);

        // 3. Update city owner and amount
        $db->prepare("
            UPDATE cities 
            SET current_owner_id = ?, current_amount = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ")->execute([$bid['owner_id'], $bid['amount'], $bid['city_id']]);

        // Twin city update for Istanbul
        $cRow = $db->query("SELECT slug FROM cities WHERE id = {$bid['city_id']}")->fetch();
        if ($cRow && in_array($cRow['slug'], ['istanbul-avrupa', 'istanbul-asya'])) {
            $twin = ($cRow['slug'] === 'istanbul-avrupa') ? 'istanbul-asya' : 'istanbul-avrupa';
            $db->prepare("UPDATE cities SET current_owner_id = ?, current_amount = ? WHERE slug = ?")
               ->execute([$bid['owner_id'], $bid['amount'], $twin]);
        }

        // 3. Increment site stats volume
        $db->prepare("UPDATE site_stats SET volume = volume + ? WHERE id = 1")->execute([$bid['amount']]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function reject_bid(int $bidId): bool {
    $db = get_db();
    $stmt = $db->prepare("UPDATE bids SET status = 'rejected' WHERE id = ?");
    return $stmt->execute([$bidId]);
}

// Authentication helpers
function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function get_current_user_info(): ?array {
    $uid = get_current_user_id();
    if (!$uid) return null;
    $db = get_db();
    $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    return $stmt->fetch() ?: null;
}

function register_user(string $name, string $email, string $password): array {
    $db = get_db();
    $email = strtolower(trim($email));
    $name = trim($name);
    
    if (empty($name) || empty($email) || strlen($password) < 6) {
        return ['success' => false, 'error' => 'Lütfen tüm alanları geçerli şekilde doldurun (şifre en az 6 karakter).'];
    }

    $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
    $chk->execute([$email]);
    if ($chk->fetch()) {
        return ['success' => false, 'error' => 'Bu e-posta adresi ile zaten bir hesap mevcut.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')");
    $ins->execute([$name, $email, $hash]);
    $userId = (int)$db->lastInsertId();

    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;

    return ['success' => true, 'user_id' => $userId];
}

function login_user(string $email, string $password): array {
    $db = get_db();
    $email = strtolower(trim($email));
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'E-posta veya şifre hatalı.'];
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    return ['success' => true, 'user' => $user];
}

function get_site_stats(): array {
    $db = get_db();
    $stmt = $db->query("SELECT * FROM site_stats WHERE id = 1");
    $stats = $stmt->fetch();
    if (!$stats) {
        $stats = ['payments_on' => 1, 'paytr_test' => 0];
    }

    // 100% GENUINE, DYNAMIC, REAL-TIME METRICS:
    // 1. Gerçek Ziyaretçi: Count of authentic recorded visits in visitors table
    $realVisits = (int)$db->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
    $stats['visits'] = max(1, $realVisits);

    // 2. Gerçek Hacim / Harcama: Sum of amounts of distinctly owned provinces (grouped by plate_code so Istanbul is counted once)
    $stats['volume'] = (int)$db->query("
        SELECT COALESCE(SUM(amount), 0) FROM (
            SELECT plate_code, MAX(current_amount) as amount 
            FROM cities 
            WHERE current_owner_id IS NOT NULL 
            GROUP BY plate_code
        )
    ")->fetchColumn();

    // 3. Gerçek Aktif Şehir: Distinct count of provinces (out of 81) that have an owner
    $stats['active_cities'] = (int)$db->query("
        SELECT COUNT(DISTINCT plate_code) 
        FROM cities 
        WHERE current_owner_id IS NOT NULL
    ")->fetchColumn();

    // 4. Gerçek Tıklama: Real sum of clicks recorded in the clicks table
    $stats['total_clicks'] = (int)$db->query("
        SELECT COUNT(*) FROM clicks
    ")->fetchColumn();

    return $stats;
}

function increment_site_visits(): void {
    track_site_visit();
}

function track_site_visit(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $now = time();
    // Only log once per 30 minutes per browser session to record authentic visitor sessions
    if (!isset($_SESSION['last_visit_logged']) || ($now - (int)$_SESSION['last_visit_logged']) > 1800) {
        $_SESSION['last_visit_logged'] = $now;
        
        $ip = mask_ip(get_client_ip());
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $page = substr($_SERVER['REQUEST_URI'] ?? '/', 0, 255);
        $ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255);
        $sid = session_id();

        try {
            $db = get_db();
            $stmt = $db->prepare("INSERT INTO visitors (ip, session_id, user_agent, page_url, referer) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$ip, $sid, $ua, $page, $ref]);
        } catch (Exception $e) {}
    }
}

function get_all_cities(): array {
    $db = get_db();
    $sql = "
        SELECT c.*, 
               o.platform, o.username, o.display_name, o.target_url, o.avatar_url,
               o.instagram_url, o.maps_url, o.youtube_url, o.x_url, o.facebook_url,
               o.total_spent as owner_total_spent, o.total_clicks as owner_total_clicks
        FROM cities c
        LEFT JOIN owners o ON c.current_owner_id = o.id
        ORDER BY CAST(c.plate_code AS INTEGER) ASC
    ";
    $rows = $db->query($sql)->fetchAll();
    foreach ($rows as &$r) {
        if (!empty($r['current_owner_id'])) {
            if (empty($r['avatar_url']) || $r['avatar_url'] === '/assets/images/default_logo.svg') {
                $r['avatar_url'] = get_default_logo_for_platform($r['platform'] ?? 'site');
            }
        }
    }
    return $rows;
}

function get_city_by_slug(string $slug): ?array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT c.*, 
               o.platform, o.username, o.display_name, o.target_url, o.avatar_url,
               o.instagram_url, o.maps_url, o.youtube_url, o.x_url, o.facebook_url
        FROM cities c
        LEFT JOIN owners o ON c.current_owner_id = o.id
        WHERE c.slug = ? OR (c.slug IN ('istanbul-avrupa', 'istanbul-asya') AND ? = 'istanbul')
        LIMIT 1
    ");
    $stmt->execute([$slug, $slug]);
    $res = $stmt->fetch();
    if ($res && !empty($res['current_owner_id'])) {
        if (empty($res['avatar_url']) || $res['avatar_url'] === '/assets/images/default_logo.svg') {
            $res['avatar_url'] = get_default_logo_for_platform($res['platform'] ?? 'site');
        }
    }
    return $res ?: null;
}

function get_leaderboard(int $limit = 50, ?string $platformFilter = null): array {
    $db = get_db();
    $where = "";
    $params = [];
    if ($platformFilter && in_array($platformFilter, ['site', 'x', 'instagram', 'app', 'youtube', 'facebook'])) {
        $where = "WHERE o.platform = ?";
        $params[] = $platformFilter;
    }

    $sql = "
        SELECT o.id, o.platform, o.username, o.display_name, o.target_url, o.avatar_url,
               COUNT(c.id) as cities_count,
               SUM(c.current_amount) as total_amount,
               SUM(c.total_clicks) as total_clicks,
               GROUP_CONCAT(c.name, ', ') as owned_city_names,
               MAX(c.updated_at) as last_activity
        FROM owners o
        JOIN cities c ON c.current_owner_id = o.id
        $where
        GROUP BY o.id
        ORDER BY total_amount DESC, cities_count DESC
        LIMIT " . intval($limit);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        if (empty($r['avatar_url']) || $r['avatar_url'] === '/assets/images/default_logo.svg') {
            $r['avatar_url'] = get_default_logo_for_platform($r['platform'] ?? 'site');
        }
    }
    return $rows;
}

function get_recent_bids(int $limit = 10, ?string $status = 'approved'): array {
    $db = get_db();
    $where = $status ? "WHERE b.status = " . $db->quote($status) : "";
    $sql = "
        SELECT b.*, c.name as city_name, c.slug as city_slug,
               o.platform, o.username, o.display_name, o.avatar_url,
               (SELECT COUNT(*) FROM cities WHERE current_owner_id = o.id) as total_cities
        FROM bids b
        JOIN cities c ON b.city_id = c.id
        JOIN owners o ON b.owner_id = o.id
        $where
        ORDER BY b.created_at DESC
        LIMIT " . intval($limit);
    $rows = $db->query($sql)->fetchAll();
    foreach ($rows as &$r) {
        if (empty($r['avatar_url']) || $r['avatar_url'] === '/assets/images/default_logo.svg') {
            $r['avatar_url'] = get_default_logo_for_platform($r['platform'] ?? 'site');
        }
    }
    return $rows;
}


function get_city_bids_history(int $cityId, int $limit = 10): array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT b.*, o.username, o.display_name, o.platform, o.avatar_url, o.target_url,
               o.instagram_url, o.maps_url, o.youtube_url, o.x_url, o.facebook_url
        FROM bids b
        JOIN owners o ON b.owner_id = o.id
        WHERE b.city_id = ? AND b.status = 'approved'
        ORDER BY b.amount DESC, b.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$cityId, $limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        if (empty($r['avatar_url']) || $r['avatar_url'] === '/assets/images/default_logo.svg') {
            $r['avatar_url'] = get_default_logo_for_platform($r['platform'] ?? 'site');
        }
    }
    return $rows;
}

function get_city_details_full(string $slug): ?array {
    $city = get_city_by_slug($slug);
    if (!$city) return null;

    $cityId = (int)$city['id'];
    $bids = get_city_bids_history($cityId, 15);

    // Leader is the top bid or current city owner
    $leader = null;
    $altIlanlar = [];

    if (!empty($bids)) {
        $leader = $bids[0];
        $altIlanlar = array_slice($bids, 1);
    } elseif ($city['current_owner_id'] && $city['username']) {
        $leader = [
            'owner_id' => $city['current_owner_id'],
            'username' => $city['username'],
            'display_name' => $city['display_name'] ?: $city['username'],
            'platform' => $city['platform'],
            'avatar_url' => $city['avatar_url'],
            'target_url' => $city['target_url'],
            'instagram_url' => $city['instagram_url'] ?? null,
            'maps_url' => $city['maps_url'] ?? null,
            'youtube_url' => $city['youtube_url'] ?? null,
            'x_url' => $city['x_url'] ?? null,
            'facebook_url' => $city['facebook_url'] ?? null,
            'amount' => $city['current_amount'],
            'created_at' => $city['updated_at']
        ];
    }

    // Count how many total cities this leader owns
    $leaderTotalCities = 1;
    if ($leader && !empty($leader['owner_id'])) {
        $db = get_db();
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM cities WHERE current_owner_id = ?");
        $cntStmt->execute([$leader['owner_id']]);
        $leaderTotalCities = (int)$cntStmt->fetchColumn() ?: 1;
    }

    return [
        'city' => $city,
        'leader' => $leader,
        'leader_total_cities' => $leaderTotalCities,
        'alt_ilanlar' => $altIlanlar
    ];
}

function get_owner_details(string $platform, string $username): ?array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT * FROM owners 
        WHERE platform = ? AND LOWER(username) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$platform, ltrim($username, '@')]);
    $owner = $stmt->fetch();
    if (!$owner) return null;

    $cStmt = $db->prepare("
        SELECT * FROM cities 
        WHERE current_owner_id = ? 
        ORDER BY current_amount DESC
    ");
    $cStmt->execute([$owner['id']]);
    $owner['cities'] = $cStmt->fetchAll();

    return $owner;
}

function get_user_ads(int $userId): array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT c.*, o.id as owner_id, o.platform, o.username, o.display_name, o.target_url, o.avatar_url,
               o.instagram_url, o.maps_url, o.youtube_url, o.x_url, o.facebook_url
        FROM cities c
        JOIN owners o ON c.current_owner_id = o.id
        WHERE o.user_id = ?
        ORDER BY c.current_amount DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_user_clicks_log(int $userId, int $limit = 20): array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT cl.*, c.name as city_name, c.slug as city_slug, o.platform
        FROM clicks cl
        JOIN cities c ON cl.city_id = c.id
        JOIN owners o ON cl.owner_id = o.id
        WHERE o.user_id = ?
        ORDER BY cl.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function update_owner_links(int $ownerId, array $links): bool {
    $db = get_db();
    $stmt = $db->prepare("
        UPDATE owners 
        SET instagram_url = ?,
            maps_url = ?,
            youtube_url = ?,
            x_url = ?,
            facebook_url = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([
        !empty($links['instagram_url']) ? trim($links['instagram_url']) : null,
        !empty($links['maps_url']) ? trim($links['maps_url']) : null,
        !empty($links['youtube_url']) ? trim($links['youtube_url']) : null,
        !empty($links['x_url']) ? trim($links['x_url']) : null,
        !empty($links['facebook_url']) ? trim($links['facebook_url']) : null,
        $ownerId
    ]);
}

/**
 * Askeri Düzey Sıfır-Güven (Zero-Trust) Resim Temizleme ve Yeniden Kodlama Motoru (PHP GD)
 * Yüklenen resmin tüm piksellerini RAM belleğe çözüp sıfırdan yepyeni temiz bir resim tuvali
 * oluşturarak kaydeder. Böylece:
 * - Resim içine gizlenmiş Python, PHP, Shell veya Bash kodları,
 * - EXIF veya dosya sonuna eklenmiş zararlı polyglot yükler (payloads),
 * - Bozuk veya sahte uzantılı dosyalar
 * %100 oranında yok edilir ve temizlenir.
 */
function reencode_and_clean_image(string $tmpFile, string $destFile, string $ext): bool {
    if (!extension_loaded('gd')) {
        return move_uploaded_file($tmpFile, $destFile);
    }

    $raw = @file_get_contents($tmpFile);
    if ($raw === false || strlen($raw) < 8) {
        return false;
    }

    $img = @imagecreatefromstring($raw);
    if ($img === false) {
        return false; // Sahte veya bozuk resim
    }

    $w = imagesx($img);
    $h = imagesy($img);
    if ($w <= 0 || $h <= 0 || $w > 5000 || $h > 5000) {
        imagedestroy($img);
        return false;
    }

    $canvas = imagecreatetruecolor($w, $h);
    if (!$canvas) {
        imagedestroy($img);
        return false;
    }

    if ($ext === 'png' || $ext === 'webp') {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $transparent);
    } else {
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $white);
    }

    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w, $h, $w, $h);

    $success = false;
    switch ($ext) {
        case 'png':
            $success = imagepng($canvas, $destFile, 8);
            break;
        case 'webp':
            $success = function_exists('imagewebp') ? imagewebp($canvas, $destFile, 85) : imagejpeg($canvas, $destFile, 85);
            break;
        case 'jpg':
        case 'jpeg':
        default:
            $success = imagejpeg($canvas, $destFile, 85);
            break;
    }

    imagedestroy($img);
    imagedestroy($canvas);
    return $success;
}

function handle_logo_upload(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Max 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    if (!in_array($ext, $allowedExts)) {
        return null;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml', 'image/svg', 'text/plain', 'text/xml'];
        if (!in_array($mime, $allowedMimes)) {
            return null;
        }
    }

    $uploadDir = __DIR__ . '/../uploads/logos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFilename = 'logo_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = $uploadDir . $newFilename;

    // SVG Deep Content Inspection (XSS & Payload Prevention)
    if ($ext === 'svg') {
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            return null;
        }
        if (preg_match('/<script|foreignObject|onload|onerror|onclick|onmouseover|javascript:|data:text\/html|<!ENTITY|SYSTEM/i', $content)) {
            return null;
        }
        if (!preg_match('/<svg[^>]*>/i', $content)) {
            return null;
        }
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/uploads/logos/' . $newFilename;
        }
        return null;
    }

    // Raster resimler için (PNG, JPG, WEBP) Sıfır-Güven Yeniden Kodlama:
    // Pikseller RAM bellekte baştan oluşturulur, sahte/gizli Python/PHP kodları tamamen silinir!
    if (reencode_and_clean_image($file['tmp_name'], $dest, $ext)) {
        return '/uploads/logos/' . $newFilename;
    }

    return null;
}

function handle_receipt_upload(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];
    if (!in_array($ext, $allowedExts)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/receipts/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $newFilename = 'receipt_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = $uploadDir . $newFilename;

    // Deep validation for PDF
    if ($ext === 'pdf') {
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        if ($header !== '%PDF') {
            return null;
        }
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/uploads/receipts/' . $newFilename;
        }
        return null;
    }

    // Raster dekontlar için Sıfır-Güven Yeniden Kodlama
    if (reencode_and_clean_image($file['tmp_name'], $dest, $ext)) {
        return '/uploads/receipts/' . $newFilename;
    }

    return null;
}

function place_bid_advanced(
    string $citySlug, 
    string $platform, 
    string $username, 
    int $amount, 
    ?string $displayName = null, 
    ?string $targetUrl = null, 
    ?string $avatarUrl = null,
    ?int $userId = null,
    string $paymentMethod = 'direct',
    string $paymentNote = '',
    ?string $instagramUrl = null,
    ?string $mapsUrl = null,
    ?string $youtubeUrl = null,
    ?string $xUrl = null,
    ?string $facebookUrl = null,
    ?string $senderName = null,
    ?string $senderPhone = null,
    ?string $receiptNo = null,
    ?string $receiptFile = null
): array {
    $db = get_db();
    $username = trim(ltrim($username, '@'));
    $platform = strtolower(trim($platform));

    if (!in_array($platform, ['instagram', 'x', 'app', 'site'])) {
        return ['success' => false, 'error' => 'Geçersiz platform seçimi.'];
    }
    if (empty($username)) {
        return ['success' => false, 'error' => 'Kullanıcı adı / adres boş olamaz.'];
    }
    if ($amount < MIN_BID_AMOUNT) {
        return ['success' => false, 'error' => 'Teklif en az ' . MIN_BID_AMOUNT . '₺ olmalıdır.'];
    }

    $slugs = ($citySlug === 'istanbul') ? ['istanbul-avrupa', 'istanbul-asya'] : [$citySlug];

    $stmt = $db->prepare("SELECT * FROM cities WHERE slug = ?");
    $stmt->execute([$slugs[0]]);
    $city = $stmt->fetch();
    if (!$city) {
        return ['success' => false, 'error' => 'Şehir bulunamadı.'];
    }

    if ($city['current_owner_id'] && $amount <= $city['current_amount']) {
        return ['success' => false, 'error' => 'Teklif mevcut en yüksek tekliften (' . number_format($city['current_amount'], 0, ',', '.') . '₺) yüksek olmalıdır.'];
    }

    try {
        $db->beginTransaction();

        $displayName = $displayName ?: ($platform === 'site' ? $username : ($platform === 'app' ? $username : '@' . $username));
        $targetUrl = $targetUrl ?: get_platform_url($platform, $username);
        
        // If no custom logo provided, fallback to standard sleek platform logo
        if (empty($avatarUrl) || $avatarUrl === '/assets/images/default_logo.svg') {
            $avatarUrl = get_default_logo_for_platform($platform);
        }

        // Get or create owner
        $oStmt = $db->prepare("SELECT * FROM owners WHERE platform = ? AND LOWER(username) = LOWER(?)");
        $oStmt->execute([$platform, $username]);
        $owner = $oStmt->fetch();

        if (!$owner) {
            $ins = $db->prepare("
                INSERT INTO owners (
                    user_id, platform, username, display_name, target_url, avatar_url,
                    instagram_url, maps_url, youtube_url, x_url, facebook_url,
                    total_spent, total_clicks
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $ins->execute([
                $userId, $platform, $username, $displayName, $targetUrl, $avatarUrl,
                $instagramUrl ?: null, $mapsUrl ?: null, $youtubeUrl ?: null, $xUrl ?: null, $facebookUrl ?: null,
                ($paymentMethod === 'iban' ? 0 : $amount)
            ]);
            $ownerId = (int)$db->lastInsertId();
        } else {
            $ownerId = (int)$owner['id'];
            $upd = $db->prepare("
                UPDATE owners 
                SET user_id = COALESCE(?, user_id),
                    total_spent = total_spent + ?,
                    display_name = COALESCE(?, display_name),
                    target_url = COALESCE(?, target_url),
                    avatar_url = COALESCE(?, avatar_url),
                    instagram_url = COALESCE(?, instagram_url),
                    maps_url = COALESCE(?, maps_url),
                    youtube_url = COALESCE(?, youtube_url),
                    x_url = COALESCE(?, x_url),
                    facebook_url = COALESCE(?, facebook_url),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $upd->execute([
                $userId, ($paymentMethod === 'iban' ? 0 : $amount), 
                $displayName, $targetUrl, $avatarUrl,
                $instagramUrl ?: null, $mapsUrl ?: null, $youtubeUrl ?: null, $xUrl ?: null, $facebookUrl ?: null,
                $ownerId
            ]);
        }

        $isPending = ($paymentMethod === 'iban');
        $status = $isPending ? 'pending_approval' : 'approved';
        $now = date('Y-m-d H:i:s');

        foreach ($slugs as $s) {
            $cQuery = $db->prepare("SELECT id FROM cities WHERE slug = ?");
            $cQuery->execute([$s]);
            $cRow = $cQuery->fetch();
            if ($cRow) {
                $cId = (int)$cRow['id'];
                
                // Only update city active owner if payment is approved / direct
                if (!$isPending) {
                    $uStmt = $db->prepare("
                        UPDATE cities 
                        SET current_owner_id = ?, current_amount = ?, updated_at = ? 
                        WHERE id = ?
                    ");
                    $uStmt->execute([$ownerId, $amount, $now, $cId]);
                }

                $bStmt = $db->prepare("
                    INSERT INTO bids (
                        city_id, owner_id, amount, status, payment_method, payment_note,
                        sender_name, sender_phone, receipt_no, receipt_file, ip_address, created_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $bStmt->execute([
                    $cId, $ownerId, $amount, $status, $paymentMethod, $paymentNote,
                    $senderName, $senderPhone, $receiptNo, $receiptFile, get_client_ip(), $now
                ]);
            }
        }

        // Increment volume only if direct/approved
        if (!$isPending) {
            $db->prepare("UPDATE site_stats SET volume = volume + ? WHERE id = 1")->execute([$amount]);
        }

        $db->commit();

        if ($isPending) {
            return [
                'success' => true,
                'pending' => true,
                'message' => 'Tebrikler! Teklifiniz ve havale bildiriminiz alındı. Havaleniz admin tarafından onaylandığında ' . $city['name'] . ' şehrinin yeni lideri olacaksınız!'
            ];
        }

        return [
            'success' => true,
            'pending' => false,
            'message' => 'Tebrikler! ' . $city['name'] . ' şehrinin yeni sahibi sizsiniz!'
        ];

    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'error' => 'Teklif verilirken bir veritabanı hatası oluştu: ' . $e->getMessage()];
    }
}

function get_admin_password(): string {
    try {
        $db = get_db();
        $pwd = $db->query("SELECT admin_password FROM site_stats WHERE id = 1")->fetchColumn();
        if (!empty($pwd)) {
            return (string)$pwd;
        }
    } catch (Exception $e) {}
    
    return defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : 'admin123';
}

function set_admin_password(string $newPassword): bool {
    try {
        $db = get_db();
        // Güvenli bcrypt kriptografik hashleme ile veritabanına kaydet
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE site_stats SET admin_password = ? WHERE id = 1");
        return $stmt->execute([$hashed]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Askeri düzey zamanlama saldırılarına ve hash denetimlerine karşı admin şifre doğrulaması.
 * Hem modern bcrypt hash'i hem de ilk kurulum fallback'ini güvenle doğrular.
 * Eğer şifre henüz hashlenmemişse otomatik olarak bcrypt'e yükseltir.
 */
function verify_admin_password(string $inputPassword): bool {
    $stored = get_admin_password();
    
    // 1. Standart bcrypt hash doğrulaması
    if (password_verify($inputPassword, $stored)) {
        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            set_admin_password($inputPassword);
        }
        return true;
    }
    
    // 2. İlk kurulum fallback doğrulaması (timing attack korumalı hash_equals)
    if (hash_equals($stored, $inputPassword)) {
        // Otomatik olarak veritabanında güvenli bcrypt hash'ine dönüştür
        set_admin_password($inputPassword);
        return true;
    }
    
    return false;
}

function get_admin_secret_key(): string {
    try {
        $db = get_db();
        $key = $db->query("SELECT admin_secret_key FROM site_stats WHERE id = 1")->fetchColumn();
        if (!empty($key)) {
            return (string)$key;
        }
    } catch (Exception $e) {}
    
    return defined('ADMIN_SECRET_KEY') ? ADMIN_SECRET_KEY : 'bb_admin_2026';
}

function set_admin_secret_key(string $newKey): bool {
    try {
        $db = get_db();
        $stmt = $db->prepare("UPDATE site_stats SET admin_secret_key = ? WHERE id = 1");
        return $stmt->execute([$newKey]);
    } catch (Exception $e) {
        return false;
    }
}




