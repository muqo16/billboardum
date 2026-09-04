<?php
$modalStats = get_site_stats();
?>
<!-- Sleek Floating White Pill Tooltip -->
<div id="map-tooltip" class="map-tooltip"></div>

<!-- City Leader & Alt İlanlar Drawer -->
<div id="city-modal-backdrop" class="drawer-backdrop" style="display: none;">
    <div id="city-modal" class="city-drawer-box" role="dialog" aria-modal="true">
        <div class="drawer-header">
            <div class="drawer-header-left">
                <span class="drawer-plate" id="modal-plate-code">34</span>
                <div>
                    <h3 class="drawer-title" id="modal-city-title">İstanbul</h3>
                    <span id="modal-total-bids-badge" class="drawer-bids-badge">1 Teklif</span>
                </div>
            </div>
            <button type="button" class="drawer-close" id="modal-close-btn" aria-label="Kapat">&times;</button>
        </div>

        <div class="drawer-body">
            <!-- Leader / Zirvedeki Reklam (GÖZ ALICI ALTIN PARLAMA & ŞAMPİYON KARTI) -->
            <div class="drawer-leader-card champion-glowing" id="modal-leader-card">
                <div class="dlc-top-bar">
                    <div class="dlc-tag champion-tag">
                        <span class="crown-icon">👑</span>
                        <span class="rank-title">#1 ZİRVE LİDERİ</span>
                        <span class="sparkle-anim">✨</span>
                    </div>
                    <span class="leader-status-live">● Canlı Zirvede</span>
                </div>
                <div class="dlc-body">
                    <div class="cl-avatar-wrap champion-avatar-wrap">
                        <img id="modal-leader-avatar" src="" alt="Avatar" class="cl-avatar" />
                        <span class="cl-badge" id="modal-leader-badge">🌐</span>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span id="modal-leader-name" class="champion-name">Ulusoy Digital</span>
                            <span id="modal-leader-amount" class="champion-amount">6.315₺</span>
                        </div>
                        <div class="champion-meta">
                            <span id="modal-leader-sub">@ulusoydigital.com · 👑 <span id="modal-leader-cities">43</span> şehir</span>
                            <span id="modal-leader-clicks">👁️ 1.318 tıklama</span>
                        </div>
                    </div>
                </div>
                <div id="modal-leader-socials" class="social-links-wrap">
                    <a href="#" id="modal-leader-visit" target="_blank" rel="noopener noreferrer" class="social-pill site">
                        <span class="icon">🌐</span> <span>Siteyi Ziyaret Et</span>
                    </a>
                </div>
            </div>

            <!-- Alt İlanlar (Önceki & Diğer Teklifler - 2. ve 3. Sıraya Düşenler) -->
            <div class="alt-ilanlar-section" id="modal-alt-section">
                <div class="alt-ilanlar-title">
                    <span>📜 Sıralamadaki Diğer Teklifler (2. ve Alt Sıralar)</span>
                    <span id="modal-alt-count" class="alt-count-pill">0 teklif</span>
                </div>
                <div class="alt-ilanlar-list" id="modal-alt-list">
                    <!-- Dynamic rows inserted here -->
                </div>
            </div>

            <!-- Zirveyi Kap Büyük Butonu (Kullanıcı Ekranındakiyle Birebir) -->
            <div class="drawer-cta-wrap">
                <button type="button" class="btn-zirveyi-kap" id="modal-btn-zirveyi-kap">
                    <span id="modal-btn-zirve-text">Zirveyi kap — 15₺</span>
                    <span class="btn-arrow">⚡</span>
                </button>
            </div>

            <!-- Outbid / Bu Şehri Devral Formu -->
            <div class="drawer-outbid-form-wrap" id="modal-form-wrap">
                <div class="dof-title" id="modal-form-title">⚡ Bu Şehri Hemen Devral (Outbid)</div>
                <form id="modal-bid-form" class="modal-bid-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="place_ad" />
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>" />
                    <input type="hidden" name="city" id="modal-bid-city" value="" />

                    <div class="seg seg-mini" role="radiogroup" aria-label="Listeleme türü" style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 6px;">
                        <label class="active"><input type="radio" name="platform" value="site" checked /> <span class="tx">🌐 Web</span></label>
                        <label><input type="radio" name="platform" value="app" /> <span class="tx">📱 Uygulama</span></label>
                        <label><input type="radio" name="platform" value="instagram" /> <span class="tx">📷 Instagram</span></label>
                        <label><input type="radio" name="platform" value="youtube" /> <span class="tx">📺 YouTube</span></label>
                        <label><input type="radio" name="platform" value="x" /> <span class="tx">𝕏 X</span></label>
                        <label><input type="radio" name="platform" value="facebook" /> <span class="tx">👥 Facebook</span></label>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="field">
                            <input type="text" name="display_name" id="modal-input-display" placeholder="Marka veya Hesap Adın" required autocomplete="off" />
                        </div>
                        <div class="field">
                            <input type="text" name="username" id="modal-input-user" placeholder="Kullanıcı adın / site adresin" required autocomplete="off" />
                        </div>
                        <div class="field">
                            <input type="url" name="target_url" id="modal-input-target" placeholder="Hedef Link (https://...)" required autocomplete="off" />
                        </div>

                        <!-- Marka Logosu (Kendi Logonuz veya Standart Logo) -->
                        <div class="modal-logo-wrap" style="background: #fffdf8; border: 1.5px dashed #ebdcb9; border-radius: 10px; padding: 10px; display: flex; gap: 12px; align-items: center;">
                            <div style="position: relative; width: 50px; height: 50px; flex-shrink: 0; border-radius: 50%; border: 2px solid #2b2720; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                <img id="modal-logo-preview" src="/assets/images/default_site.svg" alt="Logo Önizleme" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/assets/images/default_site.svg'" />
                            </div>
                            <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <label style="background: #2b2720; color: #fff; padding: 5px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.12);">
                                        <span>📁 Logo Dosyası Seç</span>
                                        <input type="file" name="logo_file" id="modal-ad-logo-file" accept="image/png,image/jpeg,image/webp" style="display: none;" onchange="previewModalLogoFile(this)" />
                                    </label>
                                    <span id="modal-file-chosen-name" style="font-size: 11px; color: var(--ink-muted); font-weight: 600;">Standart logo seçili</span>
                                </div>
                                <div class="field" style="margin: 0;">
                                    <input type="url" name="avatar_url" id="modal-input-avatar" placeholder="veya Logo Linki Yapıştır (https://...)" autocomplete="off" style="height: 30px; font-size: 11.5px; padding: 0 8px;" oninput="previewModalUrlLogo(this.value)" />
                                </div>
                                <div style="font-size: 10.5px; color: #854d0e; line-height: 1.3;">
                                    ℹ️ <em>Logo yüklerseniz kendi logonuz görünür. Boş bırakırsanız seçtiğiniz platformun logosu otomatik atanır.</em>
                                </div>
                            </div>
                        </div>


                        <div class="field money">
                            <input type="number" name="amount" id="modal-input-amount" min="10" step="1" required />
                            <span class="tl">₺</span>
                        </div>

                        <!-- Payment Method Toggle in Modal -->
                        <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--border);">
                            <div style="font-size: 12px; font-weight: 700; margin-bottom: 4px;">Ödeme Yöntemi:</div>
                            <div style="display: flex; gap: 8px; font-size: 12px;">
                                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="iban" checked onclick="document.getElementById('modal-iban-details').style.display='flex'" />
                                    <span>🏦 IBAN Havale</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="stripe" onclick="document.getElementById('modal-iban-details').style.display='none'" />
                                    <span>💳 Kredi Kartı</span>
                                </label>
                            </div>

                            <div id="modal-iban-details" style="margin-top: 8px; background: #fffdf8; border: 1px solid #ebdcb9; border-radius: 8px; padding: 10px; font-size: 12px; display: flex; flex-direction: column; gap: 6px;">
                                <div><strong>IBAN:</strong> <?= htmlspecialchars($modalStats['iban_no'] ?? '123456789') ?></div>
                                <div><strong>Alıcı:</strong> <?= htmlspecialchars($modalStats['iban_name'] ?? 'Ulusoy Digital Medya A.Ş.') ?></div>
                                <input type="text" name="sender_name" placeholder="👤 Gönderen Adı Soyadı (Hesap Sahibi)" style="width: 100%; height: 32px; font-size: 12px; padding: 0 8px; border: 1px solid var(--border); border-radius: 6px;" />
                                <input type="tel" name="sender_phone" placeholder="📞 Telefon Numarası (Teyit İçin)" style="width: 100%; height: 32px; font-size: 12px; padding: 0 8px; border: 1px solid var(--border); border-radius: 6px;" />
                                <input type="text" name="receipt_no" placeholder="🧾 Dekont / Referans No" style="width: 100%; height: 32px; font-size: 12px; padding: 0 8px; border: 1px solid var(--border); border-radius: 6px;" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="modal-submit-btn" style="width: 100%; margin-top: 10px;">
                        <span>Teklifi Ver ve Zirveye Geç</span>
                    </button>
                    <div id="modal-bid-msg" class="bid-message" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
