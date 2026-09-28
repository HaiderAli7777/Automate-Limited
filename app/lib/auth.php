<?php
/*
 * Team accounts and access rights.
 *
 * Every person has a role. A role is a preset bundle of permissions; "Custom
 * access" lets an administrator tick exactly what someone can see and do.
 * Administrators always have everything, so nobody can lock the site out.
 */
declare(strict_types=1);

const ROLES = [
    'admin' => 'Administrator',
    'manager' => 'Manager',
    'hr' => 'HR manager',
    'recruiter' => 'Recruiter',
    'sales' => 'Sales',
    'interviewer' => 'Interviewer',
    'viewer' => 'Viewer',
    'custom' => 'Custom access',
];

const ROLE_HELP = [
    'admin' => 'Everything, including team members, access rights and settings.',
    'manager' => 'Runs recruitment and sales, exports and deletes. No team or settings.',
    'hr' => 'Recruitment, employee profiles and payroll. No sales, team or settings.',
    'recruiter' => 'Jobs, candidates, the hiring pipeline and interviews.',
    'sales' => 'Leads, contacts and the sales pipeline.',
    'interviewer' => 'Only the interviews they sit on, the CV, and their own scorecards.',
    'viewer' => 'Can look at recruitment and sales, but can\'t change anything.',
    'custom' => 'Tick exactly what this person can see and do.',
];

/** Permission key => what it allows, grouped for the access screen. */
const PERMISSION_GROUPS = [
    'Recruitment (ATS)' => [
        'ats.view' => 'See jobs, candidates, the hiring pipeline and the ATS dashboard',
        'ats.manage' => 'Post jobs, move candidates, email them, schedule interviews and make offers',
    ],
    'Interviews' => [
        'interviews.own' => 'Sit on interview panels, open the candidate\'s CV and write scorecards',
    ],
    'Sales (CRM)' => [
        'crm.view' => 'See leads, contacts, the sales pipeline and the CRM dashboard',
        'crm.manage' => 'Add and edit leads and contacts, log activity, email contacts and move deals',
        'crm.all' => 'See every lead. When off, they only see leads assigned to them',
    ],
    'People (HR)' => [
        'hr.view' => 'See employee profiles, departments and the HR dashboard',
        'hr.manage' => 'Add and edit employees, convert hires into employees, manage departments',
        'payroll.manage' => 'See salaries, run payroll, edit and confirm payslips',
    ],
    'Data' => [
        'data.export' => 'Export lists to CSV',
        'data.delete' => 'Delete jobs, candidates, leads and contacts',
    ],
    'Administration' => [
        'team.manage' => 'Add team members and set their access',
        'settings.manage' => 'Change settings, pipeline stages and email templates',
        'audit.view' => 'See the activity log of everything done in the team area',
    ],
];

const ROLE_PRESETS = [
    'manager' => ['ats.view', 'ats.manage', 'interviews.own', 'crm.view', 'crm.manage', 'crm.all', 'hr.view', 'data.export', 'data.delete', 'audit.view'],
    'hr' => ['ats.view', 'ats.manage', 'interviews.own', 'hr.view', 'hr.manage', 'payroll.manage', 'data.export'],
    'recruiter' => ['ats.view', 'ats.manage', 'interviews.own', 'data.export'],
    'sales' => ['crm.view', 'crm.manage', 'crm.all', 'data.export'],
    'interviewer' => ['interviews.own'],
    'viewer' => ['ats.view', 'crm.view', 'crm.all', 'interviews.own'],
];

/** A permission that only makes sense with another one switches that one on too. */
const PERMISSION_REQUIRES = ['ats.manage' => 'ats.view', 'crm.manage' => 'crm.view', 'crm.all' => 'crm.view', 'hr.manage' => 'hr.view', 'payroll.manage' => 'hr.view'];

/** Shorter names used around the code base. */
const PERMISSION_ALIASES = ['ats' => 'ats.view', 'crm' => 'crm.view', 'team' => 'team.manage', 'settings' => 'settings.manage'];

function all_permissions(): array
{
    $out = [];
    foreach (PERMISSION_GROUPS as $perms) {
        array_push($out, ...array_keys($perms));
    }
    return $out;
}

function normalize_permissions(array $perms): array
{
    $valid = all_permissions();
    $perms = array_values(array_intersect(array_map('strval', $perms), $valid));
    foreach (PERMISSION_REQUIRES as $p => $needs) {
        if (in_array($p, $perms, true) && !in_array($needs, $perms, true)) {
            $perms[] = $needs;
        }
    }
    // keep the canonical order so stored values compare cleanly
    return array_values(array_intersect($valid, $perms));
}

function user_permissions(?array $user): array
{
    if (!$user) {
        return [];
    }
    if ($user['role'] === 'admin') {
        return all_permissions();
    }
    if (isset(ROLE_PRESETS[$user['role']])) {
        return ROLE_PRESETS[$user['role']];
    }
    $list = json_decode((string) ($user['permissions'] ?? ''), true);
    return normalize_permissions(is_array($list) ? $list : []);
}

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

/**
 * Whether a user holds a permission. "interviews" means "can take part in
 * interviews at all": either on a panel, or seeing all of recruitment.
 */
function user_can(string $perm, ?array $user = null): bool
{
    $user = $user ?? auth_user();
    if (!$user) {
        return false;
    }
    $perms = user_permissions($user);
    if ($perm === 'interviews') {
        return in_array('interviews.own', $perms, true) || in_array('ats.view', $perms, true);
    }
    return in_array(PERMISSION_ALIASES[$perm] ?? $perm, $perms, true);
}

function require_can(string $perm): void
{
    if (!user_can($perm)) {
        abort(403, 'Your access doesn\'t include this. Ask an administrator if you need it.');
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
    return $cache ??= db()->all('SELECT id, name, email, role, permissions FROM users WHERE is_active = 1 ORDER BY name');
}

/** Ids of active people holding a permission. */
function users_with(string $perm): array
{
    $ids = [];
    foreach (active_users() as $u) {
        if (user_can($perm, $u)) {
            $ids[] = (int) $u['id'];
        }
    }
    return $ids;
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

/** Plain-language summary of someone's access, for the team list. */
function access_summary(array $user): array
{
    $level = static function (bool $view, bool $manage): string {
        return $manage ? 'Full' : ($view ? 'View only' : 'None');
    };
    $crm = $level(user_can('crm.view', $user), user_can('crm.manage', $user));
    if ($crm !== 'None') {
        $crm .= user_can('crm.all', $user) ? ', all leads' : ', own leads';
    }
    $extra = [];
    if (user_can('team.manage', $user)) {
        $extra[] = 'Team';
    }
    if (user_can('settings.manage', $user)) {
        $extra[] = 'Settings';
    }
    if (user_can('data.export', $user)) {
        $extra[] = 'Export';
    }
    if (user_can('data.delete', $user)) {
        $extra[] = 'Delete';
    }
    if (user_can('audit.view', $user)) {
        $extra[] = 'Activity log';
    }
    return [
        'Recruitment' => $level(user_can('ats.view', $user), user_can('ats.manage', $user)),
        'Interviews' => user_can('interviews', $user) ? 'Yes' : 'No',
        'Sales' => $crm,
        'People' => user_can('payroll.manage', $user) ? 'Full, with payroll' : $level(user_can('hr.view', $user), user_can('hr.manage', $user)),
        'Also' => $extra ? implode(', ', $extra) : '',
    ];
}

/**
 * SQL condition limiting leads to the current user's own when they can't see
 * every lead. The id is an integer from the session, so it is safe inline.
 */
function crm_scope(string $alias = ''): string
{
    if (user_can('crm.all')) {
        return '';
    }
    return ' AND ' . ($alias !== '' ? $alias . '.' : '') . 'owner_id = ' . (int) auth_id();
}

/** Contacts visible to someone who only sees their own leads. */
function contact_scope(string $alias = 'c'): string
{
    if (user_can('crm.all')) {
        return '';
    }
    return ' AND EXISTS (SELECT 1 FROM leads sl WHERE sl.contact_id = ' . $alias . '.id AND sl.owner_id = ' . (int) auth_id() . ')';
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
