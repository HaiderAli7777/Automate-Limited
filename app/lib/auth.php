<?php
/* Team accounts, roles and permissions. */
declare(strict_types=1);

const ROLES = [
    'admin' => 'Administrator',
    'manager' => 'Manager',
    'recruiter' => 'Recruiter',
    'sales' => 'Sales',
    'interviewer' => 'Interviewer',
];

const ROLE_HELP = [
    'admin' => 'Everything, including team accounts and settings.',
    'manager' => 'Recruitment and CRM, without team accounts or settings.',
    'recruiter' => 'Jobs, candidates, pipeline and interviews.',
    'sales' => 'Leads, contacts and the sales pipeline.',
    'interviewer' => 'Only the interviews they sit on, and their scorecards.',
];

/** Which roles hold each permission. */
const PERMISSIONS = [
    'ats' => ['admin', 'manager', 'recruiter'],
    'crm' => ['admin', 'manager', 'sales'],
    'interviews' => ['admin', 'manager', 'recruiter', 'interviewer'],
    'team' => ['admin'],
    'settings' => ['admin'],
];

function auth_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (!app_installed() || session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['uid'])) {
        return null;
    }
    // idle timeout: 8 hours without a request signs you out
    if (!empty($_SESSION['seen']) && time() - (int) $_SESSION['seen'] > 28800) {
        $_SESSION = [];
        return null;
    }
    $_SESSION['seen'] = time();
    $row = db()->one('SELECT * FROM users WHERE id = ? AND is_active = 1', [(int) $_SESSION['uid']]);
    if (!$row || !hash_equals((string) ($_SESSION['pwv'] ?? ''), password_version($row))) {
        $_SESSION = [];
        return null;
    }
    $user = $row;
    return $user;
}

/** Changes whenever the password changes, which signs out other sessions. */
function password_version(array $user): string
{
    return substr(hash('sha256', (string) $user['password_hash']), 0, 16);
}

function auth_id(): ?int
{
    $u = auth_user();
    return $u ? (int) $u['id'] : null;
}

function auth_login(array $user): void
{
    session_boot();
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $user['id'];
    $_SESSION['pwv'] = password_version($user);
    $_SESSION['seen'] = time();
    unset($_SESSION['csrf']);
    db()->update('users', ['last_login_at' => now()], 'id = ?', [(int) $user['id']]);
}

function auth_logout(): void
{
    session_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

function user_can(string $perm, ?array $user = null): bool
{
    $user = $user ?? auth_user();
    if (!$user) {
        return false;
    }
    return in_array($user['role'], PERMISSIONS[$perm] ?? [], true);
}

function require_can(string $perm): void
{
    if (!user_can($perm)) {
        abort(403, 'Your role doesn\'t include this area. Ask an administrator if you need it.');
    }
}

function is_role(string ...$roles): bool
{
    $u = auth_user();
    return $u !== null && in_array($u['role'], $roles, true);
}

function role_label(string $role): string
{
    return ROLES[$role] ?? ucfirst($role);
}

function active_users(): array
{
    static $cache = null;
    return $cache ??= db()->all('SELECT id, name, email, role FROM users WHERE is_active = 1 ORDER BY name');
}

function user_name(?int $id): string
{
    if (!$id) {
        return '';
    }
    static $names = null;
    $names ??= db()->pairs('SELECT id, name FROM users');
    return (string) ($names[$id] ?? 'Former user');
}

function password_problem(string $pw): ?string
{
    if (mb_strlen($pw) < 10) {
        return 'Use at least 10 characters.';
    }
    if (mb_strlen($pw) > 200) {
        return 'That password is too long.';
    }
    return null;
}
