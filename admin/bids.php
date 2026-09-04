<?php
require_once __DIR__ . '/auth_check.php';

$db = get_db();

if (isset($_GET['delete_bid'])) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $bidId = (int)$_GET['delete_bid'];
        $db->prepare("DELETE FROM bids WHERE id = ?")->execute([$bidId]);
    }
    header("Location: /admin/bids.php");
    exit;
}

$bids = get_recent_bids(100, null);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Teklif Geçmişi — Billboardum Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 1050px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">Teklif Geçmişi</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">Tüm onaylı ve bekleyen teklif kayıtları</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/payments.php" class="stat-pill" style="background: #fef08a; color: #854d0e; font-weight: 700;">🏦 Ödeme Onayları</a>
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
            </div>
        </div>

        <div class="panel" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Tarih</th>
                        <th style="padding: 10px;">Şehir</th>
                        <th style="padding: 10px;">Platform</th>
                        <th style="padding: 10px;">Kullanıcı</th>
                        <th style="padding: 10px;">Tutar</th>
                        <th style="padding: 10px;">Durum</th>
                        <th style="padding: 10px;">IP Adresi</th>
                        <th style="padding: 10px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bids as $b): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px; color: var(--ink-muted);">#<?= $b['id'] ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($b['created_at']) ?></td>
                            <td style="padding: 10px; font-weight: 700;"><?= htmlspecialchars($b['city_name']) ?></td>
                            <td style="padding: 10px;"><?= get_platform_label($b['platform']) ?></td>
                            <td style="padding: 10px; font-weight: 600;">@<?= htmlspecialchars(ltrim($b['username'], '@')) ?></td>
                            <td style="padding: 10px; font-weight: 700; color: #166534;"><?= format_money($b['amount']) ?></td>
                            <td style="padding: 10px;">
                                <?php if ($b['status'] === 'approved'): ?>
                                    <span style="display: inline-block; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700;">✓ Onaylandı</span>
                                <?php else: ?>
                                    <span style="display: inline-block; background: #fef08a; color: #854d0e; padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700;">⏳ Onay Bekliyor</span>
                                    <a href="/admin/payments.php" style="font-size: 11.5px; color: #2563eb; font-weight: 700; margin-left: 4px; text-decoration: underline;">Onayla</a>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; color: var(--ink-muted); font-size: 12px; font-family: monospace;"><?= htmlspecialchars(mask_ip($b['ip_address'])) ?></td>
                            <td style="padding: 10px;">
                                <a href="/admin/bids.php?delete_bid=<?= $b['id'] ?>&csrf_token=<?= get_csrf_token() ?>" onclick="return confirm('Bu teklif kaydını silmek istediğinize emin misiniz?');" class="stat-pill" style="color: #dc2626; font-size: 12px;">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
