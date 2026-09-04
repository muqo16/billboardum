<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/includes/functions.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Güvenlik doğrulaması (CSRF) başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } elseif (!check_rate_limit('user_login', 5, 15)) {
        $err = 'Çok fazla hatalı giriş denemesi! Hesabınızın güvenliği için 15 dakika boyunca giriş kilitlendi.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $res = login_user($email, $pass);
        if ($res['success']) {
            clear_failed_attempts('user_login');
            session_regenerate_id(true);
            header("Location: /panel/");
            exit;
        } else {
            record_failed_attempt('user_login');
            $err = $res['error'];
        }
    }
}

$pageTitle = "Giriş Yap — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 420px; margin: 40px auto; padding: 0 20px;">
    <div class="panel" style="text-align: center;">
        <h1 style="font-family: var(--font-serif); font-size: 24px; margin-bottom: 6px;">Reklam Veren Girişi</h1>
        <p style="color: var(--ink-muted); font-size: 13.5px; margin-bottom: 20px;">Şehirlerini ve reklam istatistiklerini güvenle yönet</p>

        <?php if ($err): ?>
            <div class="bid-message error" style="margin-bottom: 14px;"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div class="field">
                <input type="email" name="email" placeholder="E-posta Adresin" required autocomplete="email" autofocus />
            </div>
            <div class="field">
                <input type="password" name="password" placeholder="Şifren" required autocomplete="current-password" />
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">Güvenli Giriş Yap</button>
        </form>

        <div style="margin-top: 18px; font-size: 13px; color: var(--ink-muted);">
            Henüz hesabın yok mu? <a href="/kayit.php" style="color: var(--ink); font-weight: 700; text-decoration: underline;">Kayıt Ol</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
