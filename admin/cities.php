<?php
require_once __DIR__ . '/auth_check.php';

$db = get_db();
$msg = '';

// Handle full city & owner update
if (isset($_POST['update_city'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = "Güvenlik doğrulaması (CSRF) başarısız oldu!";
    } else {
        $cityId = (int)$_POST['city_id'];
        $amount = (int)$_POST['amount'];
        $clicks = (int)$_POST['clicks'];
        $displayName = trim($_POST['display_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $platform = trim($_POST['platform'] ?? 'site');
        $targetUrl = trim($_POST['target_url'] ?? '');
        $avatarUrl = trim($_POST['avatar_url'] ?? '');

        // 1. Update city amounts
        $stmt = $db->prepare("UPDATE cities SET current_amount = ?, total_clicks = ? WHERE id = ?");
        $stmt->execute([$amount, $clicks, $cityId]);

        // 2. If owner info provided, update or assign owner
        if (!empty($username)) {
            $cStmt = $db->prepare("SELECT current_owner_id, slug FROM cities WHERE id = ?");
            $cStmt->execute([$cityId]);
            $cRow = $cStmt->fetch();
            if ($cRow && $cRow['current_owner_id']) {
                $uOwner = $db->prepare("
                    UPDATE owners 
                    SET display_name = ?, username = ?, platform = ?, target_url = ?, avatar_url = ?
                    WHERE id = ?
                ");
                $uOwner->execute([$displayName, $username, $platform, $targetUrl, $avatarUrl, $cRow['current_owner_id']]);
            } else {
                // Create owner and assign
                $ins = $db->prepare("
                    INSERT INTO owners (platform, username, display_name, target_url, avatar_url, total_spent, total_clicks)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $ins->execute([$platform, $username, $displayName, $targetUrl, $avatarUrl, $amount, $clicks]);
                $newOwnerId = $db->lastInsertId();
                $db->prepare("UPDATE cities SET current_owner_id = ? WHERE id = ?")->execute([$newOwnerId, $cityId]);
            }
        }

        $msg = "Şehir ve reklam bilgileri başarıyla güncellendi.";
    }
}

if (isset($_GET['clear_city'])) {
    if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
        $cityId = (int)$_GET['clear_city'];
        $stmt = $db->prepare("UPDATE cities SET current_owner_id = NULL, current_amount = 10 WHERE id = ?");
        $stmt->execute([$cityId]);
    }
    header("Location: /admin/cities.php");
    exit;
}

$cities = get_all_cities();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>81 İl Reklam Yönetimi — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .edit-row input, .edit-row select {
            font-size: 12.5px;
            height: 34px;
            padding: 4px 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
        }
    </style>
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">📍 81 İl Reklam & Fiyat Yönetimi</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">Şehir sahiplerini, logolarını, teklif tutarlarını ve tıklamaları düzenleyin</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
                <a href="/admin/users.php" class="stat-pill">Kullanıcılar</a>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="panel" style="overflow-x: auto; padding: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--ink-muted);">
                        <th style="padding: 10px 6px;">Plaka / İl</th>
                        <th style="padding: 10px 6px;">Platform</th>
                        <th style="padding: 10px 6px;">Marka / Başlık</th>
                        <th style="padding: 10px 6px;">Kullanıcı / Domain</th>
                        <th style="padding: 10px 6px;">Logo / Avatar URL</th>
                        <th style="padding: 10px 6px;">Hedef Link</th>
                        <th style="padding: 10px 6px;">Fiyat (₺)</th>
                        <th style="padding: 10px 6px;">Tıklama</th>
                        <th style="padding: 10px 6px; text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cities as $c): ?>
                        <form method="POST" class="edit-row">
                            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                            <input type="hidden" name="city_id" value="<?= $c['id'] ?>">
                            <tr style="border-bottom: 1px solid var(--border); background: <?= $c['current_owner_id'] ? '#ffffff' : '#fbf9f4' ?>;">
                                <td style="padding: 8px 6px; white-space: nowrap;">
                                    <strong style="color: var(--ink);"><?= $c['plate_code'] ?></strong> · <?= htmlspecialchars($c['name']) ?>
                                </td>
                                <td style="padding: 8px 6px;">
                                    <select name="platform" style="width: 95px;">
                                        <option value="site" <?= $c['platform'] === 'site' ? 'selected' : '' ?>>Web Sitesi</option>
                                        <option value="x" <?= $c['platform'] === 'x' ? 'selected' : '' ?>>X</option>
                                        <option value="instagram" <?= $c['platform'] === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                                        <option value="app" <?= $c['platform'] === 'app' ? 'selected' : '' ?>>Uygulama</option>
                                    </select>
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="text" name="display_name" value="<?= htmlspecialchars($c['display_name'] ?? '') ?>" placeholder="Marka Adı" style="width: 120px;">
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="text" name="username" value="<?= htmlspecialchars($c['username'] ?? '') ?>" placeholder="username/domain" style="width: 120px;">
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="url" name="avatar_url" value="<?= htmlspecialchars($c['avatar_url'] ?? '') ?>" placeholder="Logo URL" style="width: 130px;">
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="url" name="target_url" value="<?= htmlspecialchars($c['target_url'] ?? '') ?>" placeholder="https://..." style="width: 130px;">
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="number" name="amount" value="<?= $c['current_amount'] ?>" style="width: 75px; font-weight: 700;">
                                </td>
                                <td style="padding: 8px 6px;">
                                    <input type="number" name="clicks" value="<?= $c['total_clicks'] ?>" style="width: 60px;">
                                </td>
                                <td style="padding: 8px 6px; text-align: right; white-space: nowrap;">
                                    <button type="submit" name="update_city" class="stat-pill" style="cursor: pointer; background: var(--ink); color: #fff; font-weight: 600;">Kaydet</button>
                                    <?php if ($c['current_owner_id']): ?>
                                        <a href="/admin/cities.php?clear_city=<?= $c['id'] ?>&csrf_token=<?= get_csrf_token() ?>" onclick="return confirm('Bu şehri boşaltmak istediğinize emin misiniz?');" class="stat-pill" style="color: #dc2626; margin-left: 4px;">Boşalt</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </form>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
