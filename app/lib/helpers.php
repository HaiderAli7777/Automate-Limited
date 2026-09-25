<?php
/* General helpers: escaping, URLs, requests, flash messages, formatting, views. */
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function db(): Db
{
    if (!App::$db) {
        throw new RuntimeException('The database is not configured yet. Run the installer at /install/.');
    }
    return App::$db;
}

function app_installed(): bool
{
    return App::$installed && App::$db !== null;
}

function config(string $key, $default = null)
{
    return App::$config[$key] ?? $default;
}

function storage_path(string $sub = ''): string
{
    $base = rtrim((string) (App::$config['storage_path'] ?? (APP_DIR . '/storage')), '/');
    return $sub === '' ? $base : $base . '/' . ltrim($sub, '/');
}

/* ------------------------------------------------------------------ URLs */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $base = '';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? @realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
    $root = @realpath(APP_ROOT);
    if ($doc && $root && str_starts_with($root, $doc)) {
        $base = rtrim(str_replace('\\', '/', substr($root, strlen($doc))), '/');
    } elseif (!empty(App::$config['base_url'])) {
        $base = rtrim((string) parse_url((string) App::$config['base_url'], PHP_URL_PATH), '/');
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function site_origin(): string
{
    if (!empty(App::$config['base_url'])) {
        $u = parse_url((string) App::$config['base_url']);
        if (!empty($u['host'])) {
            return ($u['scheme'] ?? 'https') . '://' . $u['host'] . (isset($u['port']) ? ':' . $u['port'] : '');
        }
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'automateltd.com';
    return (is_https() ? 'https' : 'http') . '://' . $host;
}

function abs_url(string $path = ''): string
{
    return site_origin() . url($path);
}

function asset(string $path): string
{
    $file = APP_ROOT . '/' . ltrim($path, '/');
    $v = @filemtime($file);
    return url($path) . '?v=' . ($v ?: APP_VERSION);
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }
    return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function current_path(): string
{
    return (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
}

/** Merge overrides into the current query string. Null removes a key. */
function qs(array $overrides = []): string
{
    $q = array_merge($_GET, $overrides);
    unset($q['r']);
    foreach ($q as $k => $v) {
        if ($v === null || $v === '') {
            unset($q[$k]);
        }
    }
    return $q ? '?' . http_build_query($q) : '';
}

/* ------------------------------------------------------------------ requests */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_ajax(): bool
{
    $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
        || str_contains($accept, 'application/json');
}

/** A trimmed string from POST, falling back to GET. */
function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    if (is_array($v) || $v === null) {
        return $default;
    }
    return trim((string) $v);
}

function input_int(string $key, int $default = 0): int
{
    $v = input($key);
    return ($v !== '' && is_numeric($v)) ? (int) $v : $default;
}

function input_array(string $key): array
{
    $v = $_POST[$key] ?? $_GET[$key] ?? [];
    return is_array($v) ? $v : [];
}

/** Null when blank; used for optional columns. */
function nullable(string $v): ?string
{
    $v = trim($v);
    return $v === '' ? null : $v;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function back(string $fallback): never
{
    $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($ref !== '' && $host !== '' && parse_url($ref, PHP_URL_HOST) === $host) {
        redirect($ref);
    }
    redirect($fallback);
}

function json_out($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    if (is_ajax()) {
        json_out(['ok' => false, 'error' => $message ?: 'Request failed.'], $code);
    }
    $titles = [403 => 'You don\'t have access to this.', 404 => 'That page isn\'t here.', 419 => 'This form expired.', 429 => 'Too many attempts.'];
    $title = $titles[$code] ?? 'Something went wrong.';
    if (auth_user() && str_starts_with(current_path(), url('admin'))) {
        render('admin/error', ['code' => $code, 'title' => $title, 'message' => $message], 'admin/layout');
        exit;
    }
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($title) . '</title>'
        . '<div style="font:16px/1.6 system-ui,sans-serif;max-width:40rem;margin:12vh auto;padding:0 20px;color:#0B2240">'
        . '<h1 style="font-size:1.6rem;margin:0 0 8px">' . e($title) . '</h1><p>' . e($message) . '</p>'
        . '<p><a href="' . e(url('')) . '" style="color:#0072AE">Go to the homepage</a></p></div>';
    exit;
}

/* ------------------------------------------------------------------ session + flash */
function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '28800');
    session_name('automate_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path() . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function flash(string $type, string $message): void
{
    session_boot();
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return [];
    }
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/** Remember submitted values so a form can be re-filled after a failed save. */
function remember_input(array $errors = []): void
{
    session_boot();
    $_SESSION['_old'] = $_POST;
    $_SESSION['_errors'] = $errors;
}

function old_input(): array
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    return $old;
}

function has_old(): bool
{
    return old_input() !== [];
}

function old(string $key, $default = ''): string
{
    $old = old_input();
    $v = array_key_exists($key, $old) ? $old[$key] : $default;
    return is_array($v) ? '' : (string) ($v ?? '');
}

function old_array(string $key): ?array
{
    $v = old_input()[$key] ?? null;
    return is_array($v) ? $v : null;
}

function form_errors(): array
{
    static $errors = null;
    if ($errors === null) {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
    }
    return $errors;
}

/* ------------------------------------------------------------------ settings */
function settings_all(): array
{
    if (App::$settings === null) {
        App::$settings = [];
        if (App::$db) {
            try {
                foreach (App::$db->all('SELECT skey, svalue FROM settings') as $row) {
                    App::$settings[$row['skey']] = (string) $row['svalue'];
                }
            } catch (Throwable $e) {
                App::$settings = [];
            }
        }
    }
    return App::$settings;
}

function setting(string $key, $default = null)
{
    $all = settings_all();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}

function set_setting(string $key, $value): void
{
    $value = (string) ($value ?? '');
    db()->run('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$key, $value]);
    settings_all();
    App::$settings[$key] = $value;
}

function company_name(): string
{
    return (string) setting('company_name', 'Automate Limited');
}

/* ------------------------------------------------------------------ dates + numbers */
function now(): string
{
    return date('Y-m-d H:i:s');
}

function today(): string
{
    return date('Y-m-d');
}

function ts(?string $dt): ?int
{
    if ($dt === null || $dt === '' || str_starts_with($dt, '0000')) {
        return null;
    }
    $t = strtotime($dt);
    return $t === false ? null : $t;
}

function fmt_date(?string $dt, string $format = 'j M Y'): string
{
    $t = ts($dt);
    return $t === null ? '' : date($format, $t);
}

function fmt_datetime(?string $dt): string
{
    return fmt_date($dt, 'j M Y, g:i a');
}

function fmt_time(?string $dt): string
{
    return fmt_date($dt, 'g:i a');
}

/** "Today", "Tomorrow", "Mon 3 Oct" style labels for agendas. */
function fmt_day(?string $dt): string
{
    $t = ts($dt);
    if ($t === null) {
        return '';
    }
    $d = date('Y-m-d', $t);
    if ($d === today()) {
        return 'Today';
    }
    if ($d === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    if ($d === date('Y-m-d', strtotime('-1 day'))) {
        return 'Yesterday';
    }
    return date(date('Y', $t) === date('Y') ? 'D j M' : 'D j M Y', $t);
}

function time_ago(?string $dt): string
{
    $t = ts($dt);
    if ($t === null) {
        return '';
    }
    $diff = time() - $t;
    $future = $diff < 0;
    $diff = abs($diff);
    if ($diff < 60) {
        return $future ? 'in a moment' : 'just now';
    }
    $units = [[31536000, 'year'], [2592000, 'month'], [604800, 'week'], [86400, 'day'], [3600, 'hour'], [60, 'minute']];
    foreach ($units as [$secs, $name]) {
        if ($diff >= $secs) {
            $n = (int) floor($diff / $secs);
            $label = $n . ' ' . $name . ($n === 1 ? '' : 's');
            return $future ? 'in ' . $label : $label . ' ago';
        }
    }
    return '';
}

function days_since(?string $dt): int
{
    $t = ts($dt);
    return $t === null ? 0 : max(0, (int) floor((time() - $t) / 86400));
}

/** Parse a date or datetime from a form field, returning SQL format or null. */
function parse_dt(string $value, bool $withTime = true): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    $t = strtotime(str_replace('T', ' ', $value));
    if ($t === false) {
        return null;
    }
    return date($withTime ? 'Y-m-d H:i:s' : 'Y-m-d', $t);
}

function dt_input(?string $dt): string
{
    $t = ts($dt);
    return $t === null ? '' : date('Y-m-d\TH:i', $t);
}

function fmt_money($amount, ?string $currency = null): string
{
    if ($amount === null || $amount === '') {
        return '';
    }
    $currency = $currency ?: (string) setting('default_currency', 'PKR');
    $n = (float) $amount;
    $decimals = abs($n - round($n)) > 0.001 ? 2 : 0;
    return $currency . ' ' . number_format($n, $decimals);
}

/** 1,284 / 12.9K / 4.2M */
function compact_number($n): string
{
    $n = (float) $n;
    $abs = abs($n);
    if ($abs >= 1_000_000_000) {
        return rtrim(rtrim(number_format($n / 1_000_000_000, 1), '0'), '.') . 'B';
    }
    if ($abs >= 1_000_000) {
        return rtrim(rtrim(number_format($n / 1_000_000, 1), '0'), '.') . 'M';
    }
    if ($abs >= 10_000) {
        return rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'K';
    }
    return number_format($n, abs($n - round($n)) > 0.001 ? 1 : 0);
}

function compact_money($amount, ?string $currency = null): string
{
    $currency = $currency ?: (string) setting('default_currency', 'PKR');
    return $currency . ' ' . compact_number($amount);
}

/* ------------------------------------------------------------------ strings */
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = (string) preg_replace('~[^a-z0-9]+~', '-', $s);
    $s = trim($s, '-');
    return $s === '' ? 'item' : substr($s, 0, 80);
}

function str_limit(string $s, int $n): string
{
    $s = trim((string) preg_replace('/\s+/', ' ', $s));
    return mb_strlen($s) > $n ? rtrim(mb_substr($s, 0, $n - 1)) . '…' : $s;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: '?';
}

function avatar_hue(string $seed): int
{
    return (int) (hexdec(substr(md5(strtolower($seed)), 0, 4)) % 360);
}

function avatar(string $name, string $size = ''): string
{
    return '<span class="avatar' . ($size ? ' avatar--' . e($size) : '') . '" style="--h:' . avatar_hue($name) . '" aria-hidden="true">' . e(initials($name)) . '</span>';
}

function plural(int $n, string $one, ?string $many = null): string
{
    return number_format($n) . ' ' . ($n === 1 ? $one : ($many ?? $one . 's'));
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 190;
}

function valid_url(string $u): bool
{
    return $u === '' || (bool) preg_match('~^https?://[^\s]+$~i', $u);
}

function normalise_url(string $u): string
{
    $u = trim($u);
    if ($u !== '' && !preg_match('~^https?://~i', $u)) {
        $u = 'https://' . $u;
    }
    return $u;
}

/** Split a comma separated tag string into a clean list. */
function tag_list(?string $tags): array
{
    $out = [];
    foreach (explode(',', (string) $tags) as $t) {
        $t = trim($t);
        if ($t !== '' && !in_array(mb_strtolower($t), array_map('mb_strtolower', $out), true)) {
            $out[] = mb_substr($t, 0, 40);
        }
    }
    return $out;
}

/* ------------------------------------------------------------------ pagination */
function paginate(int $total, int $perPage = 25): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, input_int('page', 1)), $pages);
    return ['total' => $total, 'per' => $perPage, 'page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage];
}

/* ------------------------------------------------------------------ views */
function view_file(string $view): string
{
    $file = APP_DIR . '/views/' . $view . '.php';
    if (!is_file($file)) {
        throw new RuntimeException('Missing view: ' . $view);
    }
    return $file;
}

/* The double-underscore names keep template variables such as $view or $layout
   from colliding with these functions' own locals. */
function partial(string $__view, array $__data = []): void
{
    extract($__data, EXTR_SKIP);
    require view_file($__view);
}

function render(string $__view, array $__data = [], ?string $__layout = null): void
{
    extract($__data, EXTR_SKIP);
    ob_start();
    require view_file($__view);
    $content = (string) ob_get_clean();
    if ($__layout === null) {
        echo $content;
        return;
    }
    require view_file($__layout);
}

function selected($a, $b): string
{
    return (string) $a === (string) $b ? ' selected' : '';
}

function checked(bool $on): string
{
    return $on ? ' checked' : '';
}

function icon(string $name, string $class = 'ic'): string
{
    static $sprite = null;
    $sprite ??= e(asset('assets/img/icons.svg'));
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="' . $sprite . '#i-' . e($name) . '"/></svg>';
}
