<?php
require_once __DIR__ . '/includes/functions.php';

$page = $_GET['p'] ?? 'kunye';
$titles = [
    'kunye' => 'Künye ve İletişim',
    'iptal' => 'İptal, İade ve Cayma Hakkı',
    'gizlilik' => 'Gizlilik ve Çerez Politikası',
    'kvkk' => 'KVKK Aydınlatma Metni',
    'on-bilgi' => 'Ön Bilgilendirme Formu',
    'kullanim' => 'Kullanım Koşulları',
    'mesafeli' => 'Mesafeli Satış Sözleşmesi'
];

$pageTitle = ($titles[$page] ?? 'Yasal Bilgiler') . " — Billboardum";
require_once __DIR__ . '/includes/header.php';
?>

<main style="max-width: 800px; margin: 0 auto 40px; padding: 0 20px;">
    <div class="panel">
        <h1 style="font-family: var(--font-serif); font-size: 28px; margin-bottom: 18px; color: var(--ink);">
            <?= htmlspecialchars($titles[$page] ?? 'Bilgilendirme') ?>
        </h1>

        <div style="line-height: 1.7; color: var(--ink);">
            <?php if ($page === 'kunye'): ?>
                <p><strong><?= SITE_NAME ?>.com</strong> — Türkiye'nin dijital harita billboard platformu.</p>
                <p style="margin-top: 12px;"><strong>E-Posta:</strong> support@billboardum.com</p>
                <p><strong>Adres:</strong> Türkiye</p>
            <?php elseif ($page === 'iptal'): ?>
                <p>Platformumuz üzerinden yapılan teklif ve reklam alımları anında dijital teslimat niteliğinde olup, 6502 sayılı Tüketicinin Korunması Hakkında Kanun kapsamında anında ifa edilen hizmetler kategorisindedir.</p>
            <?php elseif ($page === 'gizlilik'): ?>
                <p><?= SITE_NAME ?>.com olarak kullanıcılarımızın gizliliğine saygı duyuyoruz. Çerezler yalnızca site performansını artırmak ve ziyaretçi istatistiklerini takip etmek amacıyla kullanılır.</p>
            <?php elseif ($page === 'kvkk'): ?>
                <p>Kişisel Verilerin Korunması Kanunu (KVKK) uyarınca reklam verenlerin belirttikleri açık profil bilgileri harita üzerinde reklam amacı doğrultusunda işlenmektedir.</p>
            <?php else: ?>
                <p><?= SITE_NAME ?>.com hizmet şartları ve kuralları gereği platform üzerinden verilen reklamların yasalara ve genel ahlaka uygun olması zorunludur.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
