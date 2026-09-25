<?php
/* CSRF, public form tokens, rate limiting and secret encryption. */
declare(strict_types=1);

function app_key(): string
{
    $k = (string) config('app_key', '');
    if ($k === '') {
        // Only reachable before install; public forms are closed until then anyway.
        $k = hash('sha256', APP_ROOT . php_uname());
    }
    return $k;
}

/* ------------------------------------------------------------------ CSRF (signed-in areas) */
function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    session_boot();
    $sent = (string) ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($sent === '' || empty($_SESSION['csrf']) || !hash_equals((string) $_SESSION['csrf'], $sent)) {
        abort(419, 'The page was open too long or the form was submitted twice. Go back, refresh and try again.');
    }
}

/* ------------------------------------------------------------------ public form tokens
   Stateless, so visitors don't get a session cookie just for reading the site.
   The token proves the form was rendered by us, and not seconds ago by a bot. */
function form_token(): string
{
    $t = (string) time();
    return $t . '.' . substr(hash_hmac('sha256', 'form|' . $t, app_key()), 0, 32);
}

function form_token_problem(string $token): ?string
{
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return 'expired';
    }
    $expected = substr(hash_hmac('sha256', 'form|' . $parts[0], app_key()), 0, 32);
    if (!hash_equals($expected, $parts[1])) {
        return 'expired';
    }
    $age = time() - (int) $parts[0];
    if ($age < 3) {
        return 'too_fast';
    }
    if ($age > 60 * 60 * 24 * 7) {
        return 'expired';
    }
    return null;
}

/* ------------------------------------------------------------------ rate limiting */
/** Records a hit and returns false when the bucket is over its limit. */
function throttle(string $bucket, int $max, int $windowSeconds, bool $record = true): bool
{
    if (!app_installed()) {
        return true;
    }
    $bucket = substr($bucket, 0, 190);
    $since = date('Y-m-d H:i:s', time() - $windowSeconds);
    $count = (int) db()->value('SELECT COUNT(*) FROM throttle WHERE bucket = ? AND created_at >= ?', [$bucket, $since]);
    if ($count >= $max) {
        return false;
    }
    if ($record) {
        db()->insert('throttle', ['bucket' => $bucket, 'created_at' => now()]);
        if (random_int(1, 50) === 1) {
            db()->run('DELETE FROM throttle WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 86400 * 2)]);
        }
    }
    return true;
}

function throttle_clear(string $bucket): void
{
    db()->delete('throttle', 'bucket = ?', [substr($bucket, 0, 190)]);
}

/* ------------------------------------------------------------------ secrets at rest */
function encrypt_secret(string $plain): string
{
    if ($plain === '') {
        return '';
    }
    $key = hash('sha256', 'secret|' . app_key(), true);
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return 'v1:' . base64_encode($iv . $tag . $cipher);
}

function decrypt_secret(string $stored): string
{
    if ($stored === '' || !str_starts_with($stored, 'v1:')) {
        return $stored;
    }
    $raw = base64_decode(substr($stored, 3), true);
    if ($raw === false || strlen($raw) < 29) {
        return '';
    }
    $key = hash('sha256', 'secret|' . app_key(), true);
    $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    return $plain === false ? '' : $plain;
}

/* ------------------------------------------------------------------ headers */
function send_admin_headers(): void
{
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, private');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; frame-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'");
}
