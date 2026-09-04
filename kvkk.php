<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/includes/functions.php';
$stats = get_site_stats();
$company = $stats['company_title'] ?? 'Ulusoy Digital Medya ve Bilişim Teknolojileri A.Ş.';
$email = $stats['contact_email'] ?? 'info@ulusoydigital.com';

$pageTitle = "KVKK Aydınlatma Metni — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 820px; margin: 30px auto 50px; padding: 0 20px;">
    <div class="panel" style="line-height: 1.8; color: var(--ink);">
        <h1 style="font-family: var(--font-serif); font-size: 26px; margin-bottom: 16px;">6698 Sayılı KVKK Uyarınca Aydınlatma Metni</h1>
        
        <p><strong>Veri Sorumlusu:</strong> <?= htmlspecialchars($company) ?> (Bundan böyle "Platform" olarak anılacaktır.)</p>
        <p>İşbu Aydınlatma Metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, veri sorumlusu sıfatıyla Platformumuz tarafından toplanan, işlenen ve saklanan kişisel verileriniz hakkında sizleri bilgilendirmek amacıyla hazırlanmıştır.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">1. İşlenen Kişisel Verileriniz</h3>
        <p>Platformumuz üzerinden reklam verme, üyelik oluşturma veya teklif iletme süreçlerinde aşağıdaki verileriniz işlenmektedir:</p>
        <ul style="padding-left: 20px; margin-bottom: 12px;">
            <li><strong>Kimlik ve İletişim Bilgileri:</strong> Ad, soyad, e-posta adresi.</li>
            <li><strong>Reklam ve Profil Bilgileri:</strong> Marka adı, web sitesi, sosyal medya kullanıcı adı, profil/logo görseli.</li>
            <li><strong>İşlem Güvenliği Bilgileri:</strong> IP adresi, oturum çerezleri, giriş ve tıklama zaman damgaları.</li>
            <li><strong>Finansal Bilgiler:</strong> Banka havalesi dekont/referans bilgisi (Kredi kartı bilgileri sistemimizde saklanmaz, doğrudan PCI-DSS uyumlu lisanslı ödeme kuruluşlarınca işlenir).</li>
        </ul>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">2. Kişisel Verilerin İşlenme Amaçları</h3>
        <p>Toplanan kişisel verileriniz; harita üzerinde reklam yayınının gerçekleştirilmesi, teklif sıralamasının belirlenmesi, faturalandırma ve yasal yükümlülüklerin ifası ile siber güvenlik denetimlerinin sağlanması amaçlarıyla işlenir.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">3. Haklarınız</h3>
        <p>KVKK'nın 11. maddesi uyarınca dilediğiniz zaman <strong><?= htmlspecialchars($email) ?></strong> adresine başvurarak verilerinizin işlenip işlenmediğini öğrenme, düzeltilmesini veya silinmesini talep etme hakkına sahipsiniz.</p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
