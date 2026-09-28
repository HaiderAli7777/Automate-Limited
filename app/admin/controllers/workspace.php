<?php
/* Notifications, the activity log and the first-run checklist. */
declare(strict_types=1);

function recent_notifications(int $limit = 8): array
{
    return db()->all('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit, [auth_id()]);
}

function notifications_page(): void
{
    $p = paginate((int) db()->value('SELECT COUNT(*) FROM notifications WHERE user_id = ?', [auth_id()]), 40);
    $rows = db()->all('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'], [auth_id()]);
    admin_view('notifications', ['title' => 'Notifications', 'nav' => 'notifications', 'rows' => $rows, 'p' => $p]);
}

function notifications_open(int $id): void
{
    $n = db()->one('SELECT * FROM notifications WHERE id = ? AND user_id = ?', [$id, auth_id()]) ?? abort(404);
    if (!$n['read_at']) {
        db()->update('notifications', ['read_at' => now()], 'id = ?', [$id]);
    }
    $link = (string) $n['link'];
    redirect($link !== '' && str_starts_with($link, admin_url()) ? $link : admin_url('notifications'));
}

function notifications_read_all(): void
{
    db()->run('UPDATE notifications SET read_at = ? WHERE user_id = ? AND read_at IS NULL', [now(), auth_id()]);
    if (is_ajax()) {
        json_out(['ok' => true]);
    }
    back(admin_url('notifications'));
}

/* ------------------------------------------------------------------ activity log */
const ACTIVITY_ENTITIES = ['application' => 'Applications', 'candidate' => 'Candidates', 'lead' => 'Leads', 'contact' => 'Contacts', 'employee' => 'Employees', 'payroll' => 'Payroll', 'user' => 'Team'];

function activity_link(array $a): ?string
{
    $id = (int) $a['entity_id'];
    return match ($a['entity_type']) {
        'application' => admin_url('applications/' . $id),
        'candidate' => admin_url('candidates/' . $id),
        'lead' => admin_url('leads/' . $id),
        'contact' => admin_url('contacts/' . $id),
        'user' => admin_url('team/' . $id),
        'employee' => admin_url('employees/' . $id),
        'payroll' => admin_url('payroll/' . $id),
        default => null,
    };
}

/** A short name for what each entry is about, looked up in batches. */
function activity_subjects(array $rows): array
{
    $ids = [];
    foreach ($rows as $r) {
        $ids[$r['entity_type']][] = (int) $r['entity_id'];
    }
    $names = [];
    if (!empty($ids['application'])) {
        foreach (db()->all("SELECT a.id, CONCAT(c.first_name, ' ', c.last_name, ', ', j.title) AS n FROM applications a JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id WHERE a.id IN (" . in_list($ids['application']) . ')', $ids['application']) as $r) {
            $names['application'][(int) $r['id']] = $r['n'];
        }
    }
    if (!empty($ids['candidate'])) {
        foreach (db()->all("SELECT id, CONCAT(first_name, ' ', last_name) AS n FROM candidates WHERE id IN (" . in_list($ids['candidate']) . ')', $ids['candidate']) as $r) {
            $names['candidate'][(int) $r['id']] = $r['n'];
        }
    }
    if (!empty($ids['lead'])) {
        $names['lead'] = db()->pairs('SELECT id, title FROM leads WHERE id IN (' . in_list($ids['lead']) . ')', $ids['lead']);
    }
    if (!empty($ids['contact'])) {
        $names['contact'] = db()->pairs('SELECT id, name FROM contacts WHERE id IN (' . in_list($ids['contact']) . ')', $ids['contact']);
    }
    if (!empty($ids['employee'])) {
        $names['employee'] = db()->pairs("SELECT id, CONCAT(first_name, ' ', last_name) FROM employees WHERE id IN (" . in_list($ids['employee']) . ')', $ids['employee']);
    }
    if (!empty($ids['payroll'])) {
        $names['payroll'] = db()->pairs('SELECT id, title FROM payroll_runs WHERE id IN (' . in_list($ids['payroll']) . ')', $ids['payroll']);
    }
    if (!empty($ids['user'])) {
        $names['user'] = db()->pairs('SELECT id, name FROM users WHERE id IN (' . in_list($ids['user']) . ')', $ids['user']);
    }
    return $names;
}

function activity_page(): void
{
    $where = [];
    $params = [];
    $type = input('type');
    if (isset(ACTIVITY_ENTITIES[$type])) {
        $where[] = 'a.entity_type = ?';
        $params[] = $type;
    }
    if ($uid = input_int('user')) {
        $where[] = 'a.user_id = ?';
        $params[] = $uid;
    }
    if (input('from') !== '' && ($from = parse_dt(input('from'), false))) {
        $where[] = 'a.created_at >= ?';
        $params[] = $from . ' 00:00:00';
    }
    $sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $p = paginate((int) db()->value('SELECT COUNT(*) FROM activities a' . $sqlWhere, $params), 50);
    $rows = db()->all('SELECT a.*, u.name AS user_name FROM activities a LEFT JOIN users u ON u.id = a.user_id' . $sqlWhere . ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'], $params);
    admin_view('activity', ['title' => 'Activity log', 'nav' => 'activity', 'rows' => $rows, 'p' => $p, 'subjects' => activity_subjects($rows)]);
}

/* ------------------------------------------------------------------ first-run checklist */
function onboarding_steps(): array
{
    $jobs = (int) db()->value("SELECT COUNT(*) FROM jobs WHERE status = 'open' AND slug <> 'open-application'");
    return [
        ['done' => setting('mail_transport', 'mail') === 'smtp' && setting('smtp_password', '') !== '', 'title' => 'Connect your mailbox', 'text' => 'So notifications and candidate emails arrive and don\'t land in spam.', 'href' => admin_url('settings') . '#email', 'cta' => 'Set up email'],
        ['done' => (int) db()->value('SELECT COUNT(*) FROM users') > 1, 'title' => 'Add your team', 'text' => 'Give recruiters, sales and interviewers their own sign-in and the right access.', 'href' => admin_url('team/new'), 'cta' => 'Add a teammate'],
        ['done' => $jobs > 0, 'title' => 'Publish your first job', 'text' => 'It appears on the careers page straight away, ready for applications.', 'href' => admin_url('jobs/new'), 'cta' => 'Post a job'],
        ['done' => (int) db()->value('SELECT COUNT(*) FROM leads') > 0, 'title' => 'Send a test enquiry', 'text' => 'Use the contact form on the website and watch it arrive in Leads.', 'href' => url('contact/'), 'cta' => 'Open the contact page'],
    ];
}

function onboarding_dismiss(): void
{
    set_setting('onboarding_dismissed', '1');
    back(admin_url());
}
