<?php
require_once __DIR__ . '/includes/functions.php';

$platform = $_GET['p'] ?? '';
$username = $_GET['u'] ?? '';

$owner = get_owner_details($platform, $username);
if (!$owner) {
    header("Location: /");
    exit;
}

$pageTitle = htmlspecialchars($owner['display_name'] ?: '@' . $owner['username']) . " — Billboardum";
require_once __DIR__ . '/includes/header.php';
?>

<main style="max-width: 800px; margin: 0 auto 40px; padding: 0 20px;">
    <div class="panel" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="leader-avatar" style="width: 54px; height: 54px; font-size: 20px; overflow: hidden; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; border: 2px solid var(--border);">
                    <?php 
                    $ownAvatar = (!empty($owner['avatar_url']) && $owner['avatar_url'] !== '/assets/images/default_logo.svg') ? $owner['avatar_url'] : get_default_logo_for_platform($owner['platform'] ?? 'site');
                    ?>
                    <img src="<?= htmlspecialchars($ownAvatar) ?>" alt="<?= htmlspecialchars($owner['display_name'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='<?= get_default_logo_for_platform($owner['platform'] ?? 'site') ?>'" />
                </div>
                <div>
                    <h1 style="font-family: var(--font-serif); font-size: 26px; color: var(--ink);">
                        <?= htmlspecialchars($owner['display_name'] ?: '@' . $owner['username']) ?>
                    </h1>
                    <span style="color: var(--ink-muted); font-size: 13.5px;">
                        <?= get_platform_label($owner['platform']) ?> Reklam Vereni
                    </span>
                </div>
            </div>

            <?php if ($owner['target_url']): ?>
                <a href="<?= htmlspecialchars($owner['target_url']) ?>" target="_blank" rel="noopener" class="btn-bid" style="height: 40px; padding: 0 16px; font-size: 13.5px;">
                    <span>Profile Git</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
            <div style="text-align: center;">
                <div style="font-size: 12px; color: var(--ink-muted);">Sahip Olunan Şehir</div>
                <strong style="font-size: 20px; color: var(--ink);"><?= count($owner['cities']) ?></strong>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 12px; color: var(--ink-muted);">Toplam Harcama</div>
                <strong style="font-size: 20px; color: var(--ink);"><?= format_money($owner['total_spent']) ?></strong>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 12px; color: var(--ink-muted);">Toplam Tıklama</div>
                <strong style="font-size: 20px; color: var(--ink);"><?= format_number($owner['total_clicks']) ?></strong>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2 style="font-size: 18px; margin-bottom: 14px;">Sahip Olunan Şehirler</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
            <?php foreach ($owner['cities'] as $c): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #fff; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                    <div>
                        <div style="font-weight: 700; color: var(--ink);"><?= htmlspecialchars($c['name']) ?></div>
                        <div style="font-size: 12px; color: var(--ink-muted);"><?= format_number($c['total_clicks']) ?> tıklama</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-weight: 700; color: var(--ink);"><?= format_money($c['current_amount']) ?></span>
                        <div><a href="/go.php?city=<?= urlencode($c['slug']) ?>" target="_blank" style="font-size: 11.5px; color: var(--gold); text-decoration: underline;">Ziyaret Et</a></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
