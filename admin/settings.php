<?php
require_once __DIR__ . '/auth_check.php';

$db = get_db();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Güvenlik doğrulaması (CSRF) başarısız oldu.';
    } elseif (isset($_POST['change_password'])) {
        $curr = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        if (!verify_admin_password($curr)) {
            $err = 'Mevcut yönetici şifrenizi hatalı girdiniz!';
        } elseif (strlen($new) < 4) {
            $err = 'Yeni şifreniz en az 4 karakter olmalıdır.';
        } elseif ($new !== $confirm) {
            $err = 'Yeni şifreler birbiriyle eşleşmiyor!';
        } else {
            if (set_admin_password($new)) {
                $msg = 'Yönetici şifreniz başarıyla güncellendi!';
            } else {
                $err = 'Şifre güncellenirken bir hata oluştu.';
            }
        }
    } elseif (isset($_POST['change_secret_key'])) {
        $curr = trim($_POST['admin_pwd_for_key'] ?? '');
        $newKey = trim($_POST['new_secret_key'] ?? '');

        if (!verify_admin_password($curr)) {
            $err = 'Mevcut yönetici şifrenizi hatalı girdiniz!';
        } elseif (strlen($newKey) < 4) {
            $err = 'Gizli giriş anahtarı en az 4 karakter olmalıdır.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $newKey)) {
            $err = 'Gizli anahtar sadece harf, rakam ve tire/alt çizgi içerebilir.';
        } else {
            if (set_admin_secret_key($newKey)) {
                $msg = 'Gizli giriş anahtarı başarıyla güncellendi! Yeni giriş adresiniz: /admin/?key=' . htmlspecialchars($newKey);
            } else {
                $err = 'Gizli anahtar güncellenirken bir hata oluştu.';
            }
        }
    } elseif (isset($_POST['save_settings'])) {
        $ibanBank = trim($_POST['iban_bank'] ?? '');
        $ibanName = trim($_POST['iban_name'] ?? '');
        $ibanNo = trim($_POST['iban_no'] ?? '');
        $stripeEnabled = isset($_POST['stripe_enabled']) ? 1 : 0;
        $stripePk = trim($_POST['stripe_pk'] ?? '');
        $stripeSk = trim($_POST['stripe_sk'] ?? '');
        $companyTitle = trim($_POST['company_title'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');

        $stmt = $db->prepare("
            UPDATE site_stats 
            SET iban_bank = ?, iban_name = ?, iban_no = ?,
                stripe_enabled = ?, stripe_pk = ?, stripe_sk = ?, company_title = ?, contact_email = ?
            WHERE id = 1
        ");
        $stmt->execute([
            $ibanBank, $ibanName, $ibanNo,
            $stripeEnabled, $stripePk, $stripeSk, $companyTitle, $contactEmail
        ]);
        $msg = "Tüm ödeme, şirket ve entegrasyon ayarları başarıyla kaydedildi.";
    }
}

$stats = get_site_stats();
$pendingCount = count(get_pending_bids());
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Sistem, Ödeme & Yasal Ayarlar — Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: var(--bg); padding: 24px 16px;">
    <div style="max-width: 760px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 24px;">⚙️ Sistem, Ödeme & Firma Ayarları</h1>
                <span style="color: var(--ink-muted); font-size: 13px;">IBAN bilgileri, Stripe API anahtarları, KVKK & Firma parametreleri</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/" class="stat-pill">← Panele Dön</a>
                <a href="/admin/payments.php" class="stat-pill" style="background: #0284c7; color: #fff;">
                    Ödeme Onayları (<?= $pendingCount ?>)
                </a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="panel" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

            <!-- IBAN Ödeme Bilgileri -->
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #854d0e; border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                    🏦 Havale / EFT (IBAN) Bilgileri
                </h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Banka Adı</label>
                        <div class="field">
                            <input type="text" name="iban_bank" value="<?= htmlspecialchars($stats['iban_bank'] ?? 'Garanti BBVA') ?>" placeholder="örn: Garanti BBVA" required />
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Alıcı / Hesap Sahibi Ünvanı</label>
                        <div class="field">
                            <input type="text" name="iban_name" value="<?= htmlspecialchars($stats['iban_name'] ?? 'Ulusoy Digital Medya ve Bilişim A.Ş.') ?>" placeholder="örn: Şirket Tam Ünvanı" required />
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">IBAN Numarası</label>
                        <div class="field">
                            <input type="text" name="iban_no" value="<?= htmlspecialchars($stats['iban_no'] ?? '123456789') ?>" placeholder="123456789" required />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stripe Entegrasyon Bilgileri -->
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #1e3a8a; border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                    💳 Stripe (Kredi Kartı) Entegrasyonu
                </h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="stripe_enabled" value="1" <?= !empty($stats['stripe_enabled']) ? 'checked' : '' ?> style="width: 18px; height: 18px;" />
                        <span>Stripe ile Kredi Kartı Ödemesini Aktif Et</span>
                    </label>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Stripe Publishable Key (pk_...)</label>
                        <div class="field">
                            <input type="text" name="stripe_pk" value="<?= htmlspecialchars($stats['stripe_pk'] ?? '') ?>" placeholder="pk_test_... veya pk_live_..." autocomplete="off" />
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Stripe Secret Key (sk_...)</label>
                        <div class="field">
                            <input type="password" name="stripe_sk" value="<?= htmlspecialchars($stats['stripe_sk'] ?? '') ?>" placeholder="sk_test_... veya sk_live_..." autocomplete="off" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Firma & Yasal Bilgiler (KVKK, Gizlilik vb. için) -->
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; color: var(--ink); border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                    📜 Firma & Yasal Sayfa Parametreleri
                </h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Firma Ticari Ünvanı (Sözleşmelerde Görünür)</label>
                        <div class="field">
                            <input type="text" name="company_title" value="<?= htmlspecialchars($stats['company_title'] ?? 'Ulusoy Digital Medya ve Bilişim Teknolojileri A.Ş.') ?>" required />
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">İletişim & Destek E-Postası</label>
                        <div class="field">
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($stats['contact_email'] ?? 'info@ulusoydigital.com') ?>" required />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canlı İstatistikler & Gerçek Zamanlı Sayaç Bilgisi -->
            <div style="background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; padding: 16px;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px; color: #166534; display: flex; align-items: center; gap: 6px;">
                    <span>📊 Canlı Platform Metrikleri (100% Gerçek Veriler)</span>
                </h3>
                <p style="font-size: 12.5px; color: var(--ink-muted); margin-bottom: 14px;">
                    Platform istatistikleri ve pulse ticker veritabanındaki gerçek işlemlerden otomatik olarak hesaplanır. Manuel yapay veri girilemez.
                </p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
                    <div style="background: #fff; border: 1px solid var(--border); padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Gerçek Ziyaretçi</span>
                        <div style="font-size: 18px; font-weight: 800; color: #166534;"><?= number_format($stats['visits'], 0, ',', '.') ?></div>
                    </div>
                    <div style="background: #fff; border: 1px solid var(--border); padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Gerçek Hacim</span>
                        <div style="font-size: 18px; font-weight: 800; color: #0284c7;"><?= number_format($stats['volume'], 0, ',', '.') ?>₺</div>
                    </div>
                    <div style="background: #fff; border: 1px solid var(--border); padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Aktif Şehir</span>
                        <div style="font-size: 18px; font-weight: 800; color: var(--ink);"><?= $stats['active_cities'] ?> / 81</div>
                    </div>
                    <div style="background: #fff; border: 1px solid var(--border); padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase; font-weight: 700;">Toplam Tıklama</span>
                        <div style="font-size: 18px; font-weight: 800; color: #d97706;"><?= number_format($stats['total_clicks'], 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_settings" class="btn-primary" style="height: 46px; font-weight: 700; margin-top: 10px;">
                Ayarları Kaydet
            </button>
        </form>

        <!-- Yönetici Şifresi Değiştirme Paneli -->
        <form method="POST" class="panel" style="display: flex; flex-direction: column; gap: 16px; margin-top: 24px; border: 1.5px solid var(--border);">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                    <span>🔐 Yönetici Şifresini Değiştir</span>
                </h3>
                <p style="font-size: 12.5px; color: var(--ink-muted);">
                    Admin paneline giriş yaparken kullandığınız şifreyi buradan anında güncelleyebilirsiniz.
                </p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Mevcut Şifre</label>
                    <div class="field">
                        <input type="password" name="current_password" placeholder="Mevcut yönetici şifreniz" required autocomplete="current-password" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Yeni Şifre</label>
                        <div class="field">
                            <input type="password" name="new_password" placeholder="En az 4 karakter" required autocomplete="new-password" />
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Yeni Şifre Tekrar</label>
                        <div class="field">
                            <input type="password" name="confirm_password" placeholder="Yeni şifrenizi tekrar yazın" required autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="change_password" class="btn-primary" style="height: 44px; font-weight: 700; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                🔑 Yeni Şifreyi Kaydet ve Güncelle
            </button>
        </form>

        <!-- Gizli Giriş Anahtarı (Admin URL Koruması & Saklama) -->
        <form method="POST" class="panel" style="display: flex; flex-direction: column; gap: 16px; margin-top: 24px; border: 1.5px solid #cbd5e1; background: #f8fafc;">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>🛡️ Gizli Giriş Anahtarı (Admin Girişini Saklama)</span>
                </h3>
                <p style="font-size: 12.5px; color: var(--ink-muted); line-height: 1.5;">
                    Admin paneli yabancılara, tarayıcı botlarına ve saldırganlara karşı <strong>tamamen gizlenmiştir (404 Sayfa Bulunamadı olarak görünür)</strong>. Giriş ekranını açabilmek için tarayıcınıza gizli anahtarınızı eklemeniz gerekir:
                </p>
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; margin-top: 10px; font-family: monospace; font-size: 13px; color: #1e40af; word-break: break-all;">
                    🔗 Giriş URL'iniz: <strong>/admin/?key=<?= htmlspecialchars(get_admin_secret_key()) ?></strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Yeni Gizli Giriş Anahtarı</label>
                    <div class="field">
                        <input type="text" name="new_secret_key" value="<?= htmlspecialchars(get_admin_secret_key()) ?>" placeholder="Örn: gizli_anahtar_2026" required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Mevcut Yönetici Şifreniz (Onay İçin)</label>
                    <div class="field">
                        <input type="password" name="admin_pwd_for_key" placeholder="Mevcut yönetici şifreniz" required autocomplete="current-password" />
                    </div>
                </div>
            </div>

            <button type="submit" name="change_secret_key" class="btn-primary" style="height: 44px; font-weight: 700; background: #0f172a;">
                🛡️ Gizli Giriş Anahtarını Güncelle
            </button>
        </form>
    </div>
</body>
</html>
