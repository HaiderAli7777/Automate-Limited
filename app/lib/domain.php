<?php
/* Shared business logic for the ATS and CRM. */
declare(strict_types=1);

const STAGE_COLORS = ['slate', 'blue', 'cyan', 'teal', 'green', 'amber', 'orange', 'red', 'violet', 'pink'];

const EMPLOYMENT_TYPES = ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'internship' => 'Internship', 'temporary' => 'Temporary'];
const WORKPLACES = ['onsite' => 'On-site', 'hybrid' => 'Hybrid', 'remote' => 'Remote'];
const EXPERIENCE_LEVELS = ['' => 'Any level', 'entry' => 'Entry level', 'mid' => 'Mid level', 'senior' => 'Senior', 'lead' => 'Lead / manager'];
const JOB_STATUSES = ['draft' => 'Draft', 'open' => 'Open', 'paused' => 'Paused', 'closed' => 'Closed'];
const JOB_STATUS_COLORS = ['draft' => 'slate', 'open' => 'green', 'paused' => 'amber', 'closed' => 'red'];
const QUESTION_TYPES = ['text' => 'Short answer', 'textarea' => 'Long answer', 'yesno' => 'Yes / no', 'select' => 'Choose one', 'number' => 'Number'];

const CANDIDATE_SOURCES = ['careers' => 'Careers page', 'referral' => 'Referral', 'linkedin' => 'LinkedIn', 'job_board' => 'Job board', 'agency' => 'Agency', 'walk_in' => 'Walk-in', 'other' => 'Other'];
const REJECTION_REASONS = ['Not enough experience', 'Skills don\'t match the role', 'Salary expectations', 'Position filled', 'Candidate withdrew', 'Didn\'t pass the assessment', 'No response from candidate', 'Other'];

const INTERVIEW_TYPES = ['phone' => 'Phone screen', 'video' => 'Video call', 'onsite' => 'In person', 'technical' => 'Technical', 'hr' => 'HR', 'final' => 'Final'];
const INTERVIEW_STATUSES = ['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No-show'];
const INTERVIEW_STATUS_COLORS = ['scheduled' => 'blue', 'completed' => 'green', 'cancelled' => 'slate', 'no_show' => 'red'];
const RECOMMENDATIONS = ['strong_yes' => 'Strong yes', 'yes' => 'Yes', 'no' => 'No', 'strong_no' => 'Strong no'];
const RECOMMENDATION_COLORS = ['strong_yes' => 'green', 'yes' => 'teal', 'no' => 'orange', 'strong_no' => 'red'];
const OFFER_STATUSES = ['' => 'No offer yet', 'draft' => 'Drafting', 'sent' => 'Sent', 'accepted' => 'Accepted', 'declined' => 'Declined'];

const LEAD_SOURCES = ['website' => 'Website form', 'email' => 'Email', 'phone' => 'Phone call', 'whatsapp' => 'WhatsApp', 'referral' => 'Referral', 'linkedin' => 'LinkedIn', 'event' => 'Event', 'other' => 'Other'];
const LEAD_PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'];
const LEAD_PRIORITY_COLORS = ['low' => 'slate', 'normal' => 'blue', 'high' => 'orange'];
const LOST_REASONS = ['Price', 'Timing', 'Chose another provider', 'No budget', 'No response', 'Not a fit for us', 'Other'];
const ACTIVITY_KINDS = ['note' => 'Note', 'call' => 'Call', 'meeting' => 'Meeting', 'email' => 'Email', 'whatsapp' => 'WhatsApp'];

function lead_services(): array
{
    $raw = (string) setting('lead_services', "Odoo ERP\nWebsite development\nSEO\nDigital marketing\nGraphic design\nCustom solutions");
    return array_values(array_filter(array_map('trim', explode("\n", $raw))));
}

function currencies(): array
{
    return ['PKR', 'AED', 'SAR', 'USD', 'GBP', 'EUR'];
}

function badge(string $label, string $color = 'slate', string $extra = ''): string
{
    $color = in_array($color, STAGE_COLORS, true) ? $color : 'slate';
    return '<span class="badge c-' . $color . ($extra ? ' ' . e($extra) : '') . '">' . e($label) . '</span>';
}

/* ------------------------------------------------------------------ stages */
function ats_stages(): array
{
    static $cache = null;
    return $cache ??= db()->keyed('SELECT id, name, color, kind, sort_order FROM ats_stages ORDER BY sort_order, id');
}

function lead_stages(): array
{
    static $cache = null;
    return $cache ??= db()->keyed('SELECT id, name, color, kind, probability, sort_order FROM lead_stages ORDER BY sort_order, id');
}

function first_stage_id(array $stages, string $kind): ?int
{
    foreach ($stages as $s) {
        if ($s['kind'] === $kind) {
            return (int) $s['id'];
        }
    }
    return null;
}

/** CSS colour for a stage's dot (board columns, calendar entries). */
function stage_dot(string $color): string
{
    $color = in_array($color, STAGE_COLORS, true) ? $color : 'slate';
    return 'var(--' . ($color === 'cyan' ? 'cyan-dot' : $color) . ')';
}

function stage_badge(?array $stage): string
{
    return $stage ? badge($stage['name'], $stage['color']) : badge('Unknown');
}

/* ------------------------------------------------------------------ activity timeline */
function log_activity(string $entityType, int $entityId, string $type, string $title, string $body = '', array $meta = [], ?int $userId = null): int
{
    return db()->insert('activities', [
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'user_id' => $userId ?? auth_id(),
        'type' => $type,
        'title' => mb_substr($title, 0, 255),
        'body' => $body,
        'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        'created_at' => now(),
    ]);
}

/**
 * Timeline rows for one or more entities, newest first.
 * @param array<int,array{0:string,1:int[]}> $targets
 */
function activities_for(array $targets, int $limit = 200): array
{
    $where = [];
    $params = [];
    foreach ($targets as [$type, $ids]) {
        $ids = array_values(array_filter(array_map('intval', (array) $ids)));
        if (!$ids) {
            continue;
        }
        $where[] = '(a.entity_type = ? AND a.entity_id IN (' . in_list($ids) . '))';
        $params[] = $type;
        array_push($params, ...$ids);
    }
    if (!$where) {
        return [];
    }
    return db()->all(
        'SELECT a.*, u.name AS user_name FROM activities a LEFT JOIN users u ON u.id = a.user_id WHERE '
        . implode(' OR ', $where) . ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . (int) $limit,
        $params
    );
}

function activity_icon(string $type): string
{
    return [
        'note' => 'note-pencil', 'call' => 'phone-call', 'meeting' => 'users-three', 'email' => 'envelope-simple',
        'whatsapp' => 'chat-circle-text', 'stage' => 'arrow-right', 'created' => 'plus', 'interview' => 'calendar-dots',
        'feedback' => 'star', 'file' => 'paperclip', 'task' => 'check-square', 'offer' => 'handshake', 'updated' => 'pencil-simple',
        'system' => 'info',
    ][$type] ?? 'info';
}

/* ------------------------------------------------------------------ email templates */
function email_template(string $key): ?array
{
    return db()->one('SELECT * FROM email_templates WHERE tkey = ?', [$key]);
}

function fill_template(string $text, array $vars): string
{
    return (string) preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', static function (array $m) use ($vars): string {
        return array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : '';
    }, $text);
}

const TEMPLATE_VARS = [
    'ats' => ['candidate_name', 'candidate_first_name', 'job_title', 'company_name', 'sender_name', 'interview_date', 'interview_time', 'interview_type', 'interview_duration', 'interview_location', 'careers_url'],
    'crm' => ['contact_name', 'contact_first_name', 'contact_company', 'service', 'company_name', 'sender_name'],
];

function candidate_vars(array $app): array
{
    $first = (string) ($app['first_name'] ?? '');
    return [
        'candidate_name' => trim($first . ' ' . ($app['last_name'] ?? '')),
        'candidate_first_name' => $first,
        'job_title' => (string) ($app['job_title'] ?? ''),
        'company_name' => company_name(),
        'sender_name' => (string) (auth_user()['name'] ?? company_name()),
        'careers_url' => abs_url('careers/'),
    ];
}

function interview_vars(array $iv): array
{
    $start = ts($iv['scheduled_at']) ?? time();
    $where = trim((string) ($iv['meeting_url'] ?? '')) !== '' ? (string) $iv['meeting_url'] : (string) ($iv['location'] ?? '');
    return [
        'interview_date' => date('l j F Y', $start),
        'interview_time' => date('g:i a', $start) . ' (' . date('T', $start) . ')',
        'interview_type' => INTERVIEW_TYPES[$iv['itype']] ?? 'Interview',
        'interview_duration' => (int) $iv['duration_minutes'] . ' minutes',
        'interview_location' => $where !== '' ? $where : 'We will confirm the details separately.',
    ];
}

function lead_vars(array $lead): array
{
    $name = (string) ($lead['contact_name'] ?? '');
    return [
        'contact_name' => $name,
        'contact_first_name' => explode(' ', trim($name))[0] ?? '',
        'contact_company' => (string) ($lead['contact_company'] ?? ''),
        'service' => (string) ($lead['service'] ?? ''),
        'company_name' => company_name(),
        'sender_name' => (string) (auth_user()['name'] ?? company_name()),
    ];
}

/** Send a stored template; returns [ok, subject, body]. */
function send_template(string $key, string $to, array $vars, array $opts = []): array
{
    $tpl = email_template($key);
    if (!$tpl) {
        return [false, '', ''];
    }
    $subject = fill_template((string) $tpl['subject'], $vars);
    $body = fill_template((string) $tpl['body'], $vars);
    $ok = Mailer::send($to, $subject, $body, $opts);
    return [$ok, $subject, $body];
}

/** Internal notification to a comma separated list of team addresses. */
function notify_team(string $addresses, string $subject, string $body, array $opts = []): void
{
    foreach (array_filter(array_map('trim', explode(',', $addresses))) as $addr) {
        if (valid_email($addr)) {
            Mailer::send($addr, $subject, $body, $opts);
        }
    }
}

/* ------------------------------------------------------------------ applications */
function application_full(int $id): ?array
{
    return db()->one(
        'SELECT a.*, c.first_name, c.last_name, c.email, c.phone, c.location, c.current_title, c.current_company,
                c.linkedin_url, c.portfolio_url, c.experience_years, c.expected_salary, c.notice_period, c.tags, c.source AS candidate_source,
                j.title AS job_title, j.slug AS job_slug, j.status AS job_status, j.department
         FROM applications a
         JOIN candidates c ON c.id = a.candidate_id
         JOIN jobs j ON j.id = a.job_id
         WHERE a.id = ?',
        [$id]
    );
}

function candidate_name(array $row): string
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
}

/** Move an application to a stage, recording why. Returns the new stage or null if unchanged. */
function application_move(int $appId, int $stageId, string $reason = ''): ?array
{
    $stages = ats_stages();
    $app = db()->one('SELECT id, stage_id FROM applications WHERE id = ?', [$appId]);
    if (!$app || !isset($stages[$stageId]) || (int) $app['stage_id'] === $stageId) {
        return null;
    }
    $to = $stages[$stageId];
    $from = $stages[(int) $app['stage_id']] ?? null;
    $status = $to['kind'] === 'hired' ? 'hired' : ($to['kind'] === 'rejected' ? 'rejected' : 'active');
    $data = ['stage_id' => $stageId, 'status' => $status, 'stage_changed_at' => now(), 'updated_at' => now()];
    $data['hired_at'] = $status === 'hired' ? now() : null;
    $data['rejected_at'] = $status === 'rejected' ? now() : null;
    $data['rejection_reason'] = $status === 'rejected' ? mb_substr($reason, 0, 120) : null;
    db()->update('applications', $data, 'id = ?', [$appId]);
    $title = 'Moved to ' . $to['name'];
    log_activity('application', $appId, 'stage', $title, $status === 'rejected' && $reason !== '' ? 'Reason: ' . $reason : '', [
        'from' => $from['name'] ?? null, 'to' => $to['name'],
    ]);
    return $to;
}

/** Interviewers may only see applications they are on the panel for. */
function can_view_application(int $appId): bool
{
    if (user_can('ats')) {
        return true;
    }
    if (!is_role('interviewer')) {
        return false;
    }
    return (bool) db()->value(
        'SELECT 1 FROM interviews i JOIN interview_panel p ON p.interview_id = i.id WHERE i.application_id = ? AND p.user_id = ? LIMIT 1',
        [$appId, auth_id()]
    );
}

function feedback_summary(int $appId): array
{
    $row = db()->one(
        'SELECT COUNT(f.id) AS n, AVG(f.rating) AS avg_rating FROM interview_feedback f JOIN interviews i ON i.id = f.interview_id WHERE i.application_id = ?',
        [$appId]
    );
    return ['count' => (int) ($row['n'] ?? 0), 'avg' => $row['avg_rating'] !== null ? round((float) $row['avg_rating'], 1) : null];
}

function stars(?float $rating, int $max = 5): string
{
    if ($rating === null) {
        return '<span class="muted">No scores yet</span>';
    }
    $full = (int) round($rating);
    $out = '<span class="stars" title="' . e(number_format($rating, 1)) . ' out of ' . $max . '" aria-label="' . e(number_format($rating, 1)) . ' out of ' . $max . '">';
    for ($i = 1; $i <= $max; $i++) {
        $out .= '<span class="star' . ($i <= $full ? ' is-on' : '') . '" aria-hidden="true">★</span>';
    }
    return $out . '<span class="stars__n">' . e(number_format($rating, 1)) . '</span></span>';
}

/* ------------------------------------------------------------------ leads */
function lead_full(int $id): ?array
{
    return db()->one(
        'SELECT l.*, c.name AS contact_name, c.email AS contact_email, c.phone AS contact_phone, c.company AS contact_company,
                c.job_title AS contact_title, c.country AS contact_country, c.city AS contact_city, c.website AS contact_website
         FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id WHERE l.id = ?',
        [$id]
    );
}

function lead_move(int $leadId, int $stageId, string $reason = ''): ?array
{
    $stages = lead_stages();
    $lead = db()->one('SELECT id, stage_id FROM leads WHERE id = ?', [$leadId]);
    if (!$lead || !isset($stages[$stageId]) || (int) $lead['stage_id'] === $stageId) {
        return null;
    }
    $to = $stages[$stageId];
    $from = $stages[(int) $lead['stage_id']] ?? null;
    $status = $to['kind'] === 'won' ? 'won' : ($to['kind'] === 'lost' ? 'lost' : 'open');
    db()->update('leads', [
        'stage_id' => $stageId,
        'status' => $status,
        'stage_changed_at' => now(),
        'updated_at' => now(),
        'won_at' => $status === 'won' ? now() : null,
        'lost_at' => $status === 'lost' ? now() : null,
        'lost_reason' => $status === 'lost' ? mb_substr($reason, 0, 120) : null,
    ], 'id = ?', [$leadId]);
    log_activity('lead', $leadId, 'stage', 'Moved to ' . $to['name'], $status === 'lost' && $reason !== '' ? 'Reason: ' . $reason : '', [
        'from' => $from['name'] ?? null, 'to' => $to['name'],
    ]);
    return $to;
}

/** Weighted value of a lead: its value times the stage probability. */
function weighted_value(array $lead): float
{
    $stage = lead_stages()[(int) $lead['stage_id']] ?? null;
    return (float) ($lead['value'] ?? 0) * ((int) ($stage['probability'] ?? 0)) / 100;
}

/* ------------------------------------------------------------------ jobs */
function unique_job_slug(string $title, int $ignoreId = 0): string
{
    $base = slugify($title);
    $slug = $base;
    $n = 2;
    while (db()->value('SELECT id FROM jobs WHERE slug = ? AND id <> ?', [$slug, $ignoreId])) {
        $slug = $base . '-' . $n++;
    }
    return $slug;
}

/** Open jobs visible on the careers page. */
function public_jobs(): array
{
    return db()->all(
        "SELECT * FROM jobs WHERE status = 'open' AND listed = 1 AND (closes_at IS NULL OR closes_at >= ?) ORDER BY published_at DESC, id DESC",
        [today()]
    );
}

function job_salary(array $job): string
{
    if (!(int) $job['salary_visible'] || ($job['salary_min'] === null && $job['salary_max'] === null)) {
        return '';
    }
    $cur = (string) ($job['salary_currency'] ?: 'PKR');
    $per = ($job['salary_period'] ?? 'month') === 'year' ? ' a year' : ' a month';
    $min = $job['salary_min'] !== null ? number_format((float) $job['salary_min']) : null;
    $max = $job['salary_max'] !== null ? number_format((float) $job['salary_max']) : null;
    if ($min !== null && $max !== null && $min !== $max) {
        return $cur . ' ' . $min . ' to ' . $max . $per;
    }
    return $cur . ' ' . ($min ?? $max) . $per;
}

/* ------------------------------------------------------------------ tasks */
function open_tasks_for(string $type, int $id): array
{
    return db()->all(
        'SELECT t.*, u.name AS assignee FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to
         WHERE t.entity_type = ? AND t.entity_id = ? ORDER BY t.completed_at IS NOT NULL, t.due_at IS NULL, t.due_at, t.id',
        [$type, $id]
    );
}

function task_due_class(?string $due, ?string $done): string
{
    if ($done) {
        return 'is-done';
    }
    $t = ts($due);
    if ($t === null) {
        return '';
    }
    if ($t < time()) {
        return 'is-overdue';
    }
    return date('Y-m-d', $t) === today() ? 'is-today' : '';
}
