<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/includes/functions.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Güvenlik doğrulaması (CSRF) başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } elseif (!check_rate_limit('user_register', 5, 15)) {
        $err = 'Çok fazla kayıt denemesi yapıldı. Lütfen 15 dakika sonra tekrar deneyin.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $res = register_user($name, $email, $pass);
        if ($res['success']) {
            clear_failed_attempts('user_register');
            session_regenerate_id(true);
            header("Location: /panel/");
            exit;
        } else {
            record_failed_attempt('user_register');
            $err = $res['error'];
        }
    }
}

$pageTitle = "Kayıt Ol — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<main style="max-width: 420px; margin: 40px auto; padding: 0 20px;">
    <div class="panel" style="text-align: center;">
        <h1 style="font-family: var(--font-serif); font-size: 24px; margin-bottom: 6px;">Reklam Veren Hesabı Aç</h1>
        <p style="color: var(--ink-muted); font-size: 13.5px; margin-bottom: 20px;">Birkaç saniyede hesabını oluştur</p>

        <?php if ($err): ?>
            <div class="bid-message error" style="margin-bottom: 14px;"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div class="field">
                <input type="text" name="name" placeholder="Adın veya Marka Adın" required />
            </div>
            <div class="field">
                <input type="email" name="email" placeholder="E-posta Adresin" required />
            </div>
            <div class="field">
                <input type="password" name="password" placeholder="Şifren (en az 6 karakter)" minlength="6" required />
            </div>
            <div style="font-size: 11.5px; color: var(--ink-muted); text-align: left; line-height: 1.4;">
                Kayıt olarak <a href="/kullanim-kosullari.php" target="_blank" style="text-decoration: underline;">Kullanım Koşulları</a> ve <a href="/kvkk.php" target="_blank" style="text-decoration: underline;">KVKK Metni</a>'ni kabul etmiş olursunuz.
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">Hesap Oluştur</button>
        </form>

        <div style="margin-top: 18px; font-size: 13px; color: var(--ink-muted);">
            Zaten hesabın var mı? <a href="/giris.php" style="color: var(--ink); font-weight: 700; text-decoration: underline;">Giriş Yap</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
