<?php
/*
 * One-time installer. Open https://yourdomain/install/ after the first deploy.
 * It creates the database tables, the first administrator and the private
 * config file, then locks itself: once a config file exists it only shows
 * "already installed". To reinstall, delete the config file by hand.
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/schema.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

$privateDir = dirname(APP_ROOT) . '/automate-private';
$fallbackDir = APP_DIR . '/storage';

function private_dir_usable(string $dir): bool
{
    if (@is_dir($dir)) {
        return @is_writable($dir);
    }
    $parent = dirname($dir);
    return @is_dir($parent) && @is_writable($parent);
}

$usePrivate = private_dir_usable($privateDir);
$storageDir = $usePrivate ? $privateDir : $fallbackDir;

$checks = [
    ['PHP 8.1 or newer (you have ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '8.1.0', '>=')],
    ['PDO MySQL extension', extension_loaded('pdo_mysql')],
    ['OpenSSL extension', extension_loaded('openssl')],
    ['Multibyte string extension', extension_loaded('mbstring')],
    ['Writable private folder for config and uploads', private_dir_usable($storageDir)],
];
$ready = !in_array(false, array_column($checks, 1), true);

$errors = [];
$done = false;
$v = [
    'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
    'site_url' => site_origin() . base_path(), 'company_name' => 'Automate Limited',
    'admin_name' => '', 'admin_email' => '', 'notify_email' => 'info@automateltd.com',
];

if (!App::$installed && is_post() && $ready) {
    foreach ($v as $k => $_) {
        $v[$k] = $k === 'db_pass' ? (string) ($_POST[$k] ?? '') : input($k);
    }
    $password = (string) ($_POST['admin_password'] ?? '');
    $password2 = (string) ($_POST['admin_password2'] ?? '');

    $localHosts = ['localhost', '127.0.0.1', '::1'];
    $allowRemote = @is_file(APP_DIR . '/storage/allow-remote-db');
    if ($v['db_name'] === '' || $v['db_user'] === '') {
        $errors['db'] = 'Enter the database name and user from hPanel, Databases, MySQL Databases.';
    } elseif (!in_array(strtolower($v['db_host']), $localHosts, true) && !$allowRemote) {
        $errors['db'] = 'For safety the installer only connects to a database on this server (localhost). See the README if your host needs a different address.';
    }
    if (!filter_var($v['site_url'], FILTER_VALIDATE_URL)) {
        $errors['site_url'] = 'Enter the full address, for example https://automateltd.com';
    }
    if ($v['admin_name'] === '') {
        $errors['admin_name'] = 'Enter your name.';
    }
    if (!valid_email($v['admin_email'])) {
        $errors['admin_email'] = 'Enter a valid email address. You will sign in with it.';
    }
    if ($problem = password_problem($password)) {
        $errors['admin_password'] = $problem;
    } elseif ($password !== $password2) {
        $errors['admin_password'] = 'The two passwords don\'t match.';
    }
    if ($v['notify_email'] !== '' && !valid_email($v['notify_email'])) {
        $errors['notify_email'] = 'Enter a valid email address, or leave it blank.';
    }

    if (!$errors) {
        try {
            $dbConfig = ['host' => $v['db_host'], 'port' => (int) ($v['db_port'] ?: 3306), 'name' => $v['db_name'], 'user' => $v['db_user'], 'pass' => $v['db_pass']];
            $db = new Db($dbConfig);
            migrate($db);
            seed_defaults($db, ['company_name' => $v['company_name'] ?: 'Automate Limited', 'notify_email' => $v['notify_email']]);

            $now = date('Y-m-d H:i:s');
            $existing = $db->value('SELECT id FROM users WHERE email = ?', [$v['admin_email']]);
            if ($existing) {
                $db->update('users', ['name' => $v['admin_name'], 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'admin', 'is_active' => 1, 'updated_at' => $now], 'id = ?', [$existing]);
            } else {
                $db->insert('users', ['name' => $v['admin_name'], 'email' => $v['admin_email'], 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'admin', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }

            foreach ([$storageDir, $storageDir . '/uploads', $storageDir . '/logs'] as $dir) {
                if (!@is_dir($dir) && !@mkdir($dir, 0750, true)) {
                    throw new RuntimeException('Could not create ' . $dir);
                }
            }
            foreach ([$storageDir, $storageDir . '/uploads', $storageDir . '/logs'] as $dir) {
                @file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
            }

            $config = [
                'db' => $dbConfig,
                'app_key' => bin2hex(random_bytes(32)),
                'base_url' => rtrim($v['site_url'], '/'),
                'storage_path' => $storageDir,
                'debug' => false,
                'installed_at' => $now,
            ];
            $php = "<?php\n// Written by the installer on {$now}. Keep this file private and out of git.\n"
                . "// To reinstall, delete this file and open /install/ again.\nreturn " . var_export($config, true) . ";\n";
            if (@file_put_contents($storageDir . '/config.php', $php, LOCK_EX) === false) {
                throw new RuntimeException('Could not write ' . $storageDir . '/config.php');
            }
            @chmod($storageDir . '/config.php', 0640);
            $done = true;
        } catch (PDOException $e) {
            $errors['db'] = 'Could not connect to the database: ' . $e->getMessage();
        } catch (Throwable $e) {
            $errors['general'] = $e->getMessage();
        }
    }
}

$field = static function (string $name, string $label, string $type = 'text', string $help = '', array $attrs = []) use (&$v, &$errors): string {
    $val = $type === 'password' ? '' : ($v[$name] ?? '');
    $err = $errors[$name] ?? '';
    $extra = '';
    foreach ($attrs as $ak => $av) {
        $extra .= ' ' . $ak . '="' . e($av) . '"';
    }
    return '<div class="field' . ($err ? ' has-error' : '') . '"><label for="f-' . e($name) . '">' . e($label) . '</label>'
        . '<input class="input" id="f-' . e($name) . '" name="' . e($name) . '" type="' . e($type) . '" value="' . e($val) . '"' . $extra . '>'
        . ($help ? '<p class="help">' . e($help) . '</p>' : '')
        . ($err ? '<p class="error">' . e($err) . '</p>' : '') . '</div>';
};
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Install | Automate Limited</title>
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<script src="<?= e(asset('assets/js/theme-init.js')) ?>"></script>
</head>
<body class="auth">
<main class="auth__wrap auth__wrap--wide">
  <div class="auth__logo"><img src="<?= e(url('logo-automate.svg')) ?>" alt="Automate Limited" width="170" height="39"></div>

<?php if (App::$installed && !$done): ?>
  <div class="panel auth__card">
    <h1>Already installed.</h1>
    <p class="muted">The site is set up. Sign in to the team area to continue. To reinstall from scratch, delete <code>automate-private/config.php</code> (one folder above the website) in the File Manager first.</p>
    <p style="margin-top:20px"><a class="btn btn--primary" href="<?= e(admin_url('login')) ?>">Go to team sign-in</a></p>
  </div>
<?php elseif ($done): ?>
  <div class="panel auth__card">
    <h1>You're set up.</h1>
    <p class="muted">The database is ready, your administrator account exists, and the private config was saved to <code><?= e($storageDir) ?></code>.</p>
    <ol class="steps-list">
      <li>Sign in with <strong><?= e($v['admin_email']) ?></strong>.</li>
      <li>Open <strong>Settings</strong> and add the SMTP password for your mailbox so notifications send reliably.</li>
      <li>Invite your team from <strong>Team</strong>, then post your first job.</li>
    </ol>
    <p style="margin-top:22px"><a class="btn btn--primary" href="<?= e(admin_url('login')) ?>">Sign in</a></p>
  </div>
<?php else: ?>
  <div class="panel auth__card">
    <h1>Set up the team area.</h1>
    <p class="muted">This connects the website to its database and creates the first administrator. It takes about a minute and runs once.</p>

    <ul class="checks">
      <?php foreach ($checks as [$label, $ok]): ?>
        <li class="<?= $ok ? 'is-ok' : 'is-bad' ?>"><?= icon($ok ? 'check-circle' : 'x-circle') ?><span><?= e($label) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <?php if (!$usePrivate): ?>
      <p class="notice">Your config and uploaded CVs will be stored in <code>app/storage</code>, which the web server is set to refuse. Storing them one folder above the website is safer; allow PHP to write there if your host permits it.</p>
    <?php endif; ?>

    <?php if (!$ready): ?>
      <p class="notice notice--bad">Fix the items marked above, then reload this page. On Hostinger, PHP version and extensions are under Advanced, PHP Configuration.</p>
    <?php else: ?>
      <?php if (!empty($errors['general'])): ?><p class="notice notice--bad"><?= e($errors['general']) ?></p><?php endif; ?>
      <form method="post" class="stack" autocomplete="off" novalidate>
        <fieldset class="fieldset">
          <legend>Database</legend>
          <p class="help">Create one in hPanel under Databases, MySQL Databases, then copy the details here.</p>
          <?php if (!empty($errors['db'])): ?><p class="notice notice--bad"><?= e($errors['db']) ?></p><?php endif; ?>
          <div class="grid-2">
            <?= $field('db_name', 'Database name', 'text', '', ['required' => 'required', 'placeholder' => 'u123456789_automate']) ?>
            <?= $field('db_user', 'Database user', 'text', '', ['required' => 'required', 'placeholder' => 'u123456789_admin']) ?>
            <?= $field('db_pass', 'Database password', 'password') ?>
            <div class="grid-2 grid-tight">
              <?= $field('db_host', 'Host') ?>
              <?= $field('db_port', 'Port', 'text', '', ['inputmode' => 'numeric']) ?>
            </div>
          </div>
        </fieldset>
        <fieldset class="fieldset">
          <legend>Site</legend>
          <div class="grid-2">
            <?= $field('site_url', 'Website address', 'url') ?>
            <?= $field('company_name', 'Company name') ?>
          </div>
          <?= $field('notify_email', 'Send new applications and enquiries to', 'email', 'You can set separate addresses for recruitment and sales later.') ?>
        </fieldset>
        <fieldset class="fieldset">
          <legend>Your administrator account</legend>
          <div class="grid-2">
            <?= $field('admin_name', 'Your name', 'text', '', ['autocomplete' => 'name']) ?>
            <?= $field('admin_email', 'Email', 'email', '', ['autocomplete' => 'email']) ?>
            <?= $field('admin_password', 'Password', 'password', 'At least 10 characters.', ['autocomplete' => 'new-password']) ?>
            <?= $field('admin_password2', 'Repeat password', 'password', '', ['autocomplete' => 'new-password']) ?>
          </div>
        </fieldset>
        <button class="btn btn--primary btn--block" type="submit">Install</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>
</main>
</body>
</html>
