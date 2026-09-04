<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/includes/functions.php';
$stats = get_site_stats();
$company = $stats['company_title'] ?? 'Ulusoy Digital Medya ve Bilişim Teknolojileri A.Ş.';
$email = $stats['contact_email'] ?? 'info@ulusoydigital.com';

$pageTitle = "Gizlilik ve Çerez Politikası — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 820px; margin: 30px auto 50px; padding: 0 20px;">
    <div class="panel" style="line-height: 1.8; color: var(--ink);">
        <h1 style="font-family: var(--font-serif); font-size: 26px; margin-bottom: 16px;">Gizlilik ve Çerez Politikası</h1>
        
        <p><?= htmlspecialchars($company) ?> olarak ziyaretçilerimizin ve reklam verenlerimizin mahremiyetine ve kişisel verilerinin güvenliğine en üst düzeyde önem veriyoruz.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">1. Çerezlerin (Cookies) Kullanımı</h3>
        <p>Sitemizde kullanıcı deneyimini iyileştirmek, oturum güvenliğini sağlamak (CSRF koruması) ve harita tıklama istatistiklerini doğru ölçümlemek için zorunlu ve analitik çerezler kullanılmaktadır. Tarayıcı ayarlarınızdan çerezleri dilediğiniz zaman yönetebilirsiniz.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">2. Veri Güvenliği Standartları</h3>
        <p>Sistemimizde endüstri standardı güvenlik önlemleri uygulanmaktadır:</p>
        <ul style="padding-left: 20px; margin-bottom: 12px;">
            <li>Tüm bağlantılar 256-bit SSL/TLS şifreleme altındadır.</li>
            <li>Parolalar tek yönlü güçlü kriptografik özetleme algoritmaları (bcrypt/Argon2) ile korunur.</li>
            <li>Yetkisiz erişim, brute-force (kaba kuvvet) saldırıları ve sahte form gönderimlerine karşı güvenlik duvarı ve hız sınırlamaları aktiftir.</li>
        </ul>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">3. İletişim</h3>
        <p>Gizlilik politikamızla ilgili soru ve talepleriniz için bize <strong><?= htmlspecialchars($email) ?></strong> üzerinden ulaşabilirsiniz.</p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
