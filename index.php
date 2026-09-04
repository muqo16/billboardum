<?php
session_start();
require_once __DIR__ . '/includes/header.php';

$currentTab = $_GET['tab'] ?? 'all';
$validTabs = ['all', 'site', 'app', 'instagram', 'youtube', 'x', 'facebook'];
if (!in_array($currentTab, $validTabs)) {
    $currentTab = 'all';
}

$stats = get_site_stats();
$leaderboard = get_leaderboard(100);
$recentBids = get_recent_bids(12);

// Default top leader for initial bottom bar
$topLeader = !empty($leaderboard) ? $leaderboard[0] : null;
?>

<main style="display: flex; flex-direction: column; align-items: center;">

    <!-- Hero Title & Fast Claim Box -->
    <section class="hero-outbid" style="margin-bottom: 24px;">
        <h1 class="hero-title" style="margin-bottom: 6px;">
            Türkiye'nin <em>Reklam Vitrini</em>
        </h1>
        <p class="hero-sub" style="margin-bottom: 20px;">
            Şehrini seç, en yüksek teklifle <strong>zirvenin sahibi</strong> ol.
        </p>

        <!-- Seamless Quick Claim Box -->
        <form action="/reklam-ver.php" method="GET" style="display: flex; max-width: 560px; width: 100%; margin: 0 auto; gap: 8px; background: #fff; padding: 6px; border-radius: 12px; border: 1.5px solid var(--border); box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
            <input type="text" name="u" placeholder="Web siten, uygulaman veya sosyal medyan..." style="flex: 1; border: none; outline: none; padding: 0 14px; font-size: 14.5px; font-weight: 500; background: transparent; color: var(--ink);" autocomplete="off" />
            <button type="submit" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                Şehrini Kap 🚀
            </button>
        </form>
    </section>

    <!-- 81-City Interactive Turkey Map Container -->
    <section class="mapwrap">
        <div class="map-container" id="map-container-main">
            <!-- Map Zoom & Fullscreen Toolbar -->
            <div class="map-tools">
                <button type="button" class="map-tool-btn" id="btn-zoom-in" title="Yakınlaştır (+)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                <button type="button" class="map-tool-btn" id="btn-zoom-out" title="Uzaklaştır (-)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                <button type="button" class="map-tool-btn" id="btn-zoom-reset" title="Haritayı Sıfırla / Ortala">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                </button>
                <button type="button" class="map-tool-btn" id="btn-fullscreen-toggle" title="Haritayı Büyüt / Tam Ekran Yap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-expand"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-compress" style="display:none;"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg>
                </button>
            </div>

            <div class="map-stage" id="map-stage">
                <div class="map-avatar-pins-layer" id="map-avatar-pins-layer"></div>
                <?php include __DIR__ . '/includes/turkey_map.svg.php'; ?>
            </div>

            <!-- Fullscreen Hint and Exit Bar (Matches Competitor Style) -->
            <div class="map-fullscreen-bottom-bar" id="map-fs-bar" style="display: none;">
                <span>🖱️ Sürükleyerek gez · Çift tıkla veya tekerlekle yakınlaştır · Esc ile çık</span>
                <button type="button" id="btn-fs-exit" class="btn-fs-exit">✕ Tam Ekrandan Çık</button>
            </div>
        </div>
    </section>

    <!-- Bottom Leader & Selected City Bar (matching user screenshot) -->
    <div class="city-leader-bar-wrap">
        <div class="city-leader-bar" id="city-leader-bar" title="İl detaylarını ve alt ilanları görmek için tıklayın">
            <div class="cl-left">
                <span class="cl-rank">#1</span>
                <div class="cl-avatar-wrap">
                    <img src="<?= htmlspecialchars($topLeader['avatar_url'] ?? 'https://api.dicebear.com/7.x/identicon/svg?seed=cagri') ?>" alt="Leader" class="cl-avatar" />
                    <span class="cl-badge">🌐</span>
                </div>
                <div class="cl-details">
                    <div class="cl-brand-line">
                        <span class="cl-brand-name"><?= htmlspecialchars($topLeader['display_name'] ?? 'Çağrı Online Market') ?></span>
                        <span class="cl-info-ic">i</span>
                    </div>
                    <div class="cl-where-line">
                        <span>İstanbul</span> <span class="cl-crown">👑</span> <span>34</span> <span>+42 şehir</span>
                    </div>
                </div>
            </div>
            <div class="cl-right">
                <span class="cl-amount"><?= number_format($topLeader['total_amount'] ?? 6315, 0, ',', '.') ?>₺</span>
                <span class="cl-clicks">👁️ <?= number_format($topLeader['total_clicks'] ?? 1318, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- Live Ticker Strip: Aynı Hizada Kesintisiz Slayt & Kaydıraksız -->
    <?php if (!empty($recentBids)): 
        // Marquee'nin kesintisiz sonsuz döngüde akması için liste uzunluğunu en az 6-8 adede tamamlayıp iki kopya yapıyoruz
        $tickerPool = $recentBids;
        while (count($tickerPool) < 6) {
            $tickerPool = array_merge($tickerPool, $recentBids);
        }
    ?>
        <div class="live-ticker-container">
            <div class="live-ticker-badge">
                <span class="live-ticker-dot"></span>
                <span class="full-txt">Son Teklifler & Alımlar</span>
                <span class="short-txt">Son Alımlar</span>
            </div>
            <div class="live-ticker-slider-wrapper">
                <div class="live-ticker-track">
                    <!-- 1. Döngü -->
                    <?php foreach ($tickerPool as $rb): 
                        $tAvatar = (!empty($rb['avatar_url']) && $rb['avatar_url'] !== '/assets/images/default_logo.svg') ? $rb['avatar_url'] : get_default_logo_for_platform($rb['platform'] ?? 'site');
                    ?>
                        <a href="/sahip.php?p=<?= urlencode($rb['platform']) ?>&u=<?= urlencode($rb['username']) ?>" class="ticker-chip">
                            <img src="<?= htmlspecialchars($tAvatar) ?>" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;" alt="<?= htmlspecialchars($rb['display_name']) ?>" />
                            <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);"><?= htmlspecialchars($rb['display_name'] ?: $rb['username']) ?></span>
                            <span style="font-size: 11.5px; color: var(--ink-muted);"><?= htmlspecialchars($rb['city_name']) ?></span>
                            <span style="font-size: 12px; font-weight: 800; color: #9f1249; background: #fce7ef; padding: 2px 8px; border-radius: 999px; border: 1px solid #f8b4cb;"><?= number_format($rb['amount'], 0, ',', '.') ?>₺</span>
                        </a>
                    <?php endforeach; ?>
                    <!-- 2. Döngü (Kusursuz Sonsuz Akış İçin Birebir Kopya) -->
                    <?php foreach ($tickerPool as $rb): 
                        $tAvatar = (!empty($rb['avatar_url']) && $rb['avatar_url'] !== '/assets/images/default_logo.svg') ? $rb['avatar_url'] : get_default_logo_for_platform($rb['platform'] ?? 'site');
                    ?>
                        <a href="/sahip.php?p=<?= urlencode($rb['platform']) ?>&u=<?= urlencode($rb['username']) ?>" class="ticker-chip" aria-hidden="true" tabindex="-1">
                            <img src="<?= htmlspecialchars($tAvatar) ?>" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;" alt="" />
                            <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);"><?= htmlspecialchars($rb['display_name'] ?: $rb['username']) ?></span>
                            <span style="font-size: 11.5px; color: var(--ink-muted);"><?= htmlspecialchars($rb['city_name']) ?></span>
                            <span style="font-size: 12px; font-weight: 800; color: #9f1249; background: #fce7ef; padding: 2px 8px; border-radius: 999px; border: 1px solid #f8b4cb;"><?= number_format($rb['amount'], 0, ',', '.') ?>₺</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Zirve Liderlik Sıralaması -->
    <section class="leaderboard-section-wrap" id="zirve" style="margin-top: 10px;">
        <div class="leaderboard-header">
            <div class="lh-left">
                <h2>🏆 Zirve Sıralaması</h2>
                <p>Türkiye haritasında en çok şehir kapatan ve en yüksek teklifi veren liderler</p>
            </div>
            <div class="filter-tabs" id="leaderboard-filter-tabs">
                <a href="#zirve" data-tab="all" class="tab-btn <?= ($currentTab ?? 'all') === 'all' ? 'active' : '' ?>">Tümü</a>
                <a href="#zirve" data-tab="site" class="tab-btn <?= ($currentTab ?? '') === 'site' ? 'active' : '' ?>">Siteler</a>
                <a href="#zirve" data-tab="app" class="tab-btn <?= ($currentTab ?? '') === 'app' ? 'active' : '' ?>">Uygulamalar</a>
                <a href="#zirve" data-tab="instagram" class="tab-btn <?= ($currentTab ?? '') === 'instagram' ? 'active' : '' ?>">Instagram</a>
                <a href="#zirve" data-tab="youtube" class="tab-btn <?= ($currentTab ?? '') === 'youtube' ? 'active' : '' ?>">YouTube</a>
                <a href="#zirve" data-tab="x" class="tab-btn <?= ($currentTab ?? '') === 'x' ? 'active' : '' ?>">X</a>
                <a href="#zirve" data-tab="facebook" class="tab-btn <?= ($currentTab ?? '') === 'facebook' ? 'active' : '' ?>">Facebook</a>
            </div>
        </div>

        <div class="outbid-table-wrap">
            <div class="outbid-table">
                <?php 
                $rank = 1;
                foreach ($leaderboard as $lead): 
                    $trophy = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                    $pIcon = $lead['platform'] === 'x' ? '𝕏' : ($lead['platform'] === 'instagram' ? '📷' : ($lead['platform'] === 'app' ? '📱' : ($lead['platform'] === 'youtube' ? '📺' : ($lead['platform'] === 'facebook' ? '👥' : '🌐'))));
                    $avatar = (!empty($lead['avatar_url']) && $lead['avatar_url'] !== '/assets/images/default_logo.svg') ? $lead['avatar_url'] : get_default_logo_for_platform($lead['platform'] ?? 'site');
                ?>
                    <div class="outbid-row" data-platform="<?= htmlspecialchars($lead['platform'] ?? 'site') ?>">
                        <div class="col-rank">
                            <?php if ($trophy): ?>
                                <span class="trophy"><?= $trophy ?></span>
                            <?php else: ?>
                                <span class="rank-num">#<?= $rank ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="col-avatar">
                            <div class="avatar-ring">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($lead['display_name']) ?>" class="avatar-img" />
                                <span class="avatar-badge"><?= $pIcon ?></span>
                            </div>
                        </div>

                        <div class="col-info">
                            <div class="brand-row">
                                <a href="/sahip.php?p=<?= urlencode($lead['platform']) ?>&u=<?= urlencode($lead['username']) ?>" class="brand-name">
                                    <?= htmlspecialchars($lead['display_name'] ?: $lead['username']) ?>
                                </a>
                                <span class="platform-tag"><?= strtoupper($lead['platform']) ?></span>
                            </div>
                            <div class="cities-tag-list">
                                <span class="city-count-pill"><?= $lead['cities_count'] ?> İl</span>
                                <span class="city-names-txt"><?= htmlspecialchars($lead['owned_city_names'] ?? '') ?></span>
                            </div>
                        </div>

                        <div class="col-stats">
                            <div class="stat-block">
                                <span class="stat-lbl">Toplam Teklif</span>
                                <span class="stat-val amount-val"><?= number_format($lead['total_amount'], 0, ',', '.') ?>₺</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-lbl">Tıklamalar</span>
                                <span class="stat-val clicks-val"><?= number_format($lead['total_clicks'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <div class="col-action">
                            <?php if (!empty($lead['target_url'])): ?>
                                <a href="<?= htmlspecialchars($lead['target_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-outlink" title="Dış bağlantıya git">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            <?php endif; ?>
                            <a href="/reklam-ver.php" class="btn-outbid-row">
                                <span>Şehir Kap ⚡</span>
                            </a>
                        </div>
                    </div>
                <?php 
                    $rank++;
                endforeach; 
                ?>
            </div>

            <div id="leaderboard-empty" style="display: none; padding: 48px 24px; text-align: center; background: #fff; border-radius: 12px; margin: 12px 0;">
                <div style="font-size: 38px; margin-bottom: 12px;">🌟</div>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 6px;">Bu kategoride henüz lider bulunmuyor</h3>
                <p style="font-size: 13.5px; color: var(--ink-muted); margin-bottom: 16px;">Hemen şehrini kap, bu kategorinin ilk ve tek lideri sen ol!</p>
                <a href="/reklam-ver.php" class="btn-cta" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 13.5px; font-weight: 700; border-radius: 8px; text-decoration: none;">Hemen İlan Ver 🚀</a>
            </div>
        </div>
    </section>

</main>

<?php 
include __DIR__ . '/includes/city_modal.php';
include __DIR__ . '/includes/footer.php'; 
?>
