<?php
require_once __DIR__ . '/auth_check.php';

$db = get_db();
$msg = '';

// Handle ad removal (vacate cities & delete owner) with CSRF protection
if (isset($_GET['delete_ad'])) {
    if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $msg = "Güvenlik doğrulaması (CSRF) başarısız oldu!";
    } else {
        $ownerId = (int)$_GET['delete_ad'];
        if (delete_owner_and_vacate_cities($ownerId)) {
            $msg = "Reklam başarıyla yayından kaldırıldı ve kapattığı tüm şehirler boşaltıldı.";
        }
    }
}

$owners = get_all_owners_with_details();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Reklam & Site Denetimi (Yasa Dışı İçerik Kontrolü) — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">📢 Reklam, Web Sitesi & Sosyal Medya Denetimi</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">Sitede yayınlanan tüm reklamları, hedef web sitelerini ve sosyal medya hesaplarını yakından takip edin</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/payments.php" class="stat-pill" style="background: #fef08a; color: #854d0e; font-weight: 700;">🏦 Ödeme Onayları</a>
                <a href="/admin/users.php" class="stat-pill">👥 Kullanıcılar</a>
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                ✓ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div style="background: #fff8eb; border: 1.5px solid #f59e0b; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; color: #92400e;">
            <strong>🛡️ Güvenlik & Yasa Dışı Site Denetim Kılavuzu:</strong>
            Kullanıcıların eklediği web sitelerini aşağıdaki <strong>"Siteyi Aç & İncele ↗"</strong> butonlarıyla kontrol edebilirsiniz. Yasa dışı bahis, kumar, dolandırıcılık veya telif ihlali barındıran bir reklam tespit ettiğinizde sağdaki kırmızı <strong>"Yayından Kaldır & Şehri Boşalt"</strong> butonuna basarak haritadan anında silebilirsiniz.
        </div>

        <div class="panel" style="padding: 16px; overflow-x: auto;">
            <?php if (empty($owners)): ?>
                <div style="text-align: center; padding: 30px; color: var(--ink-muted);">
                    Henüz kayıtlı bir reklam bulunmuyor.
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--ink-muted);">
                            <th style="padding: 10px 8px;">Logo / Marka</th>
                            <th style="padding: 10px 8px;">Platform / Kullanıcı Adı</th>
                            <th style="padding: 10px 8px;">Hedef Web Sitesi (Tıklanınca Gidilen)</th>
                            <th style="padding: 10px 8px;">Sosyal Medya Linkleri</th>
                            <th style="padding: 10px 8px;">Kapattığı Şehirler</th>
                            <th style="padding: 10px 8px;">Harcama & Tık</th>
                            <th style="padding: 10px 8px; text-align: right;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($owners as $o): ?>
                            <tr style="border-bottom: 1px solid var(--border); vertical-align: middle;">
                                <td style="padding: 12px 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <img src="<?= htmlspecialchars((!empty($o['avatar_url']) && $o['avatar_url'] !== '/assets/images/default_logo.svg') ? $o['avatar_url'] : get_default_logo_for_platform($o['platform'] ?? 'site')) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);" />
                                        <div>
                                            <strong style="color: var(--ink);"><?= htmlspecialchars($o['display_name'] ?: $o['username']) ?></strong>
                                            <div style="font-size: 11px; color: var(--ink-muted);">#<?= $o['id'] ?> · <?= htmlspecialchars($o['created_at']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <span style="display: inline-block; background: #ede6d6; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-bottom: 3px;">
                                        <?= strtoupper($o['platform']) ?>
                                    </span>
                                    <div style="font-size: 12.5px; font-weight: 600;">@<?= htmlspecialchars(ltrim($o['username'], '@')) ?></div>
                                    <?php if (!empty($o['user_name'])): ?>
                                        <div style="font-size: 11px; color: var(--ink-muted);">Üye: <?= htmlspecialchars($o['user_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <?php if (!empty($o['target_url'])): ?>
                                        <div style="max-width: 260px;">
                                            <a href="<?= htmlspecialchars($o['target_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: 700; font-size: 12.5px; word-break: break-all; text-decoration: underline; display: inline-block;">
                                                <?= htmlspecialchars($o['target_url']) ?> ↗
                                            </a>
                                            <div style="margin-top: 3px;">
                                                <a href="<?= htmlspecialchars($o['target_url']) ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 700; text-decoration: none;">
                                                    🔍 Siteyi Aç & İncele ↗
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--ink-muted); font-size: 12px;">Link yok</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <?php
                                    $socList = array_filter([
                                        'Instagram' => $o['instagram_url'],
                                        'Maps' => $o['maps_url'],
                                        'YouTube' => $o['youtube_url'],
                                        'X' => $o['x_url'],
                                        'Facebook' => $o['facebook_url'],
                                    ]);
                                    if (!empty($socList)): ?>
                                        <div style="display: flex; flex-direction: column; gap: 3px;">
                                            <?php foreach ($socList as $sName => $sUrl): ?>
                                                <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 11.5px; color: #475569; text-decoration: underline;">
                                                    <?= $sName ?> ↗
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--ink-muted); font-size: 12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <?php if ($o['city_count'] > 0): ?>
                                        <span style="display: inline-block; background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 999px; font-size: 11.5px; font-weight: 800; margin-bottom: 3px;">
                                            <?= $o['city_count'] ?> İl Sahibi
                                        </span>
                                        <div style="font-size: 11.5px; color: var(--ink); max-width: 180px;"><?= htmlspecialchars($o['owned_city_names'] ?? '') ?></div>
                                    <?php else: ?>
                                        <span style="color: var(--ink-muted); font-size: 12px;">Henüz ili yok</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px; white-space: nowrap;">
                                    <strong style="color: #166534; font-size: 14px;"><?= number_format($o['total_spent'], 0, ',', '.') ?>₺</strong>
                                    <div style="font-size: 11.5px; color: var(--ink-muted);"><?= number_format($o['total_clicks'], 0, ',', '.') ?> Tıklama</div>
                                </td>
                                <td style="padding: 12px 8px; text-align: right; white-space: nowrap;">
                                    <a href="/admin/ads.php?delete_ad=<?= $o['id'] ?>&csrf_token=<?= get_csrf_token() ?>" onclick="return confirm('DİKKAT: Bu reklamı yasa dışı olduğu gerekçesiyle haritadan kaldırmak ve tüm şehirlerini boşaltmak istediğinize emin misiniz?');" class="stat-pill" style="background: #dc2626; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; padding: 6px 12px;">
                                        🛑 Yayından Kaldır & Şehri Boşalt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
