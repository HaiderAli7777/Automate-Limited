<?php
/* Routing, access control and rendering for the team area. */
declare(strict_types=1);

require APP_DIR . '/schema.php';

/**
 * [method, pattern, handler, access]. Access is 'public' (no sign-in), 'user'
 * (any signed-in user) or a permission from PERMISSIONS; handlers can narrow further.
 */
function admin_routes(): array
{
    // access: 'public', 'user' (anyone signed in), a permission, or a list of permissions that are all required
    $viewAts = 'ats';
    $editAts = 'ats.manage';
    $viewCrm = 'crm';
    $editCrm = 'crm.manage';
    return [
        ['GET', 'login', 'auth_login_page', 'public'],
        ['POST', 'login', 'auth_login_submit', 'public'],
        ['POST', 'logout', 'auth_logout_submit', 'user'],
        ['GET', 'forgot', 'auth_forgot_page', 'public'],
        ['POST', 'forgot', 'auth_forgot_submit', 'public'],
        ['GET', 'reset', 'auth_reset_page', 'public'],
        ['POST', 'reset', 'auth_reset_submit', 'public'],
        ['GET', 'account', 'account_page', 'user'],
        ['POST', 'account', 'account_save', 'user'],

        ['GET', '', 'dashboard_overview', 'user'],
        ['POST', 'onboarding/dismiss', 'onboarding_dismiss', 'settings'],
        ['GET', 'dashboard/ats', 'dashboard_ats', $viewAts],
        ['GET', 'dashboard/crm', 'dashboard_crm', $viewCrm],
        ['GET', 'search', 'search_page', 'user'],
        ['GET', 'notifications', 'notifications_page', 'user'],
        ['GET', 'notifications/(\d+)/open', 'notifications_open', 'user'],
        ['POST', 'notifications/read-all', 'notifications_read_all', 'user'],
        ['GET', 'activity', 'activity_page', 'audit.view'],

        ['GET', 'jobs', 'jobs_index', $viewAts],
        ['GET', 'jobs/new', 'jobs_form', $editAts],
        ['POST', 'jobs/new', 'jobs_save', $editAts],
        ['GET', 'jobs/(\d+)', 'jobs_show', $viewAts],
        ['GET', 'jobs/(\d+)/edit', 'jobs_form', $editAts],
        ['POST', 'jobs/(\d+)/edit', 'jobs_save', $editAts],
        ['POST', 'jobs/(\d+)/status', 'jobs_status', $editAts],
        ['POST', 'jobs/(\d+)/duplicate', 'jobs_duplicate', $editAts],
        ['POST', 'jobs/(\d+)/delete', 'jobs_delete', [$editAts, 'data.delete']],

        ['GET', 'candidates', 'candidates_index', $viewAts],
        ['GET', 'candidates/export', 'candidates_export', [$viewAts, 'data.export']],
        ['GET', 'candidates/new', 'candidates_form', $editAts],
        ['POST', 'candidates/new', 'candidates_create', $editAts],
        ['GET', 'candidates/(\d+)', 'candidates_show', $viewAts],
        ['GET', 'candidates/(\d+)/edit', 'candidates_edit', $editAts],
        ['POST', 'candidates/(\d+)/edit', 'candidates_update', $editAts],
        ['POST', 'candidates/(\d+)/apply', 'candidates_add_to_job', $editAts],
        ['POST', 'candidates/(\d+)/files', 'candidates_upload', $editAts],
        ['POST', 'candidates/(\d+)/delete', 'candidates_delete', [$editAts, 'data.delete']],

        ['GET', 'pipeline', 'pipeline_board', $viewAts],
        ['POST', 'applications/bulk', 'applications_bulk', $editAts],
        ['GET', 'applications/(\d+)', 'applications_show', 'interviews'],
        ['POST', 'applications/(\d+)/stage', 'applications_stage', $editAts],
        ['POST', 'applications/(\d+)/note', 'applications_note', 'interviews'],
        ['POST', 'applications/(\d+)/email', 'applications_email', $editAts],
        ['GET', 'applications/(\d+)/email-preview', 'applications_email_preview', $editAts],
        ['POST', 'applications/(\d+)/offer', 'applications_offer', $editAts],
        ['POST', 'applications/(\d+)/owner', 'applications_owner', $editAts],

        ['GET', 'interviews', 'interviews_index', 'interviews'],
        ['GET', 'interviews/new', 'interviews_form', $editAts],
        ['POST', 'interviews/new', 'interviews_save', $editAts],
        ['GET', 'interviews/(\d+)', 'interviews_show', 'interviews'],
        ['GET', 'interviews/(\d+)/edit', 'interviews_form', $editAts],
        ['POST', 'interviews/(\d+)/edit', 'interviews_save', $editAts],
        ['POST', 'interviews/(\d+)/status', 'interviews_status', $editAts],
        ['POST', 'interviews/(\d+)/feedback', 'interviews_feedback', 'interviews'],
        ['GET', 'interviews/(\d+)/ics', 'interviews_ics', 'interviews'],

        ['GET', 'leads', 'leads_index', $viewCrm],
        ['GET', 'leads/board', 'leads_board', $viewCrm],
        ['GET', 'leads/export', 'leads_export', [$viewCrm, 'data.export']],
        ['POST', 'leads/bulk', 'leads_bulk', $editCrm],
        ['GET', 'leads/new', 'leads_form', $editCrm],
        ['POST', 'leads/new', 'leads_create', $editCrm],
        ['GET', 'leads/(\d+)', 'leads_show', $viewCrm],
        ['POST', 'leads/(\d+)/update', 'leads_update', $editCrm],
        ['POST', 'leads/(\d+)/stage', 'leads_stage', $editCrm],
        ['POST', 'leads/(\d+)/activity', 'leads_activity', $editCrm],
        ['POST', 'leads/(\d+)/email', 'leads_email', $editCrm],
        ['GET', 'leads/(\d+)/email-preview', 'leads_email_preview', $editCrm],
        ['POST', 'leads/(\d+)/delete', 'leads_delete', [$editCrm, 'data.delete']],

        ['GET', 'contacts', 'contacts_index', $viewCrm],
        ['GET', 'contacts/new', 'contacts_form', $editCrm],
        ['POST', 'contacts/new', 'contacts_save', $editCrm],
        ['GET', 'contacts/(\d+)', 'contacts_show', $viewCrm],
        ['POST', 'contacts/(\d+)', 'contacts_save', $editCrm],
        ['POST', 'contacts/(\d+)/delete', 'contacts_delete', [$editCrm, 'data.delete']],

        ['GET', 'tasks', 'tasks_index', 'user'],
        ['POST', 'tasks/new', 'tasks_create', 'user'],
        ['POST', 'tasks/(\d+)/toggle', 'tasks_toggle', 'user'],
        ['POST', 'tasks/(\d+)/delete', 'tasks_delete', 'user'],

        ['POST', 'activities/(\d+)/delete', 'activities_delete', 'user'],
        ['GET', 'files/(\d+)', 'files_download', 'interviews'],
        ['POST', 'files/(\d+)/delete', 'files_delete', $editAts],

        ['GET', 'team', 'team_index', 'team'],
        ['GET', 'team/new', 'team_form', 'team'],
        ['POST', 'team/new', 'team_save', 'team'],
        ['GET', 'team/(\d+)', 'team_form', 'team'],
        ['POST', 'team/(\d+)', 'team_save', 'team'],
        ['POST', 'team/(\d+)/invite', 'team_invite', 'team'],
        ['POST', 'team/(\d+)/toggle', 'team_toggle', 'team'],

        ['GET', 'settings', 'settings_page', 'settings'],
        ['POST', 'settings', 'settings_save', 'settings'],
        ['POST', 'settings/test-email', 'settings_test_email', 'settings'],
        ['GET', 'settings/stages', 'stages_page', 'settings'],
        ['POST', 'settings/stages', 'stages_save', 'settings'],
        ['GET', 'settings/templates', 'templates_index', 'settings'],
        ['GET', 'settings/templates/(\d+)', 'templates_form', 'settings'],
        ['POST', 'settings/templates/(\d+)', 'templates_save', 'settings'],
    ];
}

function admin_path(): string
{
    if (isset($_GET['r'])) {
        return trim((string) $_GET['r'], '/');
    }
    $path = current_path();
    $base = base_path() . '/admin';
    if (str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    $path = trim($path, '/');
    return $path === 'index.php' ? '' : $path;
}

function admin_run(): void
{
    send_admin_headers();
    if (!app_installed()) {
        redirect(url('install/'));
    }
    session_boot();

    foreach (glob(APP_DIR . '/admin/controllers/*.php') ?: [] as $file) {
        require_once $file;
    }

    if ((int) setting('schema_version', '0') < schema_version()) {
        migrate(db());
    }

    $path = admin_path();
    $method = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 'POST' : 'GET';
    foreach (admin_routes() as [$m, $pattern, $handler, $access]) {
        if ($m !== $method || !preg_match('#^' . $pattern . '$#', $path, $matches)) {
            continue;
        }
        if ($access !== 'public') {
            if (!auth_user()) {
                if (is_ajax()) {
                    json_out(['ok' => false, 'error' => 'Your session ended. Sign in again.'], 401);
                }
                $next = $method === 'GET' ? '?next=' . rawurlencode((string) ($_SERVER['REQUEST_URI'] ?? '')) : '';
                redirect(admin_url('login') . $next);
            }
            if ($access !== 'user') {
                foreach ((array) $access as $perm) {
                    require_can($perm);
                }
            }
        }
        if ($method === 'POST') {
            csrf_verify();
        }
        $args = array_map('intval', array_slice($matches, 1));
        $handler(...$args);
        return;
    }
    if (!auth_user()) {
        redirect(admin_url('login'));
    }
    abort(404, 'That page doesn\'t exist in the team area.');
}

/** Render an admin view inside the app shell. */
function admin_view(string $view, array $data = []): void
{
    render('admin/' . $view, $data, 'admin/layout');
}

/** Sidebar counters, cached per request. */
function nav_counts(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }
    $uid = auth_id();
    $c = [
        'tasks' => (int) db()->value('SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND completed_at IS NULL AND due_at IS NOT NULL AND due_at <= ?', [$uid, date('Y-m-d 23:59:59')]),
        'overdue' => (int) db()->value('SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND completed_at IS NULL AND due_at IS NOT NULL AND due_at < ?', [$uid, now()]),
        'new_apps' => 0,
        'new_leads' => 0,
        'my_interviews' => 0,
        'notifications' => (int) db()->value('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$uid]),
    ];
    if (user_can('ats')) {
        $first = first_stage_id(ats_stages(), 'active');
        $c['new_apps'] = $first ? (int) db()->value("SELECT COUNT(*) FROM applications WHERE stage_id = ? AND status = 'active'", [$first]) : 0;
    }
    if (user_can('crm')) {
        $first = first_stage_id(lead_stages(), 'open');
        $c['new_leads'] = $first ? (int) db()->value("SELECT COUNT(*) FROM leads WHERE stage_id = ? AND status = 'open'" . crm_scope(), [$first]) : 0;
    }
    if (user_can('interviews')) {
        $c['my_interviews'] = (int) db()->value(
            "SELECT COUNT(*) FROM interviews i JOIN interview_panel p ON p.interview_id = i.id WHERE p.user_id = ? AND i.status = 'scheduled' AND i.scheduled_at >= ? AND i.scheduled_at < ?",
            [$uid, date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+7 days'))]
        );
    }
    return $c;
}

/** Pagination footer for list tables. */
function pager(array $p): string
{
    if ($p['pages'] <= 1) {
        return '';
    }
    $from = $p['offset'] + 1;
    $to = min($p['total'], $p['offset'] + $p['per']);
    $html = '<div class="pager"><span>' . number_format($from) . '-' . number_format($to) . ' of ' . number_format($p['total']) . '</span><span class="pager__links">';
    if ($p['page'] > 1) {
        $html .= '<a class="btn btn--quiet btn--sm" href="' . e(qs(['page' => $p['page'] - 1])) . '">Previous</a>';
    }
    if ($p['page'] < $p['pages']) {
        $html .= '<a class="btn btn--quiet btn--sm" href="' . e(qs(['page' => $p['page'] + 1])) . '">Next</a>';
    }
    return $html . '</span></div>';
}

/** Stream rows as a CSV download. */
function csv_download(string $filename, array $header, iterable $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $header, ',', '"', '\\');
    foreach ($rows as $row) {
        // neutralise spreadsheet formulas in user-supplied values
        $row = array_map(static function ($v) {
            $v = (string) ($v ?? '');
            return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
        }, $row);
        fputcsv($out, $row, ',', '"', '\\');
    }
    fclose($out);
    exit;
}

/** Options for a user <select>, optionally only people holding a permission. */
function user_options(?int $selected, string $emptyLabel = 'Unassigned', ?string $perm = null): string
{
    $html = '<option value="">' . e($emptyLabel) . '</option>';
    foreach (active_users() as $u) {
        if ($perm !== null && !user_can($perm, $u) && (int) $u['id'] !== (int) $selected) {
            continue;
        }
        $html .= '<option value="' . (int) $u['id'] . '"' . selected($selected ?? '', $u['id']) . '>' . e($u['name']) . '</option>';
    }
    return $html;
}

function options(array $map, $selected): string
{
    $html = '';
    foreach ($map as $value => $label) {
        $html .= '<option value="' . e((string) $value) . '"' . selected($selected ?? '', $value) . '>' . e($label) . '</option>';
    }
    return $html;
}

/** Tooltip + keyboard focus attributes for a chart mark. */
function tip(string $label, string $value): string
{
    return ' tabindex="0" data-tip-label="' . e($label) . '" data-tip-value="' . e($value) . '" aria-label="' . e($label . ': ' . $value) . '"';
}

/* ------------------------------------------------------------------ form fields
   Each helper renders label + control + help + error, and re-fills from the
   last submission when a save failed (see remember_input()). */
function fval(string $name, $value): string
{
    return has_old() ? old($name) : (string) ($value ?? '');
}

function field_id(string $name): string
{
    return 'f-' . trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
}

function field_wrap(string $name, string $label, string $control, array $o): string
{
    $errors = form_errors();
    $err = $errors[$name] ?? '';
    $cls = 'field' . ($err ? ' has-error' : '') . (isset($o['class']) ? ' ' . $o['class'] : '');
    return '<div class="' . e($cls) . '"><label for="' . e($o['id'] ?? field_id($name)) . '">' . e($label)
        . (!empty($o['optional']) ? ' <span class="opt">(optional)</span>' : '') . '</label>' . $control
        . (!empty($o['help']) ? '<p class="help">' . e($o['help']) . '</p>' : '')
        . ($err ? '<p class="error">' . e($err) . '</p>' : '') . '</div>';
}

function field_attrs(array $o): string
{
    $out = '';
    foreach (($o['attrs'] ?? []) as $k => $v) {
        $out .= $v === true ? ' ' . $k : ' ' . $k . '="' . e((string) $v) . '"';
    }
    if (!empty($o['required'])) {
        $out .= ' required';
    }
    if (isset($o['placeholder'])) {
        $out .= ' placeholder="' . e($o['placeholder']) . '"';
    }
    return $out;
}

function fi(string $name, string $label, $value = '', array $o = []): string
{
    $type = $o['type'] ?? 'text';
    $val = $type === 'password' ? '' : fval($name, $value);
    $id = $o['id'] ?? field_id($name);
    $control = '<input class="input" id="' . e($id) . '" name="' . e($name) . '" type="' . e($type) . '" value="' . e($val) . '"' . field_attrs($o) . '>';
    return field_wrap($name, $label, $control, $o);
}

function ft(string $name, string $label, $value = '', array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    $control = '<textarea class="textarea' . (isset($o['size']) ? ' textarea--' . e($o['size']) : '') . '" id="' . e($id) . '" name="' . e($name) . '"'
        . field_attrs($o) . '>' . e(fval($name, $value)) . '</textarea>';
    return field_wrap($name, $label, $control, $o);
}

/** @param array|string $options a value => label map, or ready-made <option> HTML */
function fs(string $name, string $label, $options, $value = '', array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    $html = is_array($options) ? options($options, fval($name, $value)) : $options;
    $control = '<select class="select" id="' . e($id) . '" name="' . e($name) . '"' . field_attrs($o) . '>' . $html . '</select>';
    return field_wrap($name, $label, $control, $o);
}

function fc(string $name, string $label, bool $on, string $help = ''): string
{
    $state = has_old() ? old($name) === '1' : $on;
    return '<div class="field"><label class="checkbox"><input type="hidden" name="' . e($name) . '" value="0"><input type="checkbox" name="' . e($name) . '" value="1"'
        . checked($state) . '><span>' . e($label) . ($help ? '<span class="help" style="display:block">' . e($help) . '</span>' : '') . '</span></label></div>';
}
