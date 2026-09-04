<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged']);
    unset($_SESSION['admin_gate_unlocked']);
    header("Location: /");
    exit;
}

// Secret Gatekeeper Logic ("admin girişini sakla")
$secretKey = get_admin_secret_key();

// If key is provided in query string, verify it
if (isset($_GET['key'])) {
    $providedKey = (string)$_GET['key'];
    if (!check_rate_limit('admin_gate', 5, 30)) {
        http_response_code(404);
        include __DIR__ . '/../404.php';
        exit;
    }

    if (hash_equals($secretKey, $providedKey)) {
        clear_failed_attempts('admin_gate');
        $_SESSION['admin_gate_unlocked'] = true;
        // Clean URL to prevent leaking key in browser history / referrers
        header("Location: /admin/");
        exit;
    } else {
        record_failed_attempt('admin_gate');
        http_response_code(404);
        include __DIR__ . '/../404.php';
        exit;
    }
}

// If not logged in AND gate is not unlocked, show standard 404 (Admin is completely hidden)
if (empty($_SESSION['admin_logged']) && empty($_SESSION['admin_gate_unlocked'])) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

$error = '';

// Admin login handler with CSRF and Brute Force protection
if (isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Güvenlik doğrulaması (CSRF) başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } elseif (!check_rate_limit('admin_login', 5, 15)) {
        $error = 'Çok fazla hatalı giriş denemesi! Güvenliğiniz için 15 dakika boyunca giriş kilitlendi.';
    } else {
        $pass = (string)($_POST['password'] ?? '');
        if (verify_admin_password($pass)) {
            clear_failed_attempts('admin_login');
            session_regenerate_id(true);
            $_SESSION['admin_logged'] = true;
            header("Location: /admin/");
            exit;
        } else {
            record_failed_attempt('admin_login');
            $error = "Hatalı yönetici şifresi!";
        }
    }
}

if (empty($_SESSION['admin_logged'])) {
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Admin Girişi — <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg);">
    <div class="panel" style="max-width: 380px; width: 100%; text-align: center;">
        <h2 style="font-family: var(--font-serif); margin-bottom: 12px;">🛡️ Yönetim Girişi</h2>
        <p style="font-size: 13px; color: var(--ink-muted); margin-bottom: 18px;">Güvenli yönetici oturumu</p>

        <?php if ($error): ?>
            <div class="bid-message error" style="margin-bottom: 14px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div class="field">
                <input type="password" name="password" placeholder="Yönetici Şifresi" required autofocus />
            </div>
            <button type="submit" name="login" class="btn-primary" style="width: 100%;">Giriş Yap</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

$db = get_db();

$successMsg = '';
if (isset($_POST['clear_logs'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $db->exec("DELETE FROM visitors; DELETE FROM login_attempts;");
        $successMsg = 'Ziyaretçi ve IP logları başarıyla tamamen temizlendi.';
    }
}

$totalCities = (int)$db->query("SELECT COUNT(*) FROM cities WHERE current_owner_id IS NOT NULL")->fetchColumn();
$totalBids = (int)$db->query("SELECT COUNT(*) FROM bids")->fetchColumn();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingBids = get_pending_bids();
$stats = get_site_stats();
$recentVisitors = $db->query("SELECT * FROM visitors ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Yönetim Paneli — <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 960px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 26px;">🛡️ <?= SITE_NAME ?> Yönetim Paneli</h1>
                <span style="color: var(--ink-muted); font-size: 13.5px;">Tüm harita, reklam veren, ödeme ve teklif kontrol merkezi</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/" target="_blank" class="stat-pill">Siteyi Görüntüle ↗</a>
                <a href="/admin/?logout=1" class="stat-pill" style="color: #dc2626;">Çıkış Yap</a>
            </div>
        </div>

        <?php if (!empty($pendingBids)): ?>
            <div style="background: #fef3c7; border: 1.5px solid #f59e0b; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🔔</span>
                    <div>
                        <strong style="color: #92400e; font-size: 15px;">Onay Bekleyen <?= count($pendingBids) ?> Adet Havale / IBAN Ödemesi Var!</strong>
                        <div style="font-size: 13px; color: #78350f;">Kullanıcıların havalesini kontrol edip tek tıkla onaylayabilirsiniz.</div>
                    </div>
                </div>
                <a href="/admin/payments.php" class="btn-primary" style="height: 38px; padding: 0 16px; font-size: 13px; background: #92400e;">
                    Ödemeleri İncele & Onayla →
                </a>
            </div>
        <?php endif; ?>

        <!-- Stat Grid - 100% Genuine Dynamic Live Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Dolu Şehirler</span>
                <div style="font-size: 24px; font-weight: 800; color: var(--ink); margin-top: 4px;"><?= $stats['active_cities'] ?> / 81</div>
            </div>
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Toplam Gerçek Hacim</span>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;"><?= number_format($stats['volume'], 0, ',', '.') ?>₺</div>
            </div>
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Gerçek Ziyaretçi</span>
                <div style="font-size: 24px; font-weight: 800; color: #166534; margin-top: 4px;"><?= number_format($stats['visits'], 0, ',', '.') ?></div>
            </div>
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Toplam Tıklama</span>
                <div style="font-size: 24px; font-weight: 800; color: #d97706; margin-top: 4px;"><?= number_format($stats['total_clicks'], 0, ',', '.') ?></div>
            </div>
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Teklif Adedi</span>
                <div style="font-size: 24px; font-weight: 800; color: var(--ink); margin-top: 4px;"><?= $totalBids ?></div>
            </div>
            <div class="panel" style="padding: 16px;">
                <span style="font-size: 12px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Kayıtlı Reklam Veren</span>
                <div style="font-size: 24px; font-weight: 800; color: var(--ink); margin-top: 4px;"><?= $totalUsers ?></div>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <a href="/admin/payments.php" class="panel" style="text-decoration: none; border-left: 4px solid #f59e0b;">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink); display: flex; align-items: center; justify-content: space-between;">
                    <span>🏦 Ödeme Onayları</span>
                    <?php if (!empty($pendingBids)): ?>
                        <span style="background: #ef4444; color: #fff; font-size: 11px; padding: 2px 7px; border-radius: 999px;"><?= count($pendingBids) ?></span>
                    <?php endif; ?>
                </h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Havale/EFT ile yapılan teklifleri onayla veya reddet.</p>
            </a>

            <a href="/admin/cities.php" class="panel" style="text-decoration: none; border-left: 4px solid var(--ink);">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink);">📍 81 İl Yönetimi</h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Şehir sahiplerini, logolarını, fiyatlarını ve tıklamalarını düzenle.</p>
            </a>

            <a href="/admin/users.php" class="panel" style="text-decoration: none; border-left: 4px solid #166534;">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink);">👥 Kullanıcı Yönetimi</h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Kayıtlı reklam verenleri ve harcama geçmişlerini incele.</p>
            </a>

            <a href="/admin/bids.php" class="panel" style="text-decoration: none; border-left: 4px solid #0284c7;">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink);">📜 Teklif Geçmişi (Loglar)</h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Verilen tüm teklifleri, IP adreslerini ve zaman damgalarını denetle.</p>
            </a>

            <a href="/admin/ads.php" class="panel" style="text-decoration: none; border-left: 4px solid #dc2626;">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink);">📢 Reklam & Yasa Dışı Site Denetimi</h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Hedef web sitelerini, kumar/bahis içeriklerini ve sosyal medyaları denetle.</p>
            </a>

            <a href="/admin/settings.php" class="panel" style="text-decoration: none; border-left: 4px solid #6366f1;">
                <h3 style="font-size: 16px; margin-bottom: 4px; color: var(--ink);">⚙️ IBAN, Stripe & Ayarlar</h3>
                <p style="font-size: 13px; color: var(--ink-muted);">Banka IBAN bilgileri, Stripe API anahtarları ve canlı sayaç ayarları.</p>
            </a>
        </div>

        <?php if ($successMsg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                ✓ <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>

        <!-- Real Visitor Live Log Table -->
        <div class="panel" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🌐 Son Gerçek Ziyaretçiler (KVKK/GDPR Gizlenmiş IP)</span>
                    <span style="font-size: 12px; font-weight: 500; color: var(--ink-muted);">(IP adresleri maskeli kaydedilir)</span>
                </h3>
                <form method="POST" style="margin: 0;" onsubmit="return confirm('Tüm ziyaretçi ve IP kayıtlarını tamamen temizlemek istediğinize emin misiniz?');">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    <button type="submit" name="clear_logs" class="stat-pill" style="color: #dc2626; font-size: 11.5px; cursor: pointer; border: 1px solid #fca5a5; background: #fff5f5; padding: 4px 10px;">🗑️ Logları Temizle</button>
                </form>
            </div>
            <?php if (empty($recentVisitors)): ?>
                <p style="font-size: 13.5px; color: var(--ink-muted);">Henüz kaydedilmiş ziyaretçi oturumu bulunmuyor veya loglar temizlendi.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1.5px solid var(--border); color: var(--ink-muted);">
                                <th style="padding: 8px 6px;">ID</th>
                                <th style="padding: 8px 6px;">IP Adresi (Maskeli)</th>
                                <th style="padding: 8px 6px;">Sayfa</th>
                                <th style="padding: 8px 6px;">Tarayıcı / Cihaz</th>
                                <th style="padding: 8px 6px;">Zaman Damgası</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentVisitors as $v): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 8px 6px; font-weight: 700;">#<?= $v['id'] ?></td>
                                    <td style="padding: 8px 6px; font-family: monospace; color: #0284c7;"><?= htmlspecialchars(mask_ip($v['ip'])) ?></td>
                                    <td style="padding: 8px 6px;"><?= htmlspecialchars($v['page_url'] ?: '/') ?></td>
                                    <td style="padding: 8px 6px; color: var(--ink-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($v['user_agent'] ?? '') ?>">
                                        <?= htmlspecialchars(substr($v['user_agent'] ?? 'Bilinmiyor', 0, 45)) ?>...
                                    </td>
                                    <td style="padding: 8px 6px; color: var(--ink-muted);"><?= date('d.m.Y H:i:s', strtotime($v['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
