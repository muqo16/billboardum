<?php
require_once __DIR__ . '/auth_check.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Güvenlik doğrulaması (CSRF) başarısız oldu.';
    } else {
        $bidId = (int)($_POST['bid_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'approve') {
            if (approve_bid($bidId)) {
                $msg = 'Teklif ve ödeme başarıyla onaylandı! Şehir yeni sahibine devredildi.';
            } else {
                $err = 'Onaylama işlemi sırasında bir hata oluştu.';
            }
        } elseif ($action === 'reject') {
            if (reject_bid($bidId)) {
                $msg = 'Teklif reddedildi.';
            } else {
                $err = 'Reddetme işlemi sırasında bir hata oluştu.';
            }
        }
    }
}

$pendingBids = get_pending_bids();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Ödeme ve Teklif Onayları — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">🏦 Havale / IBAN Ödeme Onayları</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">Kullanıcıların IBAN ile gönderdiği ve onay bekleyen reklam teklifleri</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
                <a href="/admin/cities.php" class="stat-pill">81 İl Yönetimi</a>
                <a href="/admin/settings.php" class="stat-pill">IBAN & Stripe Ayarları</a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <div class="panel" style="padding: 16px; overflow-x: auto;">
            <?php if (empty($pendingBids)): ?>
                <div style="text-align: center; padding: 30px; color: var(--ink-muted);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 10px; display: block; opacity: 0.5;"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>
                    <strong>Şu anda onay bekleyen ödeme bulunmuyor.</strong>
                    <p style="font-size: 13px; margin-top: 4px;">Kullanıcılar IBAN havalesi ile teklif verdiğinde burada listelenecektir.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--ink-muted);">
                            <th style="padding: 10px 8px;">ID / Tarih</th>
                            <th style="padding: 10px 8px;">Hedef Şehir</th>
                            <th style="padding: 10px 8px;">Reklam, Web Sitesi & Sosyal Medya (Denetle)</th>
                            <th style="padding: 10px 8px;">Tutar</th>
                            <th style="padding: 10px 8px;">Havale & Dekont Bilgileri</th>
                            <th style="padding: 10px 8px; text-align: right;">Onay / Red</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingBids as $pb): ?>
                            <tr style="border-bottom: 1px solid var(--border); vertical-align: top;">
                                <td style="padding: 12px 8px;">
                                    <strong>#<?= $pb['id'] ?></strong>
                                    <div style="font-size: 11.5px; color: var(--ink-muted);"><?= $pb['created_at'] ?></div>
                                    <div style="font-size: 11px; color: var(--ink-muted); margin-top: 4px;">IP: <?= htmlspecialchars($pb['ip_address'] ?? '—') ?></div>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <strong style="color: var(--ink); font-size: 15px;"><?= htmlspecialchars($pb['city_name']) ?></strong> 
                                    <span style="font-size: 12px; color: var(--ink-muted);">(<?= $pb['plate_code'] ?>)</span>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <img src="<?= htmlspecialchars((!empty($pb['avatar_url']) && $pb['avatar_url'] !== '/assets/images/default_logo.svg') ? $pb['avatar_url'] : get_default_logo_for_platform($pb['platform'] ?? 'site')) ?>" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);" />
                                        <div style="flex: 1;">
                                            <div style="font-weight: 800; font-size: 14px; color: var(--ink);">
                                                <?= htmlspecialchars($pb['display_name'] ?: $pb['username']) ?>
                                                <span style="font-size: 11px; background: #ede6d6; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: 700;"><?= strtoupper($pb['platform']) ?></span>
                                            </div>
                                            <div style="font-size: 12px; color: var(--ink-muted); margin-top: 2px;">
                                                Kullanıcı Adı: <strong><?= htmlspecialchars($pb['username']) ?></strong>
                                                <?php if (!empty($pb['user_name'])): ?>
                                                    · Üye: <?= htmlspecialchars($pb['user_name']) ?> (<?= htmlspecialchars($pb['user_email']) ?>)
                                                <?php endif; ?>
                                            </div>

                                            <!-- Hedef Web Sitesi ve Yasa Dışı Kontrol Linki -->
                                            <?php if (!empty($pb['target_url'])): ?>
                                                <div style="margin-top: 8px; background: #fff8eb; border: 1.5px solid #f59e0b; padding: 6px 10px; border-radius: 8px; font-size: 12px;">
                                                    <div style="font-weight: 700; color: #92400e; margin-bottom: 3px;">
                                                        🌐 Tıklanınca Gidilecek Hedef Web Sitesi:
                                                    </div>
                                                    <a href="<?= htmlspecialchars($pb['target_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: 800; word-break: break-all; text-decoration: underline;">
                                                        <?= htmlspecialchars($pb['target_url']) ?> ↗
                                                    </a>
                                                    <div style="font-size: 11px; color: #b45309; margin-top: 2px;">
                                                        ⚠️ Onaylamadan önce siteyi açıp kumar, bahis vb. yasa dışı içerik barındırmadığını teyit edin.
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Varsa Eklenen Sosyal Medya Hesapları -->
                                            <?php 
                                            $socials = array_filter([
                                                'Instagram' => $pb['instagram_url'] ?? null,
                                                'Google Maps' => $pb['maps_url'] ?? null,
                                                'YouTube' => $pb['youtube_url'] ?? null,
                                                'X (Twitter)' => $pb['x_url'] ?? null,
                                                'Facebook' => $pb['facebook_url'] ?? null,
                                            ]);
                                            if (!empty($socials)): ?>
                                                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 5px;">
                                                    <?php foreach ($socials as $sName => $sUrl): ?>
                                                        <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 4px; color: #334155; text-decoration: none; font-weight: 600;">
                                                            <?= $sName ?> ↗
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 8px; white-space: nowrap;">
                                    <strong style="color: #166534; font-size: 18px;"><?= number_format($pb['amount'], 0, ',', '.') ?>₺</strong>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 10px; border-radius: 8px; font-size: 12.5px; line-height: 1.5;">
                                        <div><strong>👤 Gönderen:</strong> <?= htmlspecialchars($pb['sender_name'] ?: 'Belirtilmedi') ?></div>
                                        <div><strong>📞 Telefon:</strong> 
                                            <?php if (!empty($pb['sender_phone'])): ?>
                                                <a href="tel:<?= htmlspecialchars($pb['sender_phone']) ?>" style="font-weight: 800; color: #166534; text-decoration: underline;"><?= htmlspecialchars($pb['sender_phone']) ?></a>
                                                <span style="font-size: 10.5px; color: var(--ink-muted);">(Teyit İçin Ara)</span>
                                            <?php else: ?>
                                                <span style="color: #dc2626;">Telefon girilmedi</span>
                                            <?php endif; ?>
                                        </div>
                                        <div><strong>🧾 Dekont / Not:</strong> <?= htmlspecialchars($pb['receipt_no'] ?: ($pb['payment_note'] ?: '—')) ?></div>
                                        
                                        <?php if (!empty($pb['receipt_file'])): ?>
                                            <div style="margin-top: 6px;">
                                                <a href="<?= htmlspecialchars($pb['receipt_file']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 11.5px; text-decoration: none;">
                                                    📄 Yüklenen Dekont Belgesini Aç ↗
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 12px 8px; text-align: right; white-space: nowrap;">
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                                        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                        <input type="hidden" name="bid_id" value="<?= $pb['id'] ?>">
                                        <button type="submit" name="action" value="approve" onclick="return confirm('Bu teklifi onaylayıp şehri haritada bu reklam verene devretmek istediğinize emin misiniz?');" class="stat-pill" style="background: #166534; color: #fff; cursor: pointer; border: none; font-weight: 700; width: 100%; text-align: center; padding: 7px 12px;">
                                            ✓ Onayla & Yayına Al
                                        </button>
                                        <button type="submit" name="action" value="reject" onclick="return confirm('Bu teklifi yasa dışı içerik veya sahte dekont gerekçesiyle REDDETMEK istediğinize emin misiniz?');" class="stat-pill" style="background: #dc2626; color: #fff; cursor: pointer; border: none; font-weight: 700; width: 100%; text-align: center; padding: 7px 12px;">
                                            ✕ Sahte / Yasa Dışı Reddet
                                        </button>
                                    </form>
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
