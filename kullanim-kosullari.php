<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/includes/functions.php';
$stats = get_site_stats();
$company = $stats['company_title'] ?? 'Ulusoy Digital Medya ve Bilişim Teknolojileri A.Ş.';
$email = $stats['contact_email'] ?? 'info@ulusoydigital.com';

$pageTitle = "Kullanım Koşulları & Mesafeli Sözleşme — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 820px; margin: 30px auto 50px; padding: 0 20px;">
    <div class="panel" style="line-height: 1.8; color: var(--ink);">
        <h1 style="font-family: var(--font-serif); font-size: 26px; margin-bottom: 16px;">Kullanım Koşulları & Mesafeli Satış Bilgilendirmesi</h1>
        
        <p>İşbu kullanım şartları, <strong><?= htmlspecialchars($company) ?></strong> tarafından işletilen <?= SITE_NAME ?> platformunu kullanan veya reklam veren tüm gerçek ve tüzel kişiler için bağlayıcıdır.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">1. Reklam ve Teklif Kuralları</h3>
        <ul style="padding-left: 20px; margin-bottom: 12px;">
            <li>Harita üzerindeki şehirler en yüksek teklifi veren reklam verene tahsis edilir.</li>
            <li>Daha yüksek bir teklif verildiğinde mevcut reklam sahibi liderliği kaybeder ve ilgili şehrin "Alt İlanlar" (Teklif Geçmişi) listesinde yer almaya devam eder.</li>
            <li>Yasalara, genel ahlaka aykırı, yasa dışı bahis, kumar veya zararlı içerik barındıran reklamların yayını derhal durdurulur ve üyelik feshedilir.</li>
        </ul>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">2. Ödeme, İptal ve Cayma Hakkı</h3>
        <p>Platformumuz üzerinden gerçekleştirilen reklam alımları 6502 sayılı Tüketicinin Korunması Hakkında Kanun kapsamında <em>"Elektronik ortamda anında ifa edilen hizmetler"</em> niteliğinde olduğundan, onaylanan ve haritada yayına giren reklamlar için cayma hakkı ve iade geçerli değildir.</p>

        <h3 style="font-size: 17px; margin-top: 18px; margin-bottom: 6px;">3. Uyuşmazlıkların Çözümü</h3>
        <p>İşbu sözleşmeden doğabilecek uyuşmazlıklarda Türkiye Cumhuriyeti Mahkemeleri ve İcra Daireleri yetkilidir.</p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
