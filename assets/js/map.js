// Interactive 81-City Turkey Map, Hover Pinpoint, Leader & Alt İlanlar Drawer
document.addEventListener('DOMContentLoaded', () => {
    const svgMap = document.getElementById('svg-turkiye-haritasi');
    const pinsLayer = document.getElementById('map-avatar-pins-layer');
    const tooltip = document.getElementById('map-tooltip');
    const leaderBar = document.getElementById('city-leader-bar');

    // Drawer Elements
    const modalBackdrop = document.getElementById('city-modal-backdrop');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const modalPlate = document.getElementById('modal-plate-code');
    const modalTitle = document.getElementById('modal-city-title');
    const modalLeaderCard = document.getElementById('modal-leader-card');
    const modalLeaderAvatar = document.getElementById('modal-leader-avatar');
    const modalLeaderBadge = document.getElementById('modal-leader-badge');
    const modalLeaderName = document.getElementById('modal-leader-name');
    const modalLeaderAmount = document.getElementById('modal-leader-amount');
    const modalLeaderSub = document.getElementById('modal-leader-sub');
    const modalLeaderCities = document.getElementById('modal-leader-cities');
    const modalLeaderClicks = document.getElementById('modal-leader-clicks');
    const modalLeaderVisit = document.getElementById('modal-leader-visit');
    const modalAltCount = document.getElementById('modal-alt-count');
    const modalAltList = document.getElementById('modal-alt-list');
    const modalBidCity = document.getElementById('modal-bid-city');
    const modalBidAmount = document.getElementById('modal-input-amount');
    const modalBidMsg = document.getElementById('modal-bid-msg');
    const modalFormTitle = document.getElementById('modal-form-title');
    const modalBidForm = document.getElementById('modal-bid-form');

    let allCities = [];
    let cityMap = {};
    let activeCitySlug = 'istanbul';

    function closeModal() {
        if (modalBackdrop) modalBackdrop.style.display = 'none';
    }
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) closeModal();
        });
    }

    // Platform icons and labels helper
    function getPlatformIcon(p) {
        switch (p) {
            case 'x': return '𝕏';
            case 'instagram': return '📷';
            case 'app': return '📱';
            case 'youtube': return '📺';
            case 'facebook': return '👥';
            default: return '🌐';
        }
    }

    function getPlatformLabel(p) {
        switch (p) {
            case 'x': return 'X (Twitter)';
            case 'instagram': return 'Instagram';
            case 'app': return 'Mobil Uygulama';
            case 'youtube': return 'YouTube';
            case 'facebook': return 'Facebook';
            default: return 'Web Sitesi';
        }
    }

    function getPlatformActionBtnText(p) {
        switch (p) {
            case 'x': return 'X Profiline Git';
            case 'instagram': return 'Instagram Profiline Git';
            case 'app': return 'Uygulamayı Aç / İndir';
            case 'youtube': return 'YouTube Kanalına Git';
            case 'facebook': return 'Facebook Sayfasına Git';
            default: return 'Web Sitesini Ziyaret Et';
        }
    }

    function getDefaultLogo(p) {
        switch (p) {
            case 'app': return '/assets/images/default_app.svg';
            case 'instagram': return '/assets/images/default_instagram.svg';
            case 'youtube': return '/assets/images/default_youtube.svg';
            case 'x': case 'twitter': return '/assets/images/default_x.svg';
            case 'facebook': return '/assets/images/default_facebook.svg';
            case 'site': default: return '/assets/images/default_site.svg';
        }
    }

    function resolveAvatar(url, p) {
        if (!url || url === '/assets/images/default_logo.svg') {
            return getDefaultLogo(p);
        }
        return url;
    }

    // Update the bottom Leader Card bar
    function updateLeaderBar(city, leaderData) {
        if (!leaderBar) return;
        const rankEl = leaderBar.querySelector('.cl-rank');
        const imgEl = leaderBar.querySelector('.cl-avatar');
        const badgeEl = leaderBar.querySelector('.cl-badge');
        const nameEl = leaderBar.querySelector('.cl-brand-name');
        const whereEl = leaderBar.querySelector('.cl-where-line');
        const amtEl = leaderBar.querySelector('.cl-amount');
        const clicksEl = leaderBar.querySelector('.cl-clicks');

        const isOwned = Boolean(city.current_owner_id && city.username);
        const name = city.display_name || city.username || 'Boş Şehir (İlk Sen Kap)';
        const amt = (city.current_amount || 10).toLocaleString('tr-TR') + '₺';
        const pIcon = getPlatformIcon(city.platform);
        const avatar = resolveAvatar(city.avatar_url, city.platform);

        if (rankEl) rankEl.textContent = isOwned ? '#1' : 'Boş';
        if (imgEl) imgEl.src = avatar;
        if (badgeEl) badgeEl.textContent = pIcon;
        if (nameEl) nameEl.textContent = name;
        if (amtEl) amtEl.textContent = amt;
        if (clicksEl) clicksEl.innerHTML = `👁️ ${city.total_clicks || 0}`;

        const citiesCount = (leaderData && leaderData.leader_total_cities) ? leaderData.leader_total_cities : 1;
        const extraCities = citiesCount > 1 ? ` +${citiesCount - 1} şehir` : '';
        if (whereEl) {
            whereEl.innerHTML = `<span>${city.name}</span> <span class="cl-crown">👑</span> <span>${city.plate_code}</span> <span>${extraCities}</span>`;
        }
    }

    // Open City Drawer and load full Leader & Alt İlanlar
    async function openCityDrawer(slug) {
        if (!modalBackdrop) return;
        const realSlug = (slug === 'istanbul-avrupa' || slug === 'istanbul-asya') ? 'istanbul' : slug;
        activeCitySlug = realSlug;

        // Highlight city on SVG
        document.querySelectorAll('#turkiye g[data-plakakodu]').forEach(g => g.classList.remove('selected'));
        if (realSlug === 'istanbul') {
            const avr = document.querySelector('#istanbul-avrupa');
            const asy = document.querySelector('#istanbul-asya');
            if (avr) avr.classList.add('selected');
            if (asy) asy.classList.add('selected');
        } else {
            const activeG = document.querySelector(`#turkiye g#${realSlug}`);
            if (activeG) activeG.classList.add('selected');
        }

        // Fetch details from API
        try {
            const res = await fetch(`/api.php?action=get_city_details&slug=${encodeURIComponent(realSlug)}`);
            const data = await res.json();
            if (!data || !data.success) return;

            const c = data.city;
            const leader = data.leader;
            const altIlanlar = data.alt_ilanlar || [];

            updateLeaderBar(c, data);

            if (modalPlate) modalPlate.textContent = c.plate_code || '00';
            if (modalTitle) modalTitle.textContent = c.name;
            if (modalBidCity) modalBidCity.value = realSlug;

            const isOwned = Boolean(leader);
            const currAmt = parseInt(c.current_amount || 10, 10);
            const minBid = isOwned ? (currAmt + 5) : 10;
            const totalBidsCount = (isOwned ? 1 : 0) + altIlanlar.length;

            const totalBidsBadge = document.getElementById('modal-total-bids-badge');
            if (totalBidsBadge) {
                totalBidsBadge.textContent = totalBidsCount > 0 ? `${totalBidsCount} Teklif` : 'Henüz Teklif Yok';
            }

            const btnZirve = document.getElementById('modal-btn-zirveyi-kap');
            const btnZirveText = document.getElementById('modal-btn-zirve-text');
            if (btnZirveText) {
                btnZirveText.textContent = `Zirveyi kap — ${minBid.toLocaleString('tr-TR')}₺`;
            }
            if (btnZirve) {
                btnZirve.onclick = () => {
                    const formWrap = document.getElementById('modal-form-wrap');
                    if (formWrap) {
                        formWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const firstInput = document.getElementById('modal-input-display');
                        if (firstInput) firstInput.focus();
                    }
                };
            }

            if (modalBidAmount) {
                modalBidAmount.min = minBid;
                modalBidAmount.value = minBid;
            }

            if (modalLeaderCard) {
                if (isOwned) {
                    modalLeaderCard.style.display = 'block';
                    modalLeaderCard.classList.add('champion-glowing');
                    if (modalLeaderAvatar) {
                        modalLeaderAvatar.src = resolveAvatar(leader.avatar_url, leader.platform);
                        modalLeaderAvatar.onerror = () => { modalLeaderAvatar.src = getDefaultLogo(leader.platform); };
                    }
                    if (modalLeaderBadge) modalLeaderBadge.textContent = getPlatformIcon(leader.platform);
                    if (modalLeaderName) modalLeaderName.textContent = leader.display_name || ('@' + leader.username);
                    if (modalLeaderAmount) modalLeaderAmount.textContent = currAmt.toLocaleString('tr-TR') + '₺';
                    if (modalLeaderCities) modalLeaderCities.textContent = data.leader_total_cities || 1;
                    if (modalLeaderClicks) modalLeaderClicks.textContent = `👁️ ${(c.total_clicks || 0).toLocaleString('tr-TR')} tıklama`;

                    // Click anywhere on leader card opens the ad
                    const leaderGoUrl = `/go.php?owner_id=${leader.owner_id || ''}&city=${encodeURIComponent(realSlug)}`;
                    modalLeaderCard.onclick = (e) => {
                        if (!e.target.closest('a') && !e.target.closest('button')) {
                            window.open(leaderGoUrl, '_blank');
                        }
                    };
                    modalLeaderCard.title = `${leader.display_name || leader.username} reklamına gitmek için tıkla`;

                    // Sadece şirketin ilan verdiği platformun tek butonu gösterilir (gereksiz sosyal medya butonları tamamen kaldırıldı)
                    const socialsWrap = document.getElementById('modal-leader-socials');
                    if (socialsWrap) {
                        let pills = '';
                        if (leader.target_url) {
                            const p = leader.platform || 'site';
                            const mainLabel = getPlatformActionBtnText(p);
                            const mainIcon = getPlatformIcon(p);
                            pills = `<a href="${leaderGoUrl}" target="_blank" rel="noopener noreferrer" class="social-pill ${p}" onclick="event.stopPropagation();"><span class="icon">${mainIcon}</span> <span>${mainLabel} ↗</span></a>`;
                        }
                        socialsWrap.innerHTML = pills;
                        socialsWrap.style.display = pills ? 'flex' : 'none';
                    }

                    if (modalFormTitle) modalFormTitle.textContent = `⚡ Bu Şehri Devral (Minimum: ${minBid}₺)`;
                } else {
                    const socialsWrap = document.getElementById('modal-leader-socials');
                    if (socialsWrap) socialsWrap.style.display = 'none';
                    modalLeaderCard.style.display = 'none';
                    modalLeaderCard.onclick = null;
                    if (modalFormTitle) modalFormTitle.textContent = `🌟 Bu Şehrin İlk Sahibi Ol (Minimum: ${minBid}₺)`;
                }
            }

            // Render Alt İlanlar (2., 3. ve Sonraki Sıralar)
            if (modalAltCount) modalAltCount.textContent = `${altIlanlar.length} teklif`;
            if (modalAltList) {
                if (altIlanlar.length === 0) {
                    modalAltList.innerHTML = '<div class="alt-empty">Bu şehir için henüz alt teklif bulunmuyor. Şehri devralarak ilk rekabeti siz başlatın!</div>';
                } else {
                    modalAltList.innerHTML = altIlanlar.map((a, idx) => {
                        const rankNum = idx + 2;
                        let rankBadgeClass = 'rank-default';
                        let rankBadgeText = `#${rankNum}`;
                        let rankNote = '';
                        if (rankNum === 2) {
                            rankBadgeClass = 'rank-2';
                            rankBadgeText = `🥈 #${rankNum}`;
                            rankNote = `<span class="alt-outbid-note">2. Sıraya Düştü</span>`;
                        } else if (rankNum === 3) {
                            rankBadgeClass = 'rank-3';
                            rankBadgeText = `🥉 #${rankNum}`;
                            rankNote = `<span class="alt-outbid-note">3. Sıraya Düştü</span>`;
                        }
                        const pIcon = getPlatformIcon(a.platform);
                        const pLabel = getPlatformLabel(a.platform);
                        const av = resolveAvatar(a.avatar_url, a.platform);
                        const amtStr = (a.amount || 0).toLocaleString('tr-TR') + '₺';
                        const dateStr = a.created_at ? a.created_at.substring(0, 10) : '';
                        const targetGoUrl = `/go.php?bid_id=${a.id}`;
                        return `
                            <div class="alt-ilan-row" onclick="window.open('${targetGoUrl}', '_blank');" title="${a.display_name || a.username} reklamını yeni sekmede aç">
                                <div class="alt-ilan-left">
                                    <span class="alt-rank ${rankBadgeClass}">${rankBadgeText}</span>
                                    <img src="${av}" alt="${a.display_name || a.username}" class="alt-avatar" onerror="this.src='${getDefaultLogo(a.platform)}'" />
                                    <div>
                                        <div class="alt-name">
                                            <span>${a.display_name || a.username}</span>
                                            <span class="alt-platform-tag">${pIcon} ${pLabel}</span>
                                            ${rankNote}
                                        </div>
                                        <span style="font-size: 11px; color: var(--ink-muted);">${dateStr}</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="alt-amount">${amtStr}</div>
                                    <a href="${targetGoUrl}" target="_blank" class="alt-go-btn" onclick="event.stopPropagation();" title="Reklamı Aç">Reklama Git ↗</a>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            if (modalBidMsg) modalBidMsg.style.display = 'none';
            modalBackdrop.style.display = 'flex';
        } catch (e) {
            console.error('Error opening city drawer:', e);
        }
    }

    // Handle clicking the bottom leader bar
    if (leaderBar) {
        leaderBar.addEventListener('click', () => {
            openCityDrawer(activeCitySlug);
        });
    }

    // Render rich company popup card on hover ("popup gibi düşün")
    function renderPopupCard(c, isOwned) {
        const cityName = c.name || c.slug;
        const plate = c.plate_code || '00';
        const amountStr = (c.current_amount || 10).toLocaleString('tr-TR') + '₺';
        const avatar = resolveAvatar(c.avatar_url, c.platform);
        const pIcon = isOwned ? getPlatformIcon(c.platform) : '🏙️';
        const displayName = c.display_name || (c.username ? ('@' + c.username) : 'Boş Şehir');
        const clicksStr = (c.total_clicks || 0).toLocaleString('tr-TR');

        if (isOwned) {
            return `
                <div class="company-popup-card">
                    <div class="cpp-header">
                        <div class="cpp-logo-wrap">
                            <img src="${avatar}" alt="${displayName}" class="cpp-logo" onerror="this.src='${getDefaultLogo(c.platform)}'" />
                            <span class="cpp-badge">${pIcon}</span>
                        </div>
                        <div class="cpp-meta">
                            <div class="cpp-title-line">
                                <strong class="cpp-brand">${displayName}</strong>
                                <span class="cpp-crown">👑</span>
                            </div>
                            <div class="cpp-city-line">
                                <span>📍 ${cityName}</span>
                                <span class="cpp-dot">·</span>
                                <span class="cpp-plate">${plate}</span>
                            </div>
                        </div>
                    </div>
                    <div class="cpp-stats-row">
                        <div class="cpp-stat-pill price">
                            <span class="lbl">Teklif:</span>
                            <span class="val">${amountStr}</span>
                        </div>
                        <div class="cpp-stat-pill clicks">
                            <span class="lbl">Tık:</span>
                            <span class="val">👁️ ${clicksStr}</span>
                        </div>
                    </div>
                    <div class="cpp-footer">
                        <span>Alt İlanlar & Detaylar İçin Tıkla</span>
                        <span class="cpp-arrow">➔</span>
                    </div>
                    <div class="cpp-triangle"></div>
                </div>
            `;
        } else {
            return `
                <div class="company-popup-card empty-city">
                    <div class="cpp-header">
                        <div class="cpp-logo-wrap empty">
                            <span>🏙️</span>
                        </div>
                        <div class="cpp-meta">
                            <strong class="cpp-brand">${cityName}</strong>
                            <div class="cpp-city-line">
                                <span>Plaka: ${plate}</span>
                                <span class="cpp-dot">·</span>
                                <span class="cpp-empty-tag">Henüz Sahipsiz</span>
                            </div>
                        </div>
                    </div>
                    <div class="cpp-empty-body">
                        💰 Bu şehri sadece <strong>${amountStr}</strong>'ye ilk sen kap ve haritada lider ol!
                    </div>
                    <div class="cpp-footer">
                        <span>İlk Reklamı Vermek İçin Tıkla</span>
                        <span class="cpp-arrow">➔</span>
                    </div>
                    <div class="cpp-triangle"></div>
                </div>
            `;
        }
    }

    // Load cities & setup single-province hover, pin enlargement & click
    async function loadMap() {
        try {
            const res = await fetch('/api.php?action=get_cities');
            allCities = await res.json();
            if (!Array.isArray(allCities)) return;

            cityMap = {};
            allCities.forEach(c => {
                cityMap[c.slug] = c;
                if (c.slug === 'istanbul' || c.slug === 'istanbul-avrupa' || c.slug === 'istanbul-asya') {
                    cityMap['istanbul-avrupa'] = c;
                    cityMap['istanbul-asya'] = c;
                    cityMap['istanbul'] = c;
                }
            });

            // Set initial bottom leader bar to Istanbul
            if (cityMap['istanbul']) {
                updateLeaderBar(cityMap['istanbul']);
            }

            const cityPins = {};
            if (pinsLayer) pinsLayer.innerHTML = '';
            const renderedOwners = new Set();

            // Loop through each city group in the SVG (including nested Istanbul regions)
            const provinceGroups = svgMap.querySelectorAll('#turkiye g[data-plakakodu]');
            provinceGroups.forEach(g => {
                const slug = g.id;
                const c = cityMap[slug] || {
                    slug: slug,
                    name: g.getAttribute('data-iladi') || slug,
                    plate_code: g.getAttribute('data-plakakodu') || '00',
                    current_amount: 10,
                    total_clicks: 0
                };

                const isOwned = Boolean(c.current_owner_id && c.username);
                if (isOwned) {
                    g.classList.add('has-owner');
                }

                // Render floating circular pin over owned cities
                if (isOwned) {
                    const ownerKey = c.username + '_' + (c.slug.includes('istanbul') ? 'istanbul' : c.slug);
                    if (!renderedOwners.has(ownerKey)) {
                        renderedOwners.add(ownerKey);

                        let posX = parseFloat(c.pct_x) || 0;
                        let posY = parseFloat(c.pct_y) || 0;

                        // Dynamic pixel-perfect centroid calculation from browser SVG engine
                        try {
                            const bbox = g.getBBox();
                            if (bbox && bbox.width > 0 && bbox.height > 0) {
                                posX = ((bbox.x + bbox.width / 2) / 1007.478) * 100;
                                posY = ((bbox.y + bbox.height / 2) / 446.0) * 100;
                            }
                        } catch (err) {}

                        const pin = document.createElement('div');
                        pin.className = 'city-avatar-pin';
                        pin.style.left = posX.toFixed(2) + '%';
                        pin.style.top = posY.toFixed(2) + '%';

                        const avatar = resolveAvatar(c.avatar_url, c.platform);
                        const pIcon = getPlatformIcon(c.platform);

                        pin.innerHTML = `
                            <div class="pin-bubble" title="${c.name}: ${c.display_name || c.username}">
                                <img src="${avatar}" alt="${c.display_name || c.username}" class="pin-img" onerror="this.src='${getDefaultLogo(c.platform)}'" />
                                <span class="pin-badge">${pIcon}</span>
                            </div>
                        `;

                        // Hover on pin triggers popup and enlargement
                        pin.addEventListener('mouseenter', (e) => {
                            pin.classList.add('pop-active');
                            if (tooltip) {
                                tooltip.innerHTML = renderPopupCard(c, true);
                                tooltip.style.display = 'block';
                                tooltip.style.left = e.clientX + 'px';
                                tooltip.style.top = (e.clientY - 12) + 'px';
                            }
                        });

                        pin.addEventListener('mousemove', (e) => {
                            if (tooltip && tooltip.style.display === 'block') {
                                tooltip.style.left = e.clientX + 'px';
                                tooltip.style.top = (e.clientY - 12) + 'px';
                            }
                        });

                        pin.addEventListener('mouseleave', () => {
                            pin.classList.remove('pop-active');
                            if (tooltip) tooltip.style.display = 'none';
                        });

                        pin.addEventListener('click', (e) => {
                            e.stopPropagation();
                            openCityDrawer(c.slug);
                        });

                        pinsLayer.appendChild(pin);

                        cityPins[c.slug] = pin;
                        if (c.slug.includes('istanbul')) {
                            cityPins['istanbul'] = pin;
                            cityPins['istanbul-avrupa'] = pin;
                            cityPins['istanbul-asya'] = pin;
                        }
                    }
                }

                // ==========================================
                // Single-City Hover & Click Events
                // ==========================================
                g.addEventListener('mouseenter', (e) => {
                    const activePin = cityPins[slug];
                    if (activePin) {
                        activePin.classList.add('pop-active');
                    }
                    if (tooltip) {
                        tooltip.innerHTML = renderPopupCard(c, isOwned);
                        tooltip.style.display = 'block';
                        tooltip.style.left = e.clientX + 'px';
                        tooltip.style.top = (e.clientY - 12) + 'px';
                    }
                });

                g.addEventListener('mousemove', (e) => {
                    if (tooltip && tooltip.style.display === 'block') {
                        tooltip.style.left = e.clientX + 'px';
                        tooltip.style.top = (e.clientY - 12) + 'px';
                    }
                });

                g.addEventListener('mouseleave', () => {
                    const activePin = cityPins[slug];
                    if (activePin) {
                        activePin.classList.remove('pop-active');
                    }
                    if (tooltip) tooltip.style.display = 'none';
                });

                g.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openCityDrawer(c.slug);
                });
            });

        } catch (e) {
            console.error('Error loading map data:', e);
        }
    }

    // Modal Drawer Platform Switcher & Dynamic Placeholders
    const modalSegs = document.querySelectorAll('.seg-mini label');
    const modalUserInp = document.getElementById('modal-input-user');
    const modalTargetInp = document.getElementById('modal-input-target');

    function updateModalPlatformUI(p) {
        modalSegs.forEach(lbl => {
            const inp = lbl.querySelector('input[name="platform"]');
            if (inp && inp.value === p) {
                lbl.classList.add('active');
                inp.checked = true;
            } else {
                lbl.classList.remove('active');
            }
        });
        if (p === 'x') {
            if (modalUserInp) modalUserInp.placeholder = 'X Kullanıcı Adı (örn: @ulusoydigital)';
            if (modalTargetInp) modalTargetInp.placeholder = 'X Profil Linki (https://x.com/...)';
        } else if (p === 'instagram') {
            if (modalUserInp) modalUserInp.placeholder = 'Instagram Kullanıcı Adı (örn: @ulusoydigital)';
            if (modalTargetInp) modalTargetInp.placeholder = 'Instagram Linki (https://instagram.com/...)';
        } else if (p === 'app') {
            if (modalUserInp) modalUserInp.placeholder = 'Paket Adı / App ID (örn: com.ulusoy.app)';
            if (modalTargetInp) modalTargetInp.placeholder = 'Uygulama İndirme Linki (https://...)';
        } else if (p === 'youtube') {
            if (modalUserInp) modalUserInp.placeholder = 'YouTube Kanal Adı (örn: @ulusoydigital)';
            if (modalTargetInp) modalTargetInp.placeholder = 'YouTube Kanal/Video Linki (https://youtube.com/@...)';
        } else if (p === 'facebook') {
            if (modalUserInp) modalUserInp.placeholder = 'Facebook Sayfa Adı (örn: ulusoymedya)';
            if (modalTargetInp) modalTargetInp.placeholder = 'Facebook Sayfa Linki (https://facebook.com/...)';
        } else {
            if (modalUserInp) modalUserInp.placeholder = 'Domain / Site Adresi (örn: ulusoydigital.com)';
            if (modalTargetInp) modalTargetInp.placeholder = 'Web Sitesi Linki (https://...)';
        }
    }

    modalSegs.forEach(lbl => {
        lbl.addEventListener('click', () => {
            const inp = lbl.querySelector('input[name="platform"]');
            if (inp) updateModalPlatformUI(inp.value);
        });
    });

    if (modalUserInp) {
        modalUserInp.addEventListener('input', (e) => {
            const p = document.querySelector('.seg-mini input[name="platform"]:checked')?.value || 'site';
            const clean = e.target.value.trim().replace(/^@/, '');
            if (!clean || !modalTargetInp) return;
            if (p === 'x') modalTargetInp.value = 'https://x.com/' + clean;
            else if (p === 'instagram') modalTargetInp.value = 'https://instagram.com/' + clean;
            else if (p === 'youtube') modalTargetInp.value = 'https://youtube.com/@' + clean;
            else if (p === 'facebook') modalTargetInp.value = 'https://facebook.com/' + clean;
            else if (p === 'site' && (modalTargetInp.value.includes('x.com') || modalTargetInp.value.includes('instagram.com') || !modalTargetInp.value)) {
                modalTargetInp.value = clean.startsWith('http') ? clean : ('https://' + clean);
            }
        });
    }

    // Handle Quick Form Submission in Modal
    if (modalBidForm) {
        modalBidForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('modal-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Teklif Gönderiliyor...';
            }

            const formData = new FormData(modalBidForm);
            try {
                const resp = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await resp.json();

                if (modalBidMsg) {
                    modalBidMsg.style.display = 'block';
                    if (res.success) {
                        modalBidMsg.className = 'bid-message success';
                        modalBidMsg.textContent = res.message || 'Tebrikler! Şehrin yeni lideri sizsiniz!';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        modalBidMsg.className = 'bid-message error';
                        modalBidMsg.textContent = res.error || 'Teklif verilemedi.';
                    }
                }
            } catch (err) {
                if (modalBidMsg) {
                    modalBidMsg.style.display = 'block';
                    modalBidMsg.className = 'bid-message error';
                    modalBidMsg.textContent = 'Bağlantı hatası oluştu.';
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Teklifi Ver ve Zirveye Geç';
                }
            }
        });
    }

    // ==========================================
    // INTERACTIVE ZOOM, PAN & FULLSCREEN CONTROLS
    // ==========================================
    const mapContainer = document.getElementById('map-container-main') || document.querySelector('.map-container');
    const mapStage = document.getElementById('map-stage');
    const btnZoomIn = document.getElementById('btn-zoom-in');
    const btnZoomOut = document.getElementById('btn-zoom-out');
    const btnZoomReset = document.getElementById('btn-zoom-reset');
    const btnFsToggle = document.getElementById('btn-fullscreen-toggle');
    const btnFsExit = document.getElementById('btn-fs-exit');
    const mapFsBar = document.getElementById('map-fs-bar');

    let currentZoom = 1;
    let panX = 0;
    let panY = 0;
    let isPanning = false;
    let hasMoved = false;
    let startX = 0;
    let startY = 0;
    let isFullscreen = false;

    function applyTransform() {
        if (!mapStage) return;
        mapStage.style.transform = `translate(${panX}px, ${panY}px) scale(${currentZoom})`;
    }

    function setZoom(newZoom) {
        currentZoom = Math.min(Math.max(0.75, newZoom), 4.5);
        if (currentZoom === 1) {
            panX = 0;
            panY = 0;
        }
        applyTransform();
    }

    function resetZoomAndPan() {
        currentZoom = 1;
        panX = 0;
        panY = 0;
        applyTransform();
    }

    if (btnZoomIn) {
        btnZoomIn.addEventListener('click', (e) => {
            e.stopPropagation();
            setZoom(currentZoom + 0.25);
        });
    }

    if (btnZoomOut) {
        btnZoomOut.addEventListener('click', (e) => {
            e.stopPropagation();
            setZoom(currentZoom - 0.25);
        });
    }

    if (btnZoomReset) {
        btnZoomReset.addEventListener('click', (e) => {
            e.stopPropagation();
            resetZoomAndPan();
        });
    }

    function toggleFullscreen() {
        if (!mapContainer) return;
        isFullscreen = !isFullscreen;

        const iconExpand = btnFsToggle ? btnFsToggle.querySelector('.icon-expand') : null;
        const iconCompress = btnFsToggle ? btnFsToggle.querySelector('.icon-compress') : null;

        if (isFullscreen) {
            mapContainer.classList.add('map-fullscreen-active');
            if (mapFsBar) mapFsBar.style.display = 'flex';
            if (iconExpand) iconExpand.style.display = 'none';
            if (iconCompress) iconCompress.style.display = 'block';
            document.body.style.overflow = 'hidden';
            if (btnFsToggle) btnFsToggle.title = 'Tam Ekrandan Çık (Esc)';
        } else {
            mapContainer.classList.remove('map-fullscreen-active');
            if (mapFsBar) mapFsBar.style.display = 'none';
            if (iconExpand) iconExpand.style.display = 'block';
            if (iconCompress) iconCompress.style.display = 'none';
            document.body.style.overflow = '';
            if (btnFsToggle) btnFsToggle.title = 'Haritayı Büyüt / Tam Ekran Yap';
            resetZoomAndPan();
        }
    }

    if (btnFsToggle) {
        btnFsToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleFullscreen();
        });
    }

    if (btnFsExit) {
        btnFsExit.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isFullscreen) toggleFullscreen();
        });
    }

    // Keyboard shortcuts: Esc exits fullscreen, + - zoom
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isFullscreen) toggleFullscreen();
        } else if (isFullscreen) {
            if (e.key === '+' || e.key === '=') setZoom(currentZoom + 0.25);
            else if (e.key === '-' || e.key === '_') setZoom(currentZoom - 0.25);
        }
    });

    // Mouse wheel zoom
    if (mapContainer) {
        mapContainer.addEventListener('wheel', (e) => {
            if (isFullscreen || currentZoom > 1 || e.ctrlKey) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.15 : -0.15;
                setZoom(currentZoom + delta);
            }
        }, { passive: false });
    }

    // Drag-to-pan
    if (mapStage) {
        mapStage.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            if (e.target.closest('.city-avatar-pin') || e.target.closest('.map-tools') || e.target.closest('.map-fullscreen-bottom-bar')) return;
            isPanning = true;
            hasMoved = false;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
        });

        window.addEventListener('mousemove', (e) => {
            if (!isPanning) return;
            const newX = e.clientX - startX;
            const newY = e.clientY - startY;
            if (Math.abs(newX - panX) > 4 || Math.abs(newY - panY) > 4) {
                hasMoved = true;
            }
            panX = newX;
            panY = newY;
            applyTransform();
        });

        window.addEventListener('mouseup', () => {
            isPanning = false;
        });

        mapStage.addEventListener('click', (e) => {
            if (hasMoved) {
                e.stopImmediatePropagation();
                hasMoved = false;
            }
        }, true);

        // Double click to zoom in / reset
        mapStage.addEventListener('dblclick', (e) => {
            if (e.target.closest('.city-avatar-pin') || e.target.closest('.map-tools')) return;
            e.preventDefault();
            setZoom(currentZoom >= 2 ? 1 : currentZoom + 0.5);
        });
    }

    if (svgMap) {
        loadMap().then(() => {
            const urlParams = new URLSearchParams(window.location.search);
            const initialCity = urlParams.get('city') || (window.location.hash ? window.location.hash.substring(1) : null);
            if (initialCity) {
                setTimeout(() => {
                    openCityDrawer(initialCity);
                }, 300);
            }
        });
    }
});
