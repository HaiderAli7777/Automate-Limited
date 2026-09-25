<?php
/*
 * Local development only. Mirrors the .htaccess rewrites for PHP's built-in server:
 *   php -S localhost:8080 -t . tools/dev-router.php
 * Hostinger never uses this file (and /tools is denied by .htaccess).
 */
$root = dirname(__DIR__);
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$run = static function (string $script) use ($root): bool {
    $_SERVER['SCRIPT_NAME'] = '/' . $script;
    $_SERVER['SCRIPT_FILENAME'] = $root . '/' . $script;
    chdir(dirname($root . '/' . $script));
    require $root . '/' . $script;
    return true;
};

if (preg_match('#^/(app|tools)(/|$)#', $path) || preg_match('#/\.(?!well-known/)#', $path) || preg_match('#\.(sql|md|log|lock|dist|sh)$#', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
if ($path === '/index.html') {
    header('Location: /', true, 301);
    return true;
}
if ($path === '/sitemap.xml') {
    return $run('sitemap.php');
}
if (preg_match('#^/careers/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['job'] = $m[1];
    return $run('careers/index.php');
}
if (preg_match('#^/admin(/.*)?$#', $path) && !is_file($root . $path)) {
    return $run('admin/index.php');
}
if (preg_match('#^/(login|team)/?$#', $path)) {
    header('Location: /admin/login', true, 302);
    return true;
}
if ($path !== '/' && is_file($root . $path)) {
    if (str_ends_with($path, '.php')) {
        return $run(ltrim($path, '/'));
    }
    return false;
}
$dir = rtrim($path, '/');
if (is_file($root . $dir . '/index.php')) {
    if ($path !== '/' && !str_ends_with($path, '/')) {
        header('Location: ' . $path . '/', true, 301);
        return true;
    }
    return $run(ltrim($dir . '/index.php', '/'));
}
http_response_code(404);
return $run('404.php');
