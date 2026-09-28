<?php
/*
 * Automate Limited - application bootstrap.
 *
 * Every PHP entry point (the public pages, /admin and /install) starts here.
 * It finds the private config file written by the installer, sets up error
 * handling, the timezone and the database connection, and loads the helpers.
 *
 * Nothing in /app is served to the browser: /app/.htaccess denies it.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_DIR', __DIR__);
define('APP_VERSION', '3.0.0');

require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/Db.php';
require APP_DIR . '/lib/auth.php';
require APP_DIR . '/lib/security.php';
require APP_DIR . '/lib/Mailer.php';
require APP_DIR . '/lib/uploads.php';
require APP_DIR . '/lib/markdown.php';
require APP_DIR . '/lib/domain.php';
require APP_DIR . '/lib/hr.php';
require APP_DIR . '/lib/content.php';

final class App
{
    /** @var array<string,mixed> */
    public static array $config = [];
    public static ?string $configFile = null;
    public static ?Db $db = null;
    /** @var array<string,string>|null */
    public static ?array $settings = null;
    public static bool $installed = false;
}

/* ------------------------------------------------------------------ config */
function app_config_candidates(): array
{
    $list = [];
    $env = getenv('AUTOMATE_CONFIG');
    if (is_string($env) && $env !== '') {
        $list[] = $env;
    }
    // Preferred: one level above the web root, so git deploys never touch it.
    $list[] = dirname(APP_ROOT) . '/automate-private/config.php';
    // Fallback: inside the site, protected by app/.htaccess.
    $list[] = APP_DIR . '/storage/config.php';
    return $list;
}

foreach (app_config_candidates() as $candidate) {
    if (@is_file($candidate)) {
        $loaded = require $candidate;
        if (is_array($loaded)) {
            App::$config = $loaded;
            App::$configFile = $candidate;
            App::$installed = true;
        }
        break;
    }
}

/* ------------------------------------------------------------------ errors */
$debug = (bool) (App::$config['debug'] ?? false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
$logDir = storage_path('logs');
if (@is_dir($logDir) || @mkdir($logDir, 0750, true)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

set_exception_handler(static function (Throwable $e) use ($debug): void {
    error_log('[automate] ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (is_ajax()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['ok' => false, 'error' => 'Something went wrong on our side. Please try again.']);
        return;
    }
    $detail = $debug ? '<pre style="white-space:pre-wrap;font-size:13px">' . e((string) $e) . '</pre>' : '';
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Something went wrong</title>'
        . '<div style="font:16px/1.6 system-ui,sans-serif;max-width:40rem;margin:12vh auto;padding:0 20px;color:#0B2240">'
        . '<h1 style="font-size:1.6rem;margin:0 0 8px">Something went wrong.</h1>'
        . '<p>The error has been logged. Please go back and try again.</p>' . $detail . '</div>';
});

/* ------------------------------------------------------------------ runtime */
date_default_timezone_set('Asia/Karachi');
mb_internal_encoding('UTF-8');

if (App::$installed) {
    App::$db = new Db(App::$config['db'] ?? []);
    $tz = setting('timezone', 'Asia/Karachi');
    if (is_string($tz) && in_array($tz, timezone_identifiers_list(), true)) {
        date_default_timezone_set($tz);
    }
}
