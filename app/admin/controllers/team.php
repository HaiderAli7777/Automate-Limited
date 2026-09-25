<?php
/* Team accounts: invite people, set roles, switch accounts off. Admin only. */
declare(strict_types=1);

function team_index(): void
{
    $users = db()->all('SELECT * FROM users ORDER BY is_active DESC, name');
    admin_view('team/index', ['title' => 'Team', 'nav' => 'team', 'users' => $users]);
}

function team_form(int $id = 0): void
{
    $user = $id ? db()->one('SELECT * FROM users WHERE id = ?', [$id]) : null;
    if ($id && !$user) {
        abort(404);
    }
    admin_view('team/form', ['title' => $user ? $user['name'] : 'Add a teammate', 'nav' => 'team', 'user' => $user]);
}

function team_save(int $id = 0): void
{
    $user = $id ? db()->one('SELECT * FROM users WHERE id = ?', [$id]) : null;
    if ($id && !$user) {
        abort(404);
    }
    $name = input('name');
    $email = strtolower(input('email'));
    $role = input('role');
    $active = $id ? input('is_active') === '1' : true;
    $password = (string) ($_POST['password'] ?? '');
    $invite = input('send_invite') === '1';
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Enter their name.';
    }
    if (!valid_email($email)) {
        $errors['email'] = 'Enter a valid email.';
    } elseif (db()->value('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id])) {
        $errors['email'] = 'Someone on the team already uses this email.';
    }
    if (!isset(ROLES[$role])) {
        $errors['role'] = 'Choose a role.';
    }
    if ($password !== '' && ($p = password_problem($password))) {
        $errors['password'] = $p;
    }
    if (!$id && $password === '' && !$invite) {
        $errors['password'] = 'Set a password, or tick the box to email them a link to set their own.';
    }
    // never leave the site without an active administrator
    if ($user && $user['role'] === 'admin' && ($role !== 'admin' || !$active)) {
        $admins = (int) db()->value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1 AND id <> ?", [$id]);
        if ($admins === 0) {
            $errors['role'] = 'This is the only active administrator. Make someone else an administrator first.';
        }
    }
    if ($errors) {
        remember_input($errors);
        redirect($id ? admin_url('team/' . $id) : admin_url('team/new'));
    }

    $data = [
        'name' => mb_substr($name, 0, 120),
        'email' => $email,
        'role' => $role,
        'title' => nullable(input('title')),
        'phone' => nullable(input('phone')),
        'is_active' => $active ? 1 : 0,
        'updated_at' => now(),
    ];
    if ($password !== '') {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    if ($id) {
        db()->update('users', $data, 'id = ?', [$id]);
    } else {
        $data['password_hash'] ??= password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
        $data['created_at'] = now();
        $id = db()->insert('users', $data);
    }

    if ($invite) {
        $token = bin2hex(random_bytes(32));
        db()->insert('password_resets', ['user_id' => $id, 'token_hash' => hash('sha256', $token), 'expires_at' => date('Y-m-d H:i:s', time() + 72 * 3600), 'created_at' => now()]);
        $link = site_origin() . admin_url('reset') . '?token=' . $token;
        $me = auth_user();
        $ok = Mailer::send($email, 'You\'re invited to the ' . company_name() . ' team area', "Hi " . explode(' ', $name)[0] . ",\n\n"
            . $me['name'] . " has added you to the " . company_name() . " team area as " . role_label($role) . ".\n\n"
            . "Set your password here (the link works for 3 days):\n{$link}\n\nAfter that, sign in at " . site_origin() . admin_url('login'), ['to_name' => $name]);
        flash($ok ? 'success' : 'error', $ok ? 'Saved, and an invitation is on its way to ' . $email . '.' : 'Saved, but the invitation email failed: ' . Mailer::$lastError . ' Check Settings, Email.');
    } else {
        flash('success', 'Saved.');
    }
    redirect(admin_url('team'));
}
