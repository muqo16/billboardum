<?php
/**
 * Billboardum.com - Configuration
 */

// Timezone & UTF-8
date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');

// Load optional .env configuration if present (eliminates hardcoding in production)
if (file_exists(__DIR__ . '/.env')) {
    $envLines = @file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $k = trim($parts[0]);
            $v = trim($parts[1], " \t\n\r\0\x0B\"'");
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
}

// Hardened Session Security Defaults
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

// App Settings (Dynamic & Overridable via Environment)
define('SITE_NAME', getenv('SITE_NAME') ?: 'Billboardum');
define('SITE_DOMAIN', getenv('SITE_DOMAIN') ?: 'billboardum.com');
define('SITE_SLOGAN', getenv('SITE_SLOGAN') ?: "Türkiye'nin Reklam Vitrini");

// Admin Security (Fallback Defaults - Can be managed from DB panel or .env)
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');
define('ADMIN_SECRET_KEY', getenv('ADMIN_SECRET_KEY') ?: 'bb_admin_2026');

// Database file (Protected directory, overridable via environment)
define('DB_PATH', getenv('DB_PATH') ?: (__DIR__ . '/database/billboardum.db'));

// Minimum bid amount
define('MIN_BID_AMOUNT', (int)(getenv('MIN_BID_AMOUNT') ?: 10));

