<?php
$pageTitle = "Toplu Şehir Al — Billboardum";
require_once __DIR__ . '/includes/header.php';
$cities = get_all_cities();
$activePlatform = $_GET['p'] ?? 'instagram';
?>

<main style="max-width: 960px; margin: 0 auto 40px; padding: 0 20px;">
    <section class="hero" style="margin-bottom: 24px;">
        <h1>Toplu <em>şehir al</em></h1>
        <p class="lede">Haritayı tek seferde kapat, Türkiye genelinde reklamını yap.</p>
    </section>

    <form id="bulk-form" class="panel bulk-form" method="POST" action="/api.php">
        <input type="hidden" name="cities" id="bulk-selected-cities" value="" />

        <fieldset style="border: none; margin-bottom: 20px;">
            <legend style="font-weight: 700; font-size: 16px; margin-bottom: 12px; color: var(--ink);">1. Kimliğin ve Platform</legend>
            <div class="seg" role="radiogroup" aria-label="Listeleme türü">
                <label class="<?= $activePlatform === 'instagram' ? 'active' : '' ?>">
                    <input type="radio" name="platform" value="instagram" <?= $activePlatform === 'instagram' ? 'checked' : '' ?> />
                    <span class="tx">Instagram</span>
                </label>
                <label class="<?= $activePlatform === 'x' ? 'active' : '' ?>">
                    <input type="radio" name="platform" value="x" <?= $activePlatform === 'x' ? 'checked' : '' ?> />
                    <span class="tx">X</span>
                </label>
                <label class="<?= $activePlatform === 'app' ? 'active' : '' ?>">
                    <input type="radio" name="platform" value="app" <?= $activePlatform === 'app' ? 'checked' : '' ?> />
                    <span class="tx">Uygulama</span>
                </label>
                <label class="<?= $activePlatform === 'site' ? 'active' : '' ?>">
                    <input type="radio" name="platform" value="site" <?= $activePlatform === 'site' ? 'checked' : '' ?> />
                    <span class="tx">Web Sitesi</span>
                </label>
            </div>

            <div class="field user" style="margin-top: 12px;">
                <input type="text" name="username" placeholder="Hesap veya site adresin" required autocomplete="off" />
            </div>
        </fieldset>

        <fieldset style="border: none; margin-bottom: 20px;">
            <legend style="font-weight: 700; font-size: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <span>2. Şehirleri Seç</span>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="stat-pill" id="btn-select-all" style="cursor: pointer;">Tümünü Seç (81 İl)</button>
                    <button type="button" class="stat-pill" id="btn-deselect-all" style="cursor: pointer;">Temizle</button>
                </div>
            </legend>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; max-height: 380px; overflow-y: auto; padding: 6px; background: #fff; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                <?php 
                $processed = [];
                foreach ($cities as $c): 
                    $slug = ($c['slug'] === 'istanbul-avrupa' || $c['slug'] === 'istanbul-asya') ? 'istanbul' : $c['slug'];
                    if (isset($processed[$slug])) continue;
                    $processed[$slug] = true;
                    $minPrice = $c['current_amount'] + 5;
                ?>
                    <div class="bulk-city-item" data-slug="<?= htmlspecialchars($slug) ?>" data-min-price="<?= $minPrice ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; user-select: none; font-size: 13px; transition: all 0.1s ease;">
                        <span style="font-weight: 600;"><?= htmlspecialchars($c['name']) ?></span>
                        <span style="font-weight: 700; color: var(--ink-muted);"><?= format_money($minPrice) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- Summary & Submit -->
        <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <span style="color: var(--ink-muted); font-size: 13px;">Seçilen Şehir:</span>
                <strong id="bulk-total-count" style="font-size: 18px; margin-left: 6px;">0</strong>
            </div>
            <div>
                <span style="color: var(--ink-muted); font-size: 13px;">Toplam Tutar:</span>
                <strong id="bulk-total-amount" style="font-size: 22px; color: var(--ink); margin-left: 6px;">0₺</strong>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%;">Toplu Satın Alımı Tamamla</button>
    </form>
</main>

<style>
.bulk-city-item.selected {
    background: var(--accent-bg) !important;
    border-color: #a3b88c !important;
    color: #233519 !important;
}
.bulk-city-item.selected span:last-child {
    color: #233519 !important;
}
</style>

<?php 
$extraScript = '<script src="/assets/js/bulk.js"></script>';
include __DIR__ . '/includes/footer.php'; 
?>
