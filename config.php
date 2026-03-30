<?php
// ============================================================
//  SwipeTRX Rewards — MASTER CONFIGURATION
// ============================================================

// ── DATABASE (Railway MySQL) ──────────────────────────────────
// Railway provides MYSQL_URL env var — we parse it automatically
if (getenv('MYSQL_URL')) {
    $db = parse_url(getenv('MYSQL_URL'));
    define('DB_HOST',    $db['host']);
    define('DB_PORT',    $db['port'] ?? 3306);
    define('DB_NAME',    ltrim($db['path'], '/'));
    define('DB_USER',    $db['user']);
    define('DB_PASS',    $db['pass']);
} else {
    define('DB_HOST',    getenv('MYSQLHOST')    ?: 'mysql.railway.internal');
    define('DB_PORT',    getenv('MYSQLPORT')    ?: 3306);
    define('DB_NAME',    getenv('MYSQLDATABASE') ?: 'railway');
    define('DB_USER',    getenv('MYSQLUSER')    ?: 'root');
    define('DB_PASS',    getenv('MYSQLPASSWORD') ?: 'FSYPAqEZFMYSeyJyRDmILULLoWzKxygm');
}
define('DB_CHARSET', 'utf8mb4');

// ── DB CONNECTION ─────────────────────────────────────────────
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
// Add this function to config.php or a helpers file
// Call it when creating a new user in mobile.php registration

if (!function_exists('generate_user_code')) {
        function generate_user_code($db) {
                $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        do {
                                    $code = '';
                                                for ($i = 0; $i < 6; $i++) {
                                                                $code .= $chars[random_int(0, strlen($chars) - 1)];
                                                                            }
                                                                                        $check = $db->prepare('SELECT id FROM users WHERE user_code = ?');
                                                                                                    $check->execute([$code]);
                                                                                                            } while ($check->fetch());
                                                                                                                    return $code;
                                                                                                                        }
                                                                                                                        }

                                                                    // When inserting a new user, add user_code to the INSERT:
                                                                    // $code = generate_user_code($db);
                                                                    // INSERT INTO users (..., user_code, ...) VALUES (..., ?, ...)
                                                                    // and pass $code in the execute array
                                                                    
// ── SITE ─────────────────────────────────────────────────────
if (!defined('SITE_NAME'))  define('SITE_NAME',  'SwipeTRX Rewards');
if (!defined('SITE_URL'))   define('SITE_URL',   'https://yourdomain.com');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'support@yourdomain.com');
if (!defined('TIMEZONE'))   define('TIMEZONE',   'UTC');
date_default_timezone_set(TIMEZONE);

// ── SECURITY ─────────────────────────────────────────────────
if (!defined('APP_SECRET'))     define('APP_SECRET',     'CHANGE_ME_32CHAR_RANDOM_STRING');
if (!defined('SESSION_NAME'))   define('SESSION_NAME',   'swibetrx_sess');
if (!defined('COOKIE_SECURE'))  define('COOKIE_SECURE',  true);
if (!defined('COOKIE_HTTPONLY'))define('COOKIE_HTTPONLY', true);

// ── TRX / CRYPTO ─────────────────────────────────────────────
// TRON API - YOUR KEYS
define('TRONGRID_API_KEY', '47bc6e0a-3886-4177-b679-2ba8b175a87c');
define('TRX_MASTER_WALLET', 'TCXcMNqcy4fvUAKsF6apkdgSCyrcL8ryHJ');
define('MASTER_TRX_WALLET', 'TCXcMNqcy4fvUAKsF6apkdgSCyrcL8ryHJ');
define('TRONGRID_BASE_URL', 'https://api.trongrid.io');

// TRX Settings
define('TRX_MIN_DEPOSIT_EUR', 25.00);
define('TRX_CONFIRMATIONS', 20); // TRON confirmations needed
// TESTING MODE - FREE TRX DEPOSITS
define('TRON_TESTNET', true); // ← Set to true for testing
define('TRON_NETWORK', 'shasta'); // testnet
define('TEST_MASTER_TRX', 'TCXcMNqcy4fvUAKsF6apkdgSCyrcL8ryHJ'); // your master stays same

// Testnet API (free unlimited TRX)
define('TRONGRID_SHASHA_URL', 'https://api.shasta.trongrid.io');

// ── EARNINGS ─────────────────────────────────────────────────
if (!defined('VIP_EARN_PER_VIDEO')) define('VIP_EARN_PER_VIDEO', [
    1 => 0.39, 2 => 0.50, 3 => 0.65,
    4 => 0.85, 5 => 1.10, 6 => 1.40, 7 => 2.00,
]);
if (!defined('VIP_DAILY_LIMIT')) define('VIP_DAILY_LIMIT', [
    1 => 7,  2 => 14, 3 => 21,
    4 => 28, 5 => 35, 6 => 42, 7 => 49,
]);
if (!defined('VIP_DEPOSIT_REQ')) define('VIP_DEPOSIT_REQ', [
    1 => 0,   2 => 30,  3 => 60,
    4 => 120, 5 => 240, 6 => 480, 7 => 960,
]);
if (!defined('VIP_REF_REQ')) define('VIP_REF_REQ', [
    1 => 0, 2 => 2,  3 => 4,
    4 => 6, 5 => 8,  6 => 10, 7 => 14,
]);
if (!defined('VIP_UNLOCK_BONUS')) define('VIP_UNLOCK_BONUS', [
    1 => 0,   2 => 5,   3 => 20,
    4 => 50,  5 => 100, 6 => 150, 7 => 250,
]);

// ── BONUSES ──────────────────────────────────────────────────
if (!defined('BONUS_WELCOME'))             define('BONUS_WELCOME',             2.00);
if (!defined('BONUS_REFERRAL_REGISTER'))   define('BONUS_REFERRAL_REGISTER',   5.00);
if (!defined('BONUS_REFERRAL_VIP2'))       define('BONUS_REFERRAL_VIP2',       8.00);
if (!defined('BONUS_REFERRAL_VIP3'))       define('BONUS_REFERRAL_VIP3',      12.00);
if (!defined('BONUS_REFERRAL_VIP4'))       define('BONUS_REFERRAL_VIP4',      18.00);
if (!defined('BONUS_REFERRAL_VIP5PLUS'))   define('BONUS_REFERRAL_VIP5PLUS',  25.00);
if (!defined('BONUS_10_REFS_MILESTONE'))   define('BONUS_10_REFS_MILESTONE', 100.00);

// ── WITHDRAWALS ──────────────────────────────────────────────
if (!defined('WITHDRAWAL_MIN_EUR'))      define('WITHDRAWAL_MIN_EUR',      10.00);
if (!defined('WITHDRAWAL_COMMISSION'))   define('WITHDRAWAL_COMMISSION',    0.20);
if (!defined('WITHDRAWAL_FREE_DAYS'))    define('WITHDRAWAL_FREE_DAYS',    30);
if (!defined('WITHDRAWAL_PROCESSING_H')) define('WITHDRAWAL_PROCESSING_H', 48);

// ── LANGUAGE ─────────────────────────────────────────────────
if (!defined('DEFAULT_LANG'))    define('DEFAULT_LANG', 'en');
if (!defined('SUPPORTED_LANGS')) define('SUPPORTED_LANGS', [
    'en' => '🇬🇧 English',
    'bg' => '🇧🇬 Български',
    'de' => '🇩🇪 Deutsch',
    'fr' => '🇫🇷 Français',
    'es' => '🇪🇸 Español',
    'it' => '🇮🇹 Italiano',
    'ro' => '🇷🇴 Română',
    'nl' => '🇳🇱 Nederlands',
    'pl' => '🇵🇱 Polski',
    'pt' => '🇵🇹 Português',
    'el' => '🇬🇷 Ελληνικά',
    'hu' => '🇭🇺 Magyar',
    'cs' => '🇨🇿 Čeština',
    'sk' => '🇸🇰 Slovenčina',
    'hr' => '🇭🇷 Hrvatski',
]);

// ── EMAIL (SMTP) ──────────────────────────────────────────────
if (!defined('MAIL_HOST'))      define('MAIL_HOST',      'smtp.yourmailprovider.com');
if (!defined('MAIL_PORT'))      define('MAIL_PORT',      587);
if (!defined('MAIL_SECURE'))    define('MAIL_SECURE',    'tls');
if (!defined('MAIL_USER'))      define('MAIL_USER',      'noreply@yourdomain.com');
if (!defined('MAIL_PASS'))      define('MAIL_PASS',      'CHANGE_ME_SMTP_PASSWORD');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', SITE_NAME);

// ── ADMIN ─────────────────────────────────────────────────────
if (!defined('ADMIN_EMAIL'))    define('ADMIN_EMAIL',    'admin@yourdomain.com');
if (!defined('ADMIN_PATH'))     define('ADMIN_PATH',     '/admin');
if (!defined('ADMIN_USER_IDS')) define('ADMIN_USER_IDS', '1');

// ── DEBUG ─────────────────────────────────────────────────────
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
