<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(404);
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 600px; margin: 80px auto; padding: 0 20px; text-align: center;">
    <div class="panel" style="padding: 48px 24px;">
        <div style="font-size: 54px; margin-bottom: 16px;">🔍</div>
        <h1 style="font-family: var(--font-serif); font-size: 28px; color: var(--ink); margin-bottom: 10px;">404 — Sayfa Bulunamadı</h1>
        <p style="font-size: 14.5px; color: var(--ink-muted); margin-bottom: 24px; line-height: 1.5;">
            Aradığınız sayfa silinmiş, adı değiştirilmiş veya geçici olarak kullanım dışı kalmış olabilir.
        </p>
        <a href="/" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 700;">
            <span>Ana Sayfaya Dön</span>
            <span>➔</span>
        </a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
