<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $db = null;
    if ($db === null) {
        $dbFile = DB_PATH;
        $needInit = !file_exists($dbFile) || filesize($dbFile) === 0;
        
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON;');
        $db->exec('PRAGMA journal_mode = WAL;');

        if ($needInit) {
            init_database($db);
        }
    }
    return $db;
}

function init_database(PDO $db) {
    // Create Tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS site_stats (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            visits INTEGER NOT NULL DEFAULT 14250,
            volume INTEGER NOT NULL DEFAULT 4950,
            payments_on INTEGER NOT NULL DEFAULT 1,
            paytr_test INTEGER NOT NULL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS owners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            platform TEXT NOT NULL,
            username TEXT NOT NULL,
            display_name TEXT,
            target_url TEXT,
            avatar_url TEXT,
            instagram_url TEXT,
            maps_url TEXT,
            youtube_url TEXT,
            x_url TEXT,
            facebook_url TEXT,
            total_spent INTEGER NOT NULL DEFAULT 0,
            total_clicks INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS cities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            plate_code TEXT NOT NULL,
            area_code TEXT,
            name TEXT NOT NULL,
            coord_x REAL DEFAULT 0,
            coord_y REAL DEFAULT 0,
            pct_x REAL DEFAULT 0,
            pct_y REAL DEFAULT 0,
            current_owner_id INTEGER,
            current_amount INTEGER NOT NULL DEFAULT 10,
            total_clicks INTEGER NOT NULL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (current_owner_id) REFERENCES owners(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS bids (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            city_id INTEGER NOT NULL,
            owner_id INTEGER NOT NULL,
            amount INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'approved',
            payment_method TEXT DEFAULT 'direct',
            payment_note TEXT,
            sender_name TEXT,
            sender_phone TEXT,
            receipt_no TEXT,
            receipt_file TEXT,
            ip_address TEXT,
            paid_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
            FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS clicks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            city_id INTEGER NOT NULL,
            owner_id INTEGER,
            ip_address TEXT,
            user_agent TEXT,
            referer TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS visitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            session_id TEXT,
            user_agent TEXT,
            page_url TEXT,
            referer TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_visitors_ip ON visitors(ip);
        CREATE INDEX IF NOT EXISTS idx_visitors_created ON visitors(created_at);
    ");

    // Insert site stats (defaults to 0, dynamically calculated from real data)
    $stmt = $db->prepare("INSERT OR IGNORE INTO site_stats (id, visits, volume) VALUES (1, 0, 0)");
    $stmt->execute();

    // Insert default demo user & admin
    $userPass = password_hash('123456', PASSWORD_DEFAULT);
    $uStmt = $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $uStmt->execute(['Demo Reklam Veren', 'demo@billboardum.com', $userPass, 'user']);
    $demoUserId = $db->lastInsertId();

    // 4 Realistic Flagship Ads / Owners
    $flagships = [
        [
            'platform' => 'site',
            'username' => 'trendyol.com',
            'display_name' => 'Trendyol',
            'target_url' => 'https://www.trendyol.com',
            'avatar_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=trendyol&backgroundColor=f97316',
            'city_slugs' => ['istanbul-avrupa', 'istanbul-asya'],
            'amount' => 2500,
            'clicks' => 342
        ],
        [
            'platform' => 'app',
            'username' => 'com.peakgames.toyblast',
            'display_name' => 'Toy Blast',
            'target_url' => 'https://play.google.com/store/apps/details?id=com.peakgames.toyblast',
            'avatar_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=toyblast&backgroundColor=3b82f6',
            'city_slugs' => ['ankara'],
            'amount' => 1250,
            'clicks' => 189
        ],
        [
            'platform' => 'x',
            'username' => 'techistanbul',
            'display_name' => 'Tech Istanbul',
            'target_url' => 'https://x.com/techistanbul',
            'avatar_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=techistanbul&backgroundColor=06b6d4',
            'city_slugs' => ['izmir'],
            'amount' => 750,
            'clicks' => 94
        ],
        [
            'platform' => 'instagram',
            'username' => 'kahvekeyfi',
            'display_name' => 'Kahve Keyfi',
            'target_url' => 'https://instagram.com/kahvekeyfi',
            'avatar_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=kahvekeyfi&backgroundColor=eab308',
            'city_slugs' => ['bursa'],
            'amount' => 450,
            'clicks' => 62
        ]
    ];

    $ownerMap = [];
    foreach ($flagships as $f) {
        $insOwner = $db->prepare("
            INSERT INTO owners (user_id, platform, username, display_name, target_url, avatar_url, total_spent, total_clicks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insOwner->execute([
            $demoUserId,
            $f['platform'],
            $f['username'],
            $f['display_name'],
            $f['target_url'],
            $f['avatar_url'],
            $f['amount'],
            $f['clicks']
        ]);
        $oId = $db->lastInsertId();
        $ownerMap[$f['display_name']] = [
            'id' => $oId,
            'slugs' => $f['city_slugs'],
            'amount' => $f['amount'],
            'clicks' => $f['clicks'],
            'info' => $f
        ];
    }

    // Load coordinates & cities
    $citiesJson = file_get_contents(__DIR__ . '/cities_extracted.json');
    $citiesData = json_decode($citiesJson, true) ?: [];

    $coordsJson = file_get_contents(__DIR__ . '/city_coords.json');
    $coordsData = json_decode($coordsJson, true) ?: [];

    $names = [
        "adana" => "Adana", "adiyaman" => "Adıyaman", "afyonkarahisar" => "Afyonkarahisar", "agri" => "Ağrı", "amasya" => "Amasya",
        "ankara" => "Ankara", "antalya" => "Antalya", "artvin" => "Artvin", "aydin" => "Aydın", "balikesir" => "Balıkesir",
        "bilecik" => "Bilecik", "bingol" => "Bingöl", "bitlis" => "Bitlis", "bolu" => "Bolu", "burdur" => "Burdur",
        "bursa" => "Bursa", "canakkale" => "Çanakkale", "cankiri" => "Çankırı", "corum" => "Çorum", "denizli" => "Denizli",
        "diyarbakir" => "Diyarbakır", "edirne" => "Edirne", "elazig" => "Elazığ", "erzincan" => "Erzincan", "erzurum" => "Erzurum",
        "eskisehir" => "Eskişehir", "gaziantep" => "Gaziantep", "giresun" => "Giresun", "gumushane" => "Gümüşhane", "hakkari" => "Hakkâri",
        "hatay" => "Hatay", "isparta" => "Isparta", "mersin" => "Mersin", "istanbul" => "İstanbul", "istanbul-avrupa" => "İstanbul (Avrupa)", "istanbul-asya" => "İstanbul (Asya)",
        "izmir" => "İzmir", "kars" => "Kars", "kastamonu" => "Kastamonu", "kayseri" => "Kayseri", "kirklareli" => "Kırklareli",
        "kirsehir" => "Kırşehir", "kocaeli" => "Kocaeli", "konya" => "Konya", "kutahya" => "Kütahya", "malatya" => "Malatya",
        "manisa" => "Manisa", "kahramanmaras" => "Kahramanmaraş", "mardin" => "Mardin", "mugla" => "Muğla", "mus" => "Muş",
        "nevsehir" => "Nevşehir", "nigde" => "Niğde", "ordu" => "Ordu", "rize" => "Rize", "sakarya" => "Sakarya",
        "samsun" => "Samsun", "siirt" => "Siirt", "sinop" => "Sinop", "sivas" => "Sivas", "tekirdag" => "Tekirdağ",
        "tokat" => "Tokat", "trabzon" => "Trabzon", "tunceli" => "Tunceli", "sanliurfa" => "Şanlıurfa", "usak" => "Uşak",
        "van" => "Van", "yozgat" => "Yozgat", "zonguldak" => "Zonguldak", "aksaray" => "Aksaray", "bayburt" => "Bayburt",
        "karaman" => "Karaman", "kirikkale" => "Kırıkkale", "batman" => "Batman", "sirnak" => "Şırnak", "bartin" => "Bartın",
        "ardahan" => "Ardahan", "igdir" => "Iğdır", "yalova" => "Yalova", "karabuk" => "Karabük", "kilis" => "Kilis",
        "osmaniye" => "Osmaniye", "duzce" => "Düzce"
    ];

    $seenSlugs = [];
    foreach ($citiesData as $c) {
        $slug = $c['slug'];
        if (isset($seenSlugs[$slug])) continue;
        $seenSlugs[$slug] = true;

        $cName = $names[$slug] ?? $c['name'];
        $cCoord = $coordsData[$slug] ?? ['x' => 0, 'y' => 0, 'pct_x' => 0, 'pct_y' => 0];

        $ownerId = null;
        $amount = 10;
        $clicks = 0;

        foreach ($ownerMap as $om) {
            if (in_array($slug, $om['slugs'])) {
                $ownerId = $om['id'];
                $amount = $om['amount'];
                $clicks = $om['clicks'];
                break;
            }
        }

        $stmt = $db->prepare("
            INSERT INTO cities (slug, plate_code, area_code, name, coord_x, coord_y, pct_x, pct_y, current_owner_id, current_amount, total_clicks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $slug,
            $c['plate_code'],
            $c['area_code'],
            $cName,
            $cCoord['x'],
            $cCoord['y'],
            $cCoord['pct_x'],
            $cCoord['pct_y'],
            $ownerId,
            $amount,
            $clicks
        ]);
        $cityId = $db->lastInsertId();

        if ($ownerId) {
            $bStmt = $db->prepare("INSERT INTO bids (city_id, owner_id, amount, status) VALUES (?, ?, ?, 'approved')");
            $bStmt->execute([$cityId, $ownerId, $amount]);
        }
    }
}
