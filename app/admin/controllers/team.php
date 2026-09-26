<?php
/*
 * Team members and their access rights.
 * Administrators can do anything here. Someone else with "team.manage" can add
 * and edit people, but only grant access they hold themselves, and can't touch
 * administrators.
 */
declare(strict_types=1);

function team_index(): void
{
    $users = db()->all('SELECT * FROM users ORDER BY is_active DESC, name');
    $status = input('status');
    if ($status === 'active') {
        $users = array_values(array_filter($users, static fn ($u) => (int) $u['is_active'] === 1));
    } elseif ($status === 'off') {
        $users = array_values(array_filter($users, static fn ($u) => (int) $u['is_active'] === 0));
    }
    admin_view('team/index', ['title' => 'Team and access', 'nav' => 'team', 'users' => $users]);
}

function team_editable(?array $target): bool
{
    if (is_role('admin')) {
        return true;
    }
    return !$target || $target['role'] !== 'admin';
}

/** Roles the signed-in person may hand out. */
function grantable_roles(): array
{
    if (is_role('admin')) {
        return ROLES;
    }
    $mine = user_permissions(auth_user());
    $out = [];
    foreach (ROLES as $key => $label) {
        if ($key === 'admin') {
            continue;
        }
        if ($key === 'custom' || !array_diff(ROLE_PRESETS[$key] ?? [], $mine)) {
            $out[$key] = $label;
        }
    }
    return $out;
}

function team_form(int $id = 0): void
{
    $user = $id ? (db()->one('SELECT * FROM users WHERE id = ?', [$id]) ?? abort(404)) : null;
    if (!team_editable($user)) {
        abort(403, 'Only an administrator can change another administrator.');
    }
    $history = $user ? activities_for([['user', [$id]]], 20) : [];
    admin_view('team/form', ['title' => $user ? $user['name'] : 'Add a team member', 'nav' => 'team', 'user' => $user, 'history' => $history]);
}

function team_save(int $id = 0): void
{
    $user = $id ? (db()->one('SELECT * FROM users WHERE id = ?', [$id]) ?? abort(404)) : null;
    if (!team_editable($user)) {
        abort(403, 'Only an administrator can change another administrator.');
    }
    $back = $id ? admin_url('team/' . $id) : admin_url('team/new');
    $name = input('name');
    $email = strtolower(input('email'));
    $role = input('role');
    $selfLocked = $user && $id === auth_id() && !is_role('admin');
    if ($selfLocked) {
        // only an administrator can change their own access
        $role = (string) $user['role'];
        $_POST['perms'] = user_permissions($user);
    }
    $active = $id ? input('is_active', '1') === '1' : true;
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
    if (!$selfLocked && !isset(grantable_roles()[$role])) {
        $errors['role'] = 'Choose one of the access levels.';
    }
    $perms = $role === 'custom' ? normalize_permissions(input_array('perms')) : [];
    if ($role === 'custom' && !$perms) {
        $errors['role'] = 'Tick at least one thing this person can do, or pick a preset.';
    }
    $resulting = $role === 'custom' ? $perms : ($role === 'admin' ? all_permissions() : (ROLE_PRESETS[$role] ?? []));
    if (!$selfLocked && !is_role('admin') && array_diff($resulting, user_permissions(auth_user()))) {
        $errors['role'] = 'You can only give access you have yourself.';
    }
    if ($password !== '' && ($p = password_problem($password))) {
        $errors['password'] = $p;
    }
    if (!$id && $password === '' && !$invite) {
        $errors['password'] = 'Set a password, or tick the box to email them a link to set their own.';
    }
    if ($id && $id === auth_id() && !$active) {
        $errors['role'] = 'You can\'t switch off your own account.';
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
        redirect($back);
    }

    $data = [
        'name' => mb_substr($name, 0, 120),
        'email' => $email,
        'role' => $role,
        'permissions' => $role === 'custom' ? json_encode($perms) : null,
        'title' => nullable(input('title')),
        'phone' => nullable(input('phone')),
        'is_active' => $active ? 1 : 0,
        'updated_at' => now(),
    ];
    if ($password !== '') {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    if ($user) {
        db()->update('users', $data, 'id = ?', [$id]);
        $changes = [];
        if ($user['role'] !== $role || (string) $user['permissions'] !== (string) $data['permissions']) {
            $changes[] = 'access set to ' . role_label($role);
        }
        if ((int) $user['is_active'] !== $data['is_active']) {
            $changes[] = $active ? 'switched on' : 'switched off';
        }
        if ($password !== '') {
            $changes[] = 'password changed';
        }
        log_activity('user', $id, 'updated', $changes ? ucfirst(implode(', ', $changes)) : 'Details updated');
    } else {
        $data['password_hash'] ??= password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
        $data['created_at'] = now();
        $id = db()->insert('users', $data);
        log_activity('user', $id, 'created', 'Added to the team as ' . role_label($role));
    }

    if ($invite) {
        [$ok, $err] = send_team_invite($id);
        flash($ok ? 'success' : 'error', $ok ? 'Saved, and an invitation is on its way to ' . $email . '.' : 'Saved, but the invitation email failed: ' . $err . ' Check Settings, Email.');
    } else {
        flash('success', 'Saved.');
    }
    redirect(admin_url('team'));
}

/** Email someone a link to set their password. Returns [ok, error]. */
function send_team_invite(int $id): array
{
    $u = db()->one('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$u) {
        return [false, 'No such user.'];
    }
    $token = bin2hex(random_bytes(32));
    db()->insert('password_resets', ['user_id' => $id, 'token_hash' => hash('sha256', $token), 'expires_at' => date('Y-m-d H:i:s', time() + 72 * 3600), 'created_at' => now()]);
    $link = site_origin() . admin_url('reset') . '?token=' . $token;
    $ok = Mailer::send((string) $u['email'], 'You\'re invited to the ' . company_name() . ' team area', 'Hi ' . explode(' ', (string) $u['name'])[0] . ",\n\n"
        . auth_user()['name'] . ' has added you to the ' . company_name() . ' team area with ' . role_label((string) $u['role']) . " access.\n\n"
        . "Set your password here (the link works for 3 days):\n{$link}\n\nAfter that, sign in at " . site_origin() . admin_url('login'), ['to_name' => $u['name']]);
    if ($ok) {
        log_activity('user', $id, 'email', 'Invitation sent');
    }
    return [$ok, Mailer::$lastError];
}

function team_invite(int $id): void
{
    $u = db()->one('SELECT * FROM users WHERE id = ?', [$id]) ?? abort(404);
    if (!team_editable($u)) {
        abort(403);
    }
    [$ok, $err] = send_team_invite($id);
    flash($ok ? 'success' : 'error', $ok ? 'A new sign-in link is on its way to ' . $u['email'] . '.' : 'The email failed: ' . $err);
    back(admin_url('team'));
}

function team_toggle(int $id): void
{
    $u = db()->one('SELECT * FROM users WHERE id = ?', [$id]) ?? abort(404);
    if (!team_editable($u)) {
        abort(403);
    }
    $on = !(int) $u['is_active'];
    if (!$on && $id === auth_id()) {
        flash('error', 'You can\'t switch off your own account.');
        back(admin_url('team'));
    }
    if (!$on && $u['role'] === 'admin' && !(int) db()->value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1 AND id <> ?", [$id])) {
        flash('error', 'This is the only active administrator.');
        back(admin_url('team'));
    }
    db()->update('users', ['is_active' => $on ? 1 : 0, 'updated_at' => now()], 'id = ?', [$id]);
    log_activity('user', $id, 'updated', $on ? 'Switched on' : 'Switched off');
    flash('success', $u['name'] . ($on ? ' can sign in again.' : ' can no longer sign in. Their history stays.'));
    back(admin_url('team'));
}
