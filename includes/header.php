<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security HTTP Headers
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}
require_once __DIR__ . '/functions.php';
increment_site_visits();
$stats = get_site_stats();
$currentUser = get_current_user_info();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#f7f2e9" />
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME . " — " . SITE_SLOGAN) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? "Türkiye haritasında en yüksek teklifi ver, şehrin sahibi ol. Web siten, X profilin, Instagram hesabın ya da uygulaman Türkiye genelinde yerini alsın.") ?>" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&family=Playfair+Display:ital,wght@0,400..700;1,600&display=swap" rel="stylesheet" />
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,%3csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%20fill='none'%3e%3cpath%20d='M18%206%20L18%2010%20M32%206%20L32%2010%20M46%206%20L46%2010'%20stroke='%234a4335'%20stroke-width='2'%20stroke-linecap='round'/%3e%3ccircle%20cx='18'%20cy='5'%20r='2.5'%20fill='%23fde047'%20stroke='%234a4335'%20stroke-width='1.2'/%3e%3ccircle%20cx='32'%20cy='5'%20r='2.5'%20fill='%23fde047'%20stroke='%234a4335'%20stroke-width='1.2'/%3e%3ccircle%20cx='46'%20cy='5'%20r='2.5'%20fill='%23fde047'%20stroke='%234a4335'%20stroke-width='1.2'/%3e%3crect%20x='7'%20y='10'%20width='50'%20height='32'%20rx='3.5'%20fill='%23eef1de'%20stroke='%234a4335'%20stroke-width='2'/%3e%3crect%20x='11'%20y='14'%20width='42'%20height='24'%20rx='2'%20fill='%23fbf7ec'%20stroke='%234a4335'%20stroke-width='1.2'/%3e%3cpath%20d='M18%2026%20L26%2021%20L26%2031%20Z'%20fill='%23d97706'%20stroke='%234a4335'%20stroke-width='1.2'%20stroke-linejoin='round'/%3e%3ccircle%20cx='32'%20cy='26'%20r='3.5'%20fill='%234a4335'/%3e%3cpath%20d='M38%2021%20C41%2023.5%2041%2028.5%2038%2031'%20stroke='%234a4335'%20stroke-width='1.5'%20stroke-linecap='round'/%3e%3cpath%20d='M43%2018%20C48%2022%2048%2030%2043%2034'%20stroke='%234a4335'%20stroke-width='1.5'%20stroke-linecap='round'/%3e%3cpath%20d='M20%2042%20L20%2057%20M44%2042%20L44%2057'%20stroke='%234a4335'%20stroke-width='2.8'%20stroke-linecap='round'/%3e%3cpath%20d='M20%2046%20L32%2054%20L44%2046'%20stroke='%234a4335'%20stroke-width='1.8'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M12%2057%20L52%2057'%20stroke='%234a4335'%20stroke-width='2.8'%20stroke-linecap='round'/%3e%3c/svg%3e" />
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/map.css" />
</head>
<body>
    <div class="app-wrap">
        <header class="top">
            <a class="brand" href="/">
                <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M18 6 L18 10 M32 6 L32 10 M46 6 L46 10" stroke="#4a4335" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="18" cy="5" r="2.5" fill="#fde047" stroke="#4a4335" stroke-width="1.2"/>
                    <circle cx="32" cy="5" r="2.5" fill="#fde047" stroke="#4a4335" stroke-width="1.2"/>
                    <circle cx="46" cy="5" r="2.5" fill="#fde047" stroke="#4a4335" stroke-width="1.2"/>
                    <rect x="7" y="10" width="50" height="32" rx="3.5" fill="#eef1de" stroke="#4a4335" stroke-width="2"/>
                    <rect x="11" y="14" width="42" height="24" rx="2" fill="#fbf7ec" stroke="#4a4335" stroke-width="1.2"/>
                    <path d="M18 26 L26 21 L26 31 Z" fill="#d97706" stroke="#4a4335" stroke-width="1.2" stroke-linejoin="round"/>
                    <circle cx="32" cy="26" r="3.5" fill="#4a4335"/>
                    <path d="M38 21 C41 23.5 41 28.5 38 31" stroke="#4a4335" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M43 18 C48 22 48 30 43 34" stroke="#4a4335" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M20 42 L20 57 M44 42 L44 57" stroke="#4a4335" stroke-width="2.8" stroke-linecap="round"/>
                    <path d="M20 46 L32 54 L44 46" stroke="#4a4335" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 57 L52 57" stroke="#4a4335" stroke-width="2.8" stroke-linecap="round"/>
                </svg>
                <span><?= SITE_NAME ?><span class="tld">.com</span></span>
            </a>

            <div class="top-nav">
                <a href="/toplu.php" class="nav-link">Toplu Alım</a>
                <a href="/reklam-ver.php" class="btn-cta">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    <span class="btn-cta-full">Reklam Ver / Şehir Kap</span>
                    <span class="btn-cta-short">Şehir Kap 🚀</span>
                </a>

                <?php if ($currentUser): ?>
                    <a href="/panel/" class="user-pill" title="Reklam Panelin">
                        <span class="u-icon">👤</span>
                        <span class="u-name"><?= htmlspecialchars($currentUser['name']) ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="pulse-wrap">
            <div class="pulse">
                <span class="dot" aria-hidden="true"></span> 
                <strong><?= format_number($stats['visits']) ?></strong>&nbsp;Ziyaretçi
                <span class="sep">·</span> 
                <strong><?= format_number($stats['volume']) ?>₺</strong>&nbsp;Hacim 
                <span class="sep">·</span> 
                <strong><?= $stats['active_cities'] ?> / 81</strong>&nbsp;Aktif Şehir
                <span class="sep">·</span> 
                <strong><?= format_number($stats['total_clicks']) ?></strong>&nbsp;Tıklama
            </div>
        </div>
