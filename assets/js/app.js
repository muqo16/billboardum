// Billboardum App JavaScript
document.addEventListener('DOMContentLoaded', () => {
    // 1. Platform Picker Logic (Hero Form)
    const platformInputs = document.querySelectorAll('#claim input[name="platform"]');
    const userInput = document.querySelector('#claim input[name="username"]');
    const citySelect = document.querySelector('#claim select[name="city"]');
    const amountInput = document.querySelector('#claim input[name="amount"]');
    const bulkLink = document.querySelector('.bulk-link');

    const placeholders = {
        instagram: 'Instagram kullanıcı adın (örn: @hesabim)',
        x: 'X kullanıcı adın (örn: @hesabim)',
        app: 'Uygulama paket adı veya adı (örn: com.app.id)',
        site: 'Web sitesi adresin (örn: siteadresi.com)'
    };

    function updatePlatform(platform) {
        if (userInput) {
            userInput.placeholder = placeholders[platform] || 'Kullanıcı adın veya adresin';
        }
        if (bulkLink) {
            bulkLink.href = '/toplu.php?p=' + encodeURIComponent(platform);
        }
        // Update label active classes
        document.querySelectorAll('#claim .seg label').forEach(lbl => {
            const inp = lbl.querySelector('input');
            if (inp && inp.value === platform) {
                lbl.classList.add('active');
            } else {
                lbl.classList.remove('active');
            }
        });
    }

    platformInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            updatePlatform(e.target.value);
        });
    });

    // 2. City select change -> update min amount
    if (citySelect && amountInput) {
        citySelect.addEventListener('change', () => {
            const opt = citySelect.selectedOptions[0];
            if (opt && opt.dataset.minAmount) {
                const min = parseInt(opt.dataset.minAmount, 10) + 1;
                amountInput.min = min;
                if (!amountInput.value || parseInt(amountInput.value, 10) < min) {
                    amountInput.value = min;
                }
            }
        });
    }

    // 3. Hero Form AJAX Submit
    const claimForm = document.getElementById('claim');
    if (claimForm) {
        claimForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = claimForm.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'İşleniyor...';

            const formData = new FormData(claimForm);
            formData.append('action', 'bid');

            try {
                const res = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Teklifiniz başarıyla alındı!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Bir hata oluştu.');
                }
            } catch (err) {
                alert('Bağlantı hatası oluştu: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // 4. Modal Platform Switcher & Modal Bid Submit
    const getPlatLogo = (p) => {
        switch (p) {
            case 'app': return '/assets/images/default_app.svg';
            case 'instagram': return '/assets/images/default_instagram.svg';
            case 'youtube': return '/assets/images/default_youtube.svg';
            case 'x': case 'twitter': return '/assets/images/default_x.svg';
            case 'facebook': return '/assets/images/default_facebook.svg';
            case 'site': default: return '/assets/images/default_site.svg';
        }
    };

    window.previewModalLogoFile = function(input) {
        const preview = document.getElementById('modal-logo-preview');
        const nameLabel = document.getElementById('modal-file-chosen-name');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (nameLabel) nameLabel.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            if (nameLabel) nameLabel.textContent = 'Standart logo seçili';
            window.previewModalUrlLogo(document.getElementById('modal-input-avatar')?.value);
        }
    };

    window.previewModalUrlLogo = function(url) {
        const preview = document.getElementById('modal-logo-preview');
        const fileInput = document.getElementById('modal-ad-logo-file');
        const activeRadio = document.querySelector('#modal-bid-form input[name="platform"]:checked');
        const platform = activeRadio ? activeRadio.value : 'site';
        if (fileInput && fileInput.files && fileInput.files[0]) {
            return; // File takes precedence
        }
        if (url && url.trim().length > 5) {
            if (preview) preview.src = url.trim();
        } else {
            if (preview) preview.src = getPlatLogo(platform);
        }
    };

    const modalForm = document.getElementById('modal-bid-form');
    if (modalForm) {
        const mPlatInputs = modalForm.querySelectorAll('input[name="platform"]');
        const mUser = document.getElementById('modal-input-user');
        mPlatInputs.forEach(inp => {
            inp.addEventListener('change', (e) => {
                const p = e.target.value;
                if (mUser) mUser.placeholder = placeholders[p] || 'Kullanıcı adın';
                modalForm.querySelectorAll('.seg label').forEach(l => {
                    const r = l.querySelector('input');
                    if (r && r.value === p) l.classList.add('active');
                    else l.classList.remove('active');
                });
                // Update default logo preview if no custom file or URL is provided
                const fInp = document.getElementById('modal-ad-logo-file');
                const uInp = document.getElementById('modal-input-avatar');
                if ((!fInp || !fInp.files || !fInp.files[0]) && (!uInp || !uInp.value.trim())) {
                    const prev = document.getElementById('modal-logo-preview');
                    if (prev) prev.src = getPlatLogo(p);
                    const nameLbl = document.getElementById('modal-file-chosen-name');
                    if (nameLbl) nameLbl.textContent = 'Standart logo seçili';
                }
            });
        });

        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const mBtn = document.getElementById('modal-submit-btn');
            const mMsg = document.getElementById('modal-bid-msg');
            mBtn.disabled = true;
            mBtn.textContent = 'Teklif Alınıyor...';
            mMsg.style.display = 'none';

            const formData = new FormData(modalForm);
            formData.append('action', 'bid');

            try {
                const res = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    mMsg.className = 'bid-message success';
                    mMsg.textContent = data.message;
                    mMsg.style.display = 'block';
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    mMsg.className = 'bid-message error';
                    mMsg.textContent = data.error;
                    mMsg.style.display = 'block';
                }
            } catch (err) {
                mMsg.className = 'bid-message error';
                mMsg.textContent = 'Hata: ' + err.message;
                mMsg.style.display = 'block';
            } finally {
                mBtn.disabled = false;
                mBtn.textContent = 'Teklifi Ver ve Sahip Ol';
            }
        });
    }

    // 5. Leaderboard Category Filter Tabs (Instant & Responsive)
    const filterTabsContainer = document.getElementById('leaderboard-filter-tabs');
    if (filterTabsContainer) {
        const tabBtns = filterTabsContainer.querySelectorAll('.tab-btn');
        const rows = document.querySelectorAll('.outbid-table .outbid-row');
        const emptyState = document.getElementById('leaderboard-empty');

        function filterLeaderboard(category) {
            let visibleCount = 0;
            rows.forEach(row => {
                const rowPlatform = (row.dataset.platform || 'site').toLowerCase();
                const shouldShow = (category === 'all' || rowPlatform === category);
                if (shouldShow) {
                    row.style.display = 'grid';
                    visibleCount++;
                    // Dynamically recalculate medal/rank for filtered list
                    const rankCol = row.querySelector('.col-rank');
                    if (rankCol) {
                        if (visibleCount === 1) {
                            rankCol.innerHTML = '<span class="trophy">🥇</span>';
                        } else if (visibleCount === 2) {
                            rankCol.innerHTML = '<span class="trophy">🥈</span>';
                        } else if (visibleCount === 3) {
                            rankCol.innerHTML = '<span class="trophy">🥉</span>';
                        } else {
                            rankCol.innerHTML = '<span class="rank-num">#' + visibleCount + '</span>';
                        }
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            if (emptyState) {
                emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
            }

            tabBtns.forEach(btn => {
                if (btn.dataset.tab === category) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const cat = btn.dataset.tab || 'all';
                filterLeaderboard(cat);
                const newUrl = (cat === 'all') ? window.location.pathname : window.location.pathname + '?tab=' + encodeURIComponent(cat);
                window.history.replaceState(null, '', newUrl);
            });
        });

        // Initialize from URL parameter if present
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab && ['site', 'app', 'instagram', 'youtube', 'x', 'facebook'].includes(initialTab)) {
            filterLeaderboard(initialTab);
        }
    }
});

