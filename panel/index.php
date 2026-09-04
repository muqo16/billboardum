<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

$user = get_current_user_info();
if (!$user) {
    header("Location: /giris.php");
    exit;
}

$pageTitle = "Reklam Veren Paneli — " . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_socials'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $ownerId = (int)($_POST['owner_id'] ?? 0);
        $db = get_db();
        $chk = $db->prepare("SELECT id FROM owners WHERE id = ? AND user_id = ?");
        $chk->execute([$ownerId, $user['id']]);
        if ($chk->fetch()) {
            update_owner_links($ownerId, [
                'instagram_url' => $_POST['instagram_url'] ?? '',
                'maps_url' => $_POST['maps_url'] ?? '',
                'youtube_url' => $_POST['youtube_url'] ?? '',
                'x_url' => $_POST['x_url'] ?? '',
                'facebook_url' => $_POST['facebook_url'] ?? ''
            ]);
            $msg = 'Sosyal medya ve Google Maps bağlantılarınız başarıyla güncellendi!';
        }
    }
}

$myAds = get_user_ads($user['id']);
$clickLogs = get_user_clicks_log($user['id'], 20);

$totalSpent = 0;
$totalClicks = 0;
foreach ($myAds as $a) {
    $totalSpent += $a['current_amount'];
    $totalClicks += $a['total_clicks'];
}

$firstAd = !empty($myAds) ? $myAds[0] : null;
?>

<main style="max-width: 1000px; margin: 0 auto 50px; padding: 0 20px;">
    <?php if ($msg): ?>
        <div class="bid-message success" style="margin-bottom: 20px;"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- User Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-family: var(--font-serif); font-size: 26px; color: var(--ink);">Hoş Geldin, <?= htmlspecialchars($user['name']) ?></h1>
            <span style="color: var(--ink-muted); font-size: 13.5px;"><?= htmlspecialchars($user['email']) ?> · Reklam Veren Paneli</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/reklam-ver.php" class="btn-primary" style="height: 40px; padding: 0 16px; font-size: 13.5px;">+ Yeni Şehir Kap</a>
            <a href="/cikis.php" class="stat-pill" style="color: #dc2626; font-weight: 600;">Çıkış Yap</a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div class="panel" style="text-align: center;">
            <div style="font-size: 12.5px; color: var(--ink-muted);">Sahip Olduğun Şehirler</div>
            <strong style="font-size: 26px; color: var(--ink);"><?= count($myAds) ?></strong>
        </div>
        <div class="panel" style="text-align: center;">
            <div style="font-size: 12.5px; color: var(--ink-muted);">Toplam Harcaman</div>
            <strong style="font-size: 26px; color: #166534;"><?= format_money($totalSpent) ?></strong>
        </div>
        <div class="panel" style="text-align: center;">
            <div style="font-size: 12.5px; color: var(--ink-muted);">Toplam Aldığın Tıklama</div>
            <strong style="font-size: 26px; color: var(--ink);"><?= format_number($totalClicks) ?></strong>
        </div>
    </div>

    <!-- Active Cities List -->
    <div class="panel" style="margin-bottom: 28px;">
        <h2 style="font-size: 18px; margin-bottom: 16px;">📍 Aktif Şehir Reklamların</h2>
        <?php if (empty($myAds)): ?>
            <div style="text-align: center; padding: 30px; color: var(--ink-muted);">
                Henüz aktif bir şehir reklamınız bulunmuyor.<br>
                <a href="/reklam-ver.php" style="color: var(--ink); font-weight: 700; text-decoration: underline; margin-top: 10px; display: inline-block;">İlk Şehrini Şimdi Kap!</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <?php foreach ($myAds as $ad): 
                    $av = (!empty($ad['avatar_url']) && $ad['avatar_url'] !== '/assets/images/default_logo.svg') ? $ad['avatar_url'] : get_default_logo_for_platform($ad['platform'] ?? 'site');
                    $hasAnySocial = !empty($ad['instagram_url']) || !empty($ad['maps_url']) || !empty($ad['youtube_url']) || !empty($ad['x_url']) || !empty($ad['facebook_url']);
                ?>
                    <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?= htmlspecialchars($av) ?>" style="width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--border);" />
                                <div>
                                    <strong style="font-size: 15px; color: var(--ink);"><?= htmlspecialchars($ad['name']) ?></strong>
                                    <div style="font-size: 12px; color: var(--ink-muted);"><?= htmlspecialchars($ad['display_name']) ?></div>
                                </div>
                            </div>
                            <span class="stat-pill" style="font-weight: 700;"><?= format_money($ad['current_amount']) ?></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--border); font-size: 12.5px;">
                            <span>👁️ <?= format_number($ad['total_clicks']) ?> tıklama</span>
                            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" style="color: var(--gold); text-decoration: underline;">Linke Git &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Click Logs -->
    <div class="panel">
        <h2 style="font-size: 18px; margin-bottom: 16px;">📊 Son Tıklama Kayıtları (Canlı İstatistik)</h2>
        <?php if (empty($clickLogs)): ?>
            <div style="text-align: center; padding: 20px; color: var(--ink-muted); font-size: 13px;">
                Henüz tıklama kaydı oluşmadı. Ziyaretçiler haritadan reklamlarınıza tıkladıkça buraya yansıyacaktır.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 1.5px solid var(--border); text-align: left; color: var(--ink-muted);">
                            <th style="padding: 8px;">Tarih</th>
                            <th style="padding: 8px;">Şehir</th>
                            <th style="padding: 8px;">Platform</th>
                            <th style="padding: 8px;">IP / Cihaz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clickLogs as $log): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 8px;"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td style="padding: 8px; font-weight: 700;"><?= htmlspecialchars($log['city_name']) ?></td>
                                <td style="padding: 8px;"><?= get_platform_label($log['platform']) ?></td>
                                <td style="padding: 8px; color: var(--ink-muted);"><?= htmlspecialchars($log['ip_address'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
