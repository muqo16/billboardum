<?php
require_once __DIR__ . '/auth_check.php';

$db = get_db();
$msg = '';

// Delete user and vacate all their cities/ads with CSRF check
if (isset($_GET['delete_user'])) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $uid = (int)$_GET['delete_user'];
        if (delete_user_and_all_data($uid)) {
            $msg = "Kullanıcı ve haritadaki tüm reklamları başarıyla silindi.";
        }
    } else {
        $msg = "Güvenlik doğrulaması (CSRF) başarısız oldu!";
    }
}

// Delete single ad of user and vacate its cities with CSRF check
if (isset($_GET['delete_ad'])) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $ownerId = (int)$_GET['delete_ad'];
        if (delete_owner_and_vacate_cities($ownerId)) {
            $msg = "Kullanıcının seçilen reklamı haritadan kaldırıldı ve şehri boşaltıldı.";
        }
    } else {
        $msg = "Güvenlik doğrulaması (CSRF) başarısız oldu!";
    }
}

$users = $db->query("
    SELECT u.*, 
           COUNT(DISTINCT c.id) as cities_count,
           COALESCE(SUM(c.current_amount), 0) as total_spent,
           COALESCE(SUM(c.total_clicks), 0) as total_clicks
    FROM users u
    LEFT JOIN owners o ON o.user_id = u.id
    LEFT JOIN cities c ON c.current_owner_id = o.id
    GROUP BY u.id
    ORDER BY u.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Kullanıcılar & Reklam Verenler — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">👥 Kullanıcılar, Reklamlar & Yasa Dışı Site Takibi</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">Kayıtlı reklam verenlerin sitelerini, sosyal medyalarını ve şehirlerini denetleyin</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/ads.php" class="stat-pill" style="background: #dbeafe; color: #1e40af; font-weight: 700;">📢 Tüm Reklam & Siteler</a>
                <a href="/admin/payments.php" class="stat-pill" style="background: #fef08a; color: #854d0e; font-weight: 700;">🏦 Ödeme Onayları</a>
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                ✓ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="panel" style="padding: 16px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--ink-muted);">
                        <th style="padding: 10px 8px; width: 60px;">ID</th>
                        <th style="padding: 10px 8px; width: 200px;">Kullanıcı Bilgileri</th>
                        <th style="padding: 10px 8px;">Kayıtlı Reklamları, Web Siteleri & Sosyal Medya (Denetle)</th>
                        <th style="padding: 10px 8px; width: 110px;">Harcama</th>
                        <th style="padding: 10px 8px; text-align: right; width: 100px;">Kullanıcı İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): 
                        // Fetch all ads/owners created by this user
                        $oStmt = $db->prepare("
                            SELECT o.*, 
                                   (SELECT COUNT(*) FROM cities WHERE current_owner_id = o.id) as city_count,
                                   (SELECT GROUP_CONCAT(name, ', ') FROM cities WHERE current_owner_id = o.id) as owned_city_names
                            FROM owners o 
                            WHERE o.user_id = ? 
                            ORDER BY o.id DESC
                        ");
                        $oStmt->execute([$u['id']]);
                        $userAds = $oStmt->fetchAll();
                    ?>
                        <tr style="border-bottom: 2px solid var(--border); vertical-align: top;">
                            <td style="padding: 12px 8px; color: var(--ink-muted); font-weight: 700;">
                                #<?= $u['id'] ?>
                            </td>
                            <td style="padding: 12px 8px;">
                                <div style="font-weight: 800; font-size: 14.5px; color: var(--ink);">
                                    <?= htmlspecialchars($u['name']) ?>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span style="background: #dbeafe; color: #1e40af; font-size: 10.5px; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">YÖNETİCİ</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 12.5px; color: #2563eb; margin-top: 2px;"><?= htmlspecialchars($u['email']) ?></div>
                                <div style="font-size: 11px; color: var(--ink-muted); margin-top: 4px;">Kayıt: <?= htmlspecialchars($u['created_at']) ?></div>
                                <div style="margin-top: 6px; font-size: 12px;">
                                    <span style="font-weight: 700; color: #166534;"><?= $u['cities_count'] ?> İl Sahibi</span>
                                </div>
                            </td>
                            <td style="padding: 12px 8px;">
                                <?php if (empty($userAds)): ?>
                                    <span style="color: var(--ink-muted); font-size: 12px; font-style: italic;">Henüz kaydedilmiş reklam veya web sitesi yok.</span>
                                <?php else: ?>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <?php foreach ($userAds as $ad): 
                                            $socials = array_filter([
                                                'Instagram' => $ad['instagram_url'],
                                                'Maps' => $ad['maps_url'],
                                                'YouTube' => $ad['youtube_url'],
                                                'X' => $ad['x_url'],
                                                'Facebook' => $ad['facebook_url'],
                                            ]);
                                        ?>
                                            <div style="background: #fff; border: 1.5px solid var(--border); border-radius: 10px; padding: 10px; box-shadow: var(--shadow-sm);">
                                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <img src="<?= htmlspecialchars((!empty($ad['avatar_url']) && $ad['avatar_url'] !== '/assets/images/default_logo.svg') ? $ad['avatar_url'] : get_default_logo_for_platform($ad['platform'] ?? 'site')) ?>" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);" />
                                                        <strong style="font-size: 13.5px;"><?= htmlspecialchars($ad['display_name'] ?: $ad['username']) ?></strong>
                                                        <span style="background: #ede6d6; font-size: 10.5px; font-weight: 700; padding: 1px 5px; border-radius: 4px;">
                                                            <?= strtoupper($ad['platform']) ?>
                                                        </span>
                                                        <span style="color: var(--ink-muted); font-size: 12px;">@<?= htmlspecialchars(ltrim($ad['username'], '@')) ?></span>
                                                    </div>
                                                    <div>
                                                        <a href="/admin/users.php?delete_ad=<?= $ad['id'] ?>&csrf_token=<?= get_csrf_token() ?>" onclick="return confirm('DİKKAT: Bu reklamı yasa dışı olduğu gerekçesiyle silmek ve kapattığı tüm şehirleri boşaltmak istiyor musunuz?');" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-decoration: none;">
                                                            🛑 Reklamı Yayından Kaldır
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- Hedef URL ve İnceleme Linki -->
                                                <?php if (!empty($ad['target_url'])): ?>
                                                    <div style="margin-top: 6px; font-size: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                        <span style="color: var(--ink-muted); font-weight: 600;">🌐 Hedef Site:</span>
                                                        <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: 700; text-decoration: underline;">
                                                            <?= htmlspecialchars($ad['target_url']) ?> ↗
                                                        </a>
                                                        <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener noreferrer" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 10.5px; padding: 1px 6px; border-radius: 4px; text-decoration: none; font-weight: 700;">
                                                            🔍 Siteyi Aç & İncele ↗
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Sosyal Medyalar -->
                                                <?php if (!empty($socials)): ?>
                                                    <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 5px;">
                                                        <?php foreach ($socials as $sName => $sUrl): ?>
                                                            <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 11px; background: #f8fafc; border: 1px solid #cbd5e1; padding: 2px 6px; border-radius: 4px; color: #475569; text-decoration: none;">
                                                                <?= $sName ?> ↗
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Şehir Bilgisi -->
                                                <?php if ($ad['city_count'] > 0): ?>
                                                    <div style="margin-top: 6px; font-size: 11.5px; color: #166534;">
                                                        📍 <strong>Kapattığı Şehirler:</strong> <?= htmlspecialchars($ad['owned_city_names'] ?? '') ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 8px; white-space: nowrap;">
                                <strong style="color: #166534; font-size: 15px;"><?= format_money($u['total_spent']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--ink-muted); margin-top: 2px;"><?= format_number($u['total_clicks']) ?> Tık</div>
                            </td>
                            <td style="padding: 12px 8px; text-align: right; white-space: nowrap;">
                                <a href="/admin/users.php?delete_user=<?= $u['id'] ?>&csrf_token=<?= get_csrf_token() ?>" onclick="return confirm('DİKKAT: Bu kullanıcıyı ve haritadaki TÜM reklamlarını kalıcı olarak silmek istediğinize emin misiniz?');" class="stat-pill" style="color: #dc2626; font-size: 12px; font-weight: 700; border-color: #fecaca; background: #fef2f2;">
                                    🗑️ Kullanıcıyı Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
