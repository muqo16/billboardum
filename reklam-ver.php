<?php
require_once __DIR__ . '/config.php';
session_start();
$pageTitle = "Reklam Ver & Şehir Kap — " . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
$cities = get_all_cities();
$selectedCitySlug = $_GET['city'] ?? '';
$currentUser = get_current_user_info();
$stats = get_site_stats();
?>

<main style="max-width: 720px; margin: 0 auto 50px; padding: 0 20px;">
    <div class="panel form-panel">
        <div style="text-align: center; margin-bottom: 24px;">
            <span class="stat-pill" style="margin-bottom: 8px; display: inline-block;">✨ Reklam Verme & Şehir Kapma</span>
            <h1 style="font-family: var(--font-serif); font-size: 28px; color: var(--ink); margin-bottom: 6px;">Şehrini Seç, Reklamını Yayınla</h1>
            <p style="color: var(--ink-muted); font-size: 14px;">Bilgilerini doldur, ödeme yöntemini seç ve Türkiye haritasında yerini al.</p>
        </div>

        <form id="ad-submit-form" method="POST" action="/api.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="place_ad" />
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>" />

            <!-- Step 1: City Selection -->
            <div class="form-group">
                <label class="form-label">1. Hangi Şehri Almak İstiyorsun?</label>
                <div class="field">
                    <select name="city" id="ad-city-select" required>
                        <option value="" disabled <?= empty($selectedCitySlug) ? 'selected' : '' ?>>Şehir seçiniz...</option>
                        <?php 
                        $processed = [];
                        foreach ($cities as $c): 
                            $slug = ($c['slug'] === 'istanbul-avrupa' || $c['slug'] === 'istanbul-asya') ? 'istanbul' : $c['slug'];
                            if (isset($processed[$slug])) continue;
                            $processed[$slug] = true;
                            $isOwned = !empty($c['current_owner_id']);
                            $ownerTxt = $isOwned ? (' — Sahibi: @' . ltrim($c['username'], '@') . ' · ' . number_format($c['current_amount'], 0, ',', '.') . '₺') : ' — Boş (10₺)';
                            $minBid = $isOwned ? ($c['current_amount'] + 5) : 10;
                            $isSelected = ($selectedCitySlug === $slug);
                        ?>
                            <option value="<?= htmlspecialchars($slug) ?>" data-min="<?= $minBid ?>" data-current="<?= $c['current_amount'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?><?= $ownerTxt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="city-bid-hint" class="field-hint"></div>
            </div>

            <!-- Step 2: Platform Selection -->
            <div class="form-group">
                <label class="form-label">2. Platform / Reklam Türü</label>
                <div class="seg" role="radiogroup" style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <label class="active" data-platform="site">
                        <input type="radio" name="platform" value="site" checked />
                        <span class="tx">🌐 Web Sitesi</span>
                    </label>
                    <label data-platform="app">
                        <input type="radio" name="platform" value="app" />
                        <span class="tx">📱 Mobil Uygulama</span>
                    </label>
                    <label data-platform="instagram">
                        <input type="radio" name="platform" value="instagram" />
                        <span class="tx">📷 Instagram</span>
                    </label>
                    <label data-platform="youtube">
                        <input type="radio" name="platform" value="youtube" />
                        <span class="tx">📺 YouTube</span>
                    </label>
                    <label data-platform="x">
                        <input type="radio" name="platform" value="x" />
                        <span class="tx">𝕏 X (Twitter)</span>
                    </label>
                    <label data-platform="facebook">
                        <input type="radio" name="platform" value="facebook" />
                        <span class="tx">👥 Facebook</span>
                    </label>
                </div>

                <!-- Dynamic Platform Info Box -->
                <div id="platform-info-box" style="margin-top: 10px; padding: 12px 14px; border-radius: 10px; font-size: 12.5px; line-height: 1.5; background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; display: flex; align-items: flex-start; gap: 10px; transition: all 0.2s ease;">
                    <span id="platform-info-icon" style="font-size: 20px; line-height: 1;">🌐</span>
                    <div>
                        <strong id="platform-info-title" style="display: block; font-size: 13.5px; margin-bottom: 2px;">Web Sitesi Reklamı Seçildi</strong>
                        <span id="platform-info-desc">Ziyaretçiler haritada ilinize tıkladığında doğrudan web sitenize yönlendirilir. Ana sayfada "Siteler" sekmesinde listelenirsiniz.</span>
                    </div>
                </div>
            </div>

            <!-- Step 3: Brand & URL -->
            <div class="form-group">
                <label class="form-label" id="step3-heading">3. Web Sitesi Reklam Bilgileri</label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label id="lbl-display-name" style="font-size: 12px; font-weight: 700; color: var(--ink-muted); margin-bottom: 4px; display: block;">Marka veya Firma Adı</label>
                        <div class="field">
                            <input type="text" name="display_name" id="input-display-name" placeholder="Marka veya Firma Adı (örn: Ulusoy Digital)" required autocomplete="off" />
                        </div>
                    </div>
                    <div>
                        <label id="lbl-username" style="font-size: 12px; font-weight: 700; color: var(--ink-muted); margin-bottom: 4px; display: block;">Alan Adı / Domain</label>
                        <div class="field">
                            <input type="text" name="username" id="input-username" placeholder="Alan Adı (örn: ulusoydigital.com)" required autocomplete="off" oninput="handleUsernameInput(this.value)" />
                        </div>
                    </div>
                    <div>
                        <label id="lbl-target-url" style="font-size: 12px; font-weight: 700; color: var(--ink-muted); margin-bottom: 4px; display: block;">Tıklanınca Gidilecek Web Linki</label>
                        <div class="field">
                            <input type="url" name="target_url" id="input-target-url" placeholder="https://ulusoydigital.com" required autocomplete="off" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Marka Logosu (Kendi Logonuz veya Standart Logo) -->
            <div class="form-group">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>4. Marka Logosu</span>
                    <span style="font-size: 11.5px; color: #8c826f; font-weight: normal;">(Yüklerseniz kendi logonuz, yüklemezseniz standart logo çıkar)</span>
                </label>
                <div style="background: #fffdf8; border: 1.5px dashed #ebdcb9; border-radius: 12px; padding: 14px; display: flex; gap: 16px; align-items: center;">
                    <!-- Live Preview Box -->
                    <div style="position: relative; width: 68px; height: 68px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #2b2720; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
                        <img id="logo-preview-img" src="/assets/images/default_site.svg" alt="Logo Önizleme" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/assets/images/default_site.svg'" />
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <label style="background: #2b2720; color: #fff; padding: 7px 15px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.15s;">
                                <span>📁 Logo Dosyası Seç</span>
                                <input type="file" name="logo_file" id="ad-logo-file" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display: none;" onchange="previewSelectedFile(this)" />
                            </label>
                            <span id="file-chosen-name" style="font-size: 12px; color: var(--ink-muted); font-weight: 600;">Standart logo seçili</span>
                        </div>
                        <div class="field" style="margin-top: 2px;">
                            <input type="url" name="avatar_url" id="ad-avatar-url" placeholder="veya İnternet Üzerinden Logo Linki Yapıştır (https://...)" autocomplete="off" oninput="previewUrlLogo(this.value)" />
                        </div>
                        <div style="font-size: 11.5px; color: #854d0e; line-height: 1.4;">
                            ℹ️ <em>Logo yüklerseniz haritada doğrudan kendi logonuz görünür. Boş bırakırsanız seçtiğiniz platformun (Web Sitesi, Instagram, X vb.) şık standart logosu otomatik atanır.</em>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Bid Amount -->
            <div class="form-group">
                <label class="form-label">5. Teklif Tutarı (₺)</label>
                <div class="field money">
                    <input type="number" name="amount" id="ad-amount-input" min="10" step="1" required />
                    <span class="tl">₺</span>
                </div>
            </div>

            <!-- Step 6: Payment Method (IBAN & Stripe) -->
            <div class="form-group">
                <label class="form-label">6. Ödeme Yöntemi</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <label id="lbl-pay-iban" style="background: #fbf7ec; border: 2px solid var(--ink); border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="payment_method" value="iban" checked onchange="togglePaymentBox(this.value)" />
                        <div>
                            <strong style="font-size: 13.5px; display: block;">🏦 Banka Havalesi / IBAN</strong>
                            <span style="font-size: 11.5px; color: var(--ink-muted);">Admin onayından sonra yayında</span>
                        </div>
                    </label>

                    <label id="lbl-pay-stripe" style="background: #fff; border: 1.5px solid var(--border); border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="payment_method" value="stripe" onchange="togglePaymentBox(this.value)" />
                        <div>
                            <strong style="font-size: 13.5px; display: block;">💳 Kredi Kartı / Stripe</strong>
                            <span style="font-size: 11.5px; color: var(--ink-muted);">Anında otomatik onay</span>
                        </div>
                    </label>
                </div>

                <!-- IBAN Details Box -->
                <div id="iban-info-box" style="background: #fffdf8; border: 1.5px solid #ebdcb9; border-radius: 12px; padding: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 8px; color: #854d0e;">Banka Havale / EFT Bilgileri:</h4>
                    <div style="font-size: 13px; line-height: 1.6;">
                        <div><strong>Banka:</strong> <?= htmlspecialchars($stats['iban_bank'] ?? 'Garanti BBVA') ?></div>
                        <div><strong>Alıcı:</strong> <?= htmlspecialchars($stats['iban_name'] ?? 'Ulusoy Digital Medya ve Bilişim A.Ş.') ?></div>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <strong>IBAN:</strong> 
                            <code style="background: #ede6d6; padding: 2px 6px; border-radius: 4px; font-weight: 700;"><?= htmlspecialchars($stats['iban_no'] ?? '123456789') ?></code>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 14px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #854d0e; display: block; margin-bottom: 4px;">👤 Havale / EFT Gönderen Adı Soyadı (Hesap Sahibi) *</label>
                            <div class="field">
                                <input type="text" name="sender_name" id="input-sender-name" placeholder="Örn: Muzaffer Ulusoy" required autocomplete="name" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #854d0e; display: block; margin-bottom: 4px;">📞 Telefon Numarası (Admin Teyidi İçin) *</label>
                            <div class="field">
                                <input type="tel" name="sender_phone" id="input-sender-phone" placeholder="Örn: 0532 123 45 67" required autocomplete="tel" />
                            </div>
                            <span style="font-size: 11px; color: var(--ink-muted);">Sahte dekont ve hatalı bildirimlerin önüne geçmek için admin teyidi amacıyla gereklidir.</span>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #854d0e; display: block; margin-bottom: 4px;">🧾 Dekont No / İşlem Referans Kodu *</label>
                            <div class="field">
                                <input type="text" name="receipt_no" id="input-receipt-no" placeholder="Örn: Garanti Dekont No: 123456789" required />
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: #854d0e; display: block; margin-bottom: 4px;">📄 Dekont Belgesi / Fotoğrafı Yükle (Hızlı Onay Sağlar)</label>
                            <div class="field">
                                <input type="file" name="receipt_file" id="input-receipt-file" accept=".pdf,image/png,image/jpeg,image/webp" />
                            </div>
                            <span style="font-size: 11px; color: var(--ink-muted);">PDF, JPG veya PNG formatında banka dekontunuzu yükleyebilirsiniz.</span>
                        </div>
                    </div>
                    <div style="font-size: 11.5px; color: var(--ink-muted); margin-top: 10px; background: #fff8eb; padding: 8px 10px; border-radius: 6px; border: 1px dashed #d97706;">
                        * Havaleniz admin panelinde gönderen adı, telefon ve dekont numarası ile kontrol edildikten sonra haritada anında yayına alınacaktır.
                    </div>
                </div>

                <!-- Stripe Info Box -->
                <div id="stripe-info-box" style="display: none; background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 8px; color: #0369a1;">Kredi Kartı ile Güvenli Ödeme:</h4>
                    <p style="font-size: 12.5px; color: #0c4a6e; margin-bottom: 8px;">
                        Stripe 256-bit SSL şifreli ödeme altyapısı ile kartınızla anında ödeme yapabilirsiniz.
                    </p>
                    <div style="background: #fff; border: 1px solid #7dd3fc; border-radius: 8px; padding: 10px; font-size: 13px;">
                        💳 Stripe Elements Güvenli Kart Formu (Canlı Anahtarlarınızla Aktif Olur)
                    </div>
                </div>
            </div>

            <!-- Submit Button & Feedback -->
            <div style="margin-top: 24px;">
                <button type="submit" id="btn-submit-ad" class="btn-primary" style="width: 100%;">
                    Teklifi Ver ve Reklamı Başlat
                </button>
                <div id="submit-response-msg" class="bid-message" style="display:none; margin-top: 12px;"></div>
            </div>
        </form>
    </div>
</main>

<script>
function togglePaymentBox(val) {
    const ibanBox = document.getElementById('iban-info-box');
    const stripeBox = document.getElementById('stripe-info-box');
    const lblIban = document.getElementById('lbl-pay-iban');
    const lblStripe = document.getElementById('lbl-pay-stripe');

    if (val === 'iban') {
        ibanBox.style.display = 'block';
        stripeBox.style.display = 'none';
        lblIban.style.border = '2px solid var(--ink)';
        lblStripe.style.border = '1.5px solid var(--border)';
    } else {
        ibanBox.style.display = 'none';
        stripeBox.style.display = 'block';
        lblStripe.style.border = '2px solid var(--ink)';
        lblIban.style.border = '1.5px solid var(--border)';
    }
}

function previewSelectedFile(input) {
    const preview = document.getElementById('logo-preview-img');
    const nameLabel = document.getElementById('file-chosen-name');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        nameLabel.textContent = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        nameLabel.textContent = 'Dosya seçilmedi';
        previewUrlLogo(document.getElementById('ad-avatar-url').value);
    }
}

function getPlatformDefaultLogo(platform) {
    switch (platform) {
        case 'app': return '/assets/images/default_app.svg';
        case 'instagram': return '/assets/images/default_instagram.svg';
        case 'youtube': return '/assets/images/default_youtube.svg';
        case 'x': case 'twitter': return '/assets/images/default_x.svg';
        case 'facebook': return '/assets/images/default_facebook.svg';
        case 'site': default: return '/assets/images/default_site.svg';
    }
}

function previewUrlLogo(url) {
    const preview = document.getElementById('logo-preview-img');
    const fileInput = document.getElementById('ad-logo-file');
    const activeRadio = document.querySelector('input[name="platform"]:checked');
    const platform = activeRadio ? activeRadio.value : 'site';
    if (fileInput && fileInput.files && fileInput.files[0]) {
        return; // File takes precedence in preview
    }
    if (url && url.trim().length > 5) {
        preview.src = url.trim();
    } else {
        preview.src = getPlatformDefaultLogo(platform);
    }
}

function handleUsernameInput(val) {
    const activeRadio = document.querySelector('input[name="platform"]:checked');
    const platform = activeRadio ? activeRadio.value : 'site';
    const inpUrl = document.getElementById('input-target-url');
    if (!inpUrl) return;

    let clean = val.trim().replace(/^@/, '');
    if (!clean) return;

    if (platform === 'x') {
        inpUrl.value = 'https://x.com/' + clean;
    } else if (platform === 'instagram') {
        inpUrl.value = 'https://instagram.com/' + clean;
    } else if (platform === 'site') {
        if (!inpUrl.value || inpUrl.value.includes('x.com') || inpUrl.value.includes('instagram.com')) {
            inpUrl.value = clean.startsWith('http') ? clean : ('https://' + clean);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const citySelect = document.getElementById('ad-city-select');
    const amountInput = document.getElementById('ad-amount-input');
    const hintDiv = document.getElementById('city-bid-hint');
    const form = document.getElementById('ad-submit-form');
    const submitBtn = document.getElementById('btn-submit-ad');
    const respMsg = document.getElementById('submit-response-msg');

    function updateCityRequirements() {
        const opt = citySelect.options[citySelect.selectedIndex];
        if (!opt || !opt.value) return;
        const min = parseInt(opt.getAttribute('data-min') || '10', 10);
        amountInput.min = min;
        if (!amountInput.value || parseInt(amountInput.value, 10) < min) {
            amountInput.value = min;
        }
        hintDiv.textContent = `Bu şehir için geçerli minimum teklif: ${min.toLocaleString('tr-TR')}₺`;
    }

    if (citySelect) {
        citySelect.addEventListener('change', updateCityRequirements);
        updateCityRequirements();
    }

    // Platform Configurations & Dynamic Adaptations
    const platformConfig = {
        site: {
            title: 'Web Sitesi Reklamı',
            desc: 'Ziyaretçiler haritada ilinize tıkladığında doğrudan web sitenize yönlendirilir. Ana sayfadaki Zirve Sıralamasında "Siteler" sekmesinde listelenirsiniz.',
            bg: '#f0fdf4',
            border: '#86efac',
            color: '#166534',
            icon: '🌐',
            stepHeading: '3. Web Sitesi Reklam Bilgileri',
            lblDisplay: 'Firma / Marka Adı',
            phDisplay: 'Marka veya Firma Adı (örn: Ulusoy Digital)',
            lblUser: 'Alan Adı / Domain',
            phUser: 'Alan Adı (örn: ulusoydigital.com)',
            lblUrl: 'Tıklanınca Gidilecek Web Linki',
            phUrl: 'https://ulusoydigital.com'
        },
        x: {
            title: 'X (Twitter) Profil Reklamı',
            desc: 'Haritada ilinizin üzerinde ve Zirve Sıralamasında 𝕏 rozetinizle görünürsünüz. İlinize tıklayanlar doğrudan X profilinize yönlendirilir.',
            bg: '#f8fafc',
            border: '#94a3b8',
            color: '#0f172a',
            icon: '𝕏',
            stepHeading: '3. X (Twitter) Profil Bilgileri',
            lblDisplay: 'X Görünecek İsim / Marka Adı',
            phDisplay: 'Görünecek İsim (örn: Ulusoy Digital)',
            lblUser: 'X Kullanıcı Adı',
            phUser: 'Kullanıcı Adın (örn: @ulusoydigital)',
            lblUrl: 'Tıklanınca Gidilecek X Profil Linki',
            phUrl: 'https://x.com/ulusoydigital'
        },
        instagram: {
            title: 'Instagram Profil Reklamı',
            desc: 'Haritada ilinizin üzerinde ve Zirve Sıralamasında 📷 Instagram rozetinizle görünürsünüz. İlinize tıklayanlar doğrudan Instagram profilinize yönlendirilir.',
            bg: '#fdf2f8',
            border: '#f472b6',
            color: '#831843',
            icon: '📷',
            stepHeading: '3. Instagram Profil Bilgileri',
            lblDisplay: 'Instagram Görünecek İsim / Marka Adı',
            phDisplay: 'Görünecek İsim (örn: Ulusoy Digital)',
            lblUser: 'Instagram Kullanıcı Adı',
            phUser: 'Kullanıcı Adın (örn: @ulusoydigital)',
            lblUrl: 'Tıklanınca Gidilecek Instagram Profil Linki',
            phUrl: 'https://instagram.com/ulusoydigital'
        },
        app: {
            title: 'Mobil Uygulama Reklamı',
            desc: 'Haritada ilinizin üzerinde ve Zirve Sıralamasında 📱 Mobil Uygulama rozetinizle görünürsünüz. İlinize tıklayanlar doğrudan App Store veya Google Play indirme sayfanıza yönlendirilir.',
            bg: '#eff6ff',
            border: '#60a5fa',
            color: '#1e3a8a',
            icon: '📱',
            stepHeading: '3. Mobil Uygulama Bilgileri',
            lblDisplay: 'Uygulama veya Oyun Adı',
            phDisplay: 'Uygulama Adı (örn: Toy Blast)',
            lblUser: 'Paket Adı veya Uygulama ID',
            phUser: 'Paket Adı (örn: com.peakgames.toyblast)',
            lblUrl: 'Tıklanınca Gidilecek Mağaza İndirme Linki',
            phUrl: 'Google Play veya App Store linki (https://...)'
        },
        youtube: {
            title: 'YouTube Kanal / Video Reklamı',
            desc: 'Haritada ilinizin üzerinde ve Zirve Sıralamasında 📺 YouTube rozetinizle görünürsünüz. İlinize tıklayanlar doğrudan YouTube kanalınıza veya videonuza yönlendirilir.',
            bg: '#fef2f2',
            border: '#f87171',
            color: '#991b1b',
            icon: '📺',
            stepHeading: '3. YouTube Reklam Bilgileri',
            lblDisplay: 'Kanal / İçerik Başlığı',
            phDisplay: 'Kanal veya Marka Adı (örn: Ulusoy Dijital)',
            lblUser: 'Kanal Kullanıcı Adı / ID',
            phUser: 'Kullanıcı Adı (örn: @ulusoydijital)',
            lblUrl: 'Tıklanınca Gidilecek YouTube Linki',
            phUrl: 'https://youtube.com/@ulusoydijital veya video linki'
        },
        facebook: {
            title: 'Facebook Sayfa Reklamı',
            desc: 'Haritada ilinizin üzerinde ve Zirve Sıralamasında 👥 Facebook rozetinizle görünürsünüz. İlinize tıklayanlar doğrudan Facebook sayfanıza yönlendirilir.',
            bg: '#eff6ff',
            border: '#93c5fd',
            color: '#1e40af',
            icon: '👥',
            stepHeading: '3. Facebook Sayfa Bilgileri',
            lblDisplay: 'Sayfa / Topluluk Adı',
            phDisplay: 'Sayfa veya Şirket Adı (örn: Ulusoy Medya)',
            lblUser: 'Sayfa Kullanıcı Adı',
            phUser: 'Kullanıcı Adı (örn: ulusoymedya)',
            lblUrl: 'Tıklanınca Gidilecek Facebook Linki',
            phUrl: 'https://facebook.com/ulusoymedya'
        }
    };

    function updatePlatformUI(platformKey) {
        const conf = platformConfig[platformKey] || platformConfig.site;

        // 1. Radio / Segments active class
        document.querySelectorAll('.seg label').forEach(lbl => {
            const inp = lbl.querySelector('input[name="platform"]');
            if (inp && inp.value === platformKey) {
                lbl.classList.add('active');
                inp.checked = true;
            } else {
                lbl.classList.remove('active');
            }
        });

        // 2. Info Box update
        const box = document.getElementById('platform-info-box');
        const ic = document.getElementById('platform-info-icon');
        const title = document.getElementById('platform-info-title');
        const desc = document.getElementById('platform-info-desc');
        if (box && title && desc) {
            box.style.background = conf.bg;
            box.style.borderColor = conf.border;
            box.style.color = conf.color;
            if (ic) ic.textContent = conf.icon;
            title.textContent = conf.title + ' Seçildi';
            desc.textContent = conf.desc;
        }

        // 3. Step 3 Inputs, Labels & Placeholders
        const heading = document.getElementById('step3-heading');
        const lblDisplay = document.getElementById('lbl-display-name');
        const inpDisplay = document.getElementById('input-display-name');
        const lblUser = document.getElementById('lbl-username');
        const inpUser = document.getElementById('input-username');
        const lblUrl = document.getElementById('lbl-target-url');
        const inpUrl = document.getElementById('input-target-url');

        if (heading) heading.textContent = conf.stepHeading;
        if (lblDisplay) lblDisplay.textContent = conf.lblDisplay;
        if (inpDisplay) inpDisplay.placeholder = conf.phDisplay;
        if (lblUser) lblUser.textContent = conf.lblUser;
        if (inpUser) inpUser.placeholder = conf.phUser;
        if (lblUrl) lblUrl.textContent = conf.lblUrl;
        if (inpUrl) inpUrl.placeholder = conf.phUrl;

        // Auto-adapt existing username input
        if (inpUser && inpUser.value.trim().length > 0) {
            handleUsernameInput(inpUser.value);
        }

        // Auto-adapt standard preview logo if no custom file or URL is active
        const fileInput = document.getElementById('ad-logo-file');
        const urlInput = document.getElementById('ad-avatar-url');
        const preview = document.getElementById('logo-preview-img');
        const chosenName = document.getElementById('file-chosen-name');
        if ((!fileInput || !fileInput.files || !fileInput.files[0]) && (!urlInput || !urlInput.value.trim())) {
            if (preview) preview.src = getPlatformDefaultLogo(platformKey);
            if (chosenName) chosenName.textContent = `Standart ${conf.title} logosu seçili`;
        }
    }

    // Attach click and change listeners to platform segments
    document.querySelectorAll('.seg label').forEach(lbl => {
        lbl.addEventListener('click', () => {
            const inp = lbl.querySelector('input[name="platform"]');
            if (inp) {
                updatePlatformUI(inp.value);
            }
        });
    });

    document.querySelectorAll('input[name="platform"]').forEach(inp => {
        inp.addEventListener('change', () => {
            updatePlatformUI(inp.value);
        });
    });

    // Run once on load to ensure defaults are styled
    updatePlatformUI('site');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.textContent = 'İşlem Yapılıyor...';
            respMsg.style.display = 'none';

            const fd = new FormData(form);
            try {
                const res = await fetch('/api.php', { method: 'POST', body: fd });
                const data = await res.json();
                respMsg.style.display = 'block';

                if (data.success) {
                    respMsg.className = 'bid-message success';
                    respMsg.textContent = data.message || 'Tebrikler! Teklifiniz başarıyla kaydedildi.';
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1800);
                } else {
                    respMsg.className = 'bid-message error';
                    respMsg.textContent = data.error || 'Teklif verilemedi.';
                }
            } catch (err) {
                respMsg.style.display = 'block';
                respMsg.className = 'bid-message error';
                respMsg.textContent = 'Bağlantı hatası oluştu.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Teklifi Ver ve Reklamı Başlat';
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
