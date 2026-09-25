<?php
/* Sign in, sign out, password reset and the signed-in user's own account. */
declare(strict_types=1);

function auth_login_page(): void
{
    if (auth_user()) {
        redirect(admin_url());
    }
    $email = old('email');
    $next = input('next');
    ob_start(); ?>
    <h1>Team sign-in</h1>
    <p class="muted">Recruitment, CRM and the pipeline behind the website.</p>
    <form method="post" action="<?= e(admin_url('login')) ?>" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>" autofocus></div>
      <div class="field"><label for="password">Password</label><input class="input" id="password" name="password" type="password" autocomplete="current-password" required></div>
      <button class="btn btn--primary btn--block" type="submit">Sign in</button>
      <p class="small muted" style="text-align:center"><a class="link" href="<?= e(admin_url('forgot')) ?>">Forgot your password?</a></p>
    </form>
    <?php
    render('admin/auth-layout', ['title' => 'Sign in', 'content' => ob_get_clean()]);
}

function auth_login_submit(): void
{
    $email = strtolower(input('email'));
    $password = (string) ($_POST['password'] ?? '');
    $ipBucket = 'login-ip|' . client_ip();
    $userBucket = 'login|' . client_ip() . '|' . $email;

    if (!throttle($ipBucket, 30, 900, false) || !throttle($userBucket, 6, 900, false)) {
        remember_input();
        flash('error', 'Too many attempts. Wait 15 minutes and try again, or reset your password.');
        redirect(admin_url('login'));
    }
    $user = $email !== '' ? db()->one('SELECT * FROM users WHERE email = ?', [$email]) : null;
    if (!$user || !password_verify($password, (string) $user['password_hash']) || !(int) $user['is_active']) {
        // record failures only, so a successful sign-in never locks anyone out
        throttle($ipBucket, 30, 900);
        throttle($userBucket, 6, 900);
        remember_input();
        flash('error', $user && !(int) $user['is_active'] ? 'This account is switched off. Ask an administrator.' : 'That email and password don\'t match.');
        redirect(admin_url('login'));
    }
    if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
        db()->update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [(int) $user['id']]);
        $user = db()->one('SELECT * FROM users WHERE id = ?', [(int) $user['id']]);
    }
    throttle_clear($userBucket);
    auth_login($user);
    $next = input('next');
    // only same-site paths inside the team area
    if ($next !== '' && str_starts_with($next, admin_url()) && !str_contains($next, '//')) {
        redirect($next);
    }
    redirect(admin_url());
}

function auth_logout_submit(): void
{
    auth_logout();
    session_boot();
    flash('success', 'You\'re signed out.');
    redirect(admin_url('login'));
}

function auth_forgot_page(): void
{
    ob_start(); ?>
    <h1>Reset your password</h1>
    <p class="muted">Enter the email you sign in with. If it belongs to an account, we'll send a reset link that works for one hour.</p>
    <form method="post" action="<?= e(admin_url('forgot')) ?>" class="stack">
      <?= csrf_field() ?>
      <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" autocomplete="username" required autofocus></div>
      <button class="btn btn--primary btn--block" type="submit">Send reset link</button>
      <p class="small muted" style="text-align:center"><a class="link" href="<?= e(admin_url('login')) ?>">Back to sign-in</a></p>
    </form>
    <?php
    render('admin/auth-layout', ['title' => 'Reset password', 'content' => ob_get_clean()]);
}

function auth_forgot_submit(): void
{
    $email = strtolower(input('email'));
    if (throttle('forgot|' . client_ip(), 5, 3600)) {
        $user = valid_email($email) ? db()->one('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]) : null;
        if ($user) {
            $token = bin2hex(random_bytes(32));
            db()->insert('password_resets', [
                'user_id' => (int) $user['id'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => now(),
            ]);
            $link = site_origin() . admin_url('reset') . '?token=' . $token;
            Mailer::send($user['email'], 'Reset your password', "Hi " . explode(' ', (string) $user['name'])[0] . ",\n\nSomeone asked to reset the password for your " . company_name() . " team account. If it was you, open this link within the next hour:\n\n{$link}\n\nIf it wasn't you, ignore this email. Your password stays the same.", ['to_name' => $user['name']]);
        }
    }
    flash('success', 'If that email belongs to an account, a reset link is on its way.');
    redirect(admin_url('login'));
}

function reset_row(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    return db()->one('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ?', [hash('sha256', $token), now()]);
}

function auth_reset_page(): void
{
    $token = input('token');
    if (!reset_row($token)) {
        flash('error', 'That reset link has expired or was already used. Request a new one.');
        redirect(admin_url('forgot'));
    }
    ob_start(); ?>
    <h1>Choose a new password</h1>
    <p class="muted">At least 10 characters. A short sentence is easier to remember than symbols.</p>
    <form method="post" action="<?= e(admin_url('reset')) ?>" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="field"><label for="password">New password</label><input class="input" id="password" name="password" type="password" autocomplete="new-password" required minlength="10" autofocus></div>
      <div class="field"><label for="password2">Repeat it</label><input class="input" id="password2" name="password2" type="password" autocomplete="new-password" required minlength="10"></div>
      <button class="btn btn--primary btn--block" type="submit">Save password</button>
    </form>
    <?php
    render('admin/auth-layout', ['title' => 'New password', 'content' => ob_get_clean()]);
}

function auth_reset_submit(): void
{
    $token = input('token');
    $row = reset_row($token);
    if (!$row) {
        flash('error', 'That reset link has expired or was already used. Request a new one.');
        redirect(admin_url('forgot'));
    }
    $pw = (string) ($_POST['password'] ?? '');
    $problem = password_problem($pw) ?? ($pw !== (string) ($_POST['password2'] ?? '') ? 'The two passwords don\'t match.' : null);
    if ($problem) {
        flash('error', $problem);
        redirect(admin_url('reset') . '?token=' . $token);
    }
    db()->update('users', ['password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'updated_at' => now()], 'id = ?', [(int) $row['user_id']]);
    db()->update('password_resets', ['used_at' => now()], 'user_id = ? AND used_at IS NULL', [(int) $row['user_id']]);
    flash('success', 'Password saved. Sign in with your new password.');
    redirect(admin_url('login'));
}

function account_page(): void
{
    admin_view('account', ['title' => 'My account', 'nav' => 'account', 'user' => auth_user()]);
}

function account_save(): void
{
    $me = auth_user();
    $name = input('name');
    $email = strtolower(input('email'));
    $errors = [];
    if ($name === '') {
        $errors['name'] = 'Enter your name.';
    }
    if (!valid_email($email)) {
        $errors['email'] = 'Enter a valid email.';
    } elseif (db()->value('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, (int) $me['id']])) {
        $errors['email'] = 'Another account already uses this email.';
    }
    $newPw = (string) ($_POST['new_password'] ?? '');
    if ($newPw !== '') {
        if (!password_verify((string) ($_POST['current_password'] ?? ''), (string) $me['password_hash'])) {
            $errors['current_password'] = 'Your current password isn\'t right.';
        } elseif ($p = password_problem($newPw)) {
            $errors['new_password'] = $p;
        }
    }
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('account'));
    }
    $data = ['name' => mb_substr($name, 0, 120), 'email' => $email, 'title' => nullable(input('title')), 'phone' => nullable(input('phone')), 'updated_at' => now()];
    if ($newPw !== '') {
        $data['password_hash'] = password_hash($newPw, PASSWORD_DEFAULT);
    }
    db()->update('users', $data, 'id = ?', [(int) $me['id']]);
    if ($newPw !== '') {
        // keep this session signed in with the new password version
        $_SESSION['pwv'] = password_version(db()->one('SELECT * FROM users WHERE id = ?', [(int) $me['id']]));
    }
    flash('success', 'Your account is saved.');
    redirect(admin_url('account'));
}
