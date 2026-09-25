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
        ['GET', 'dashboard/ats', 'dashboard_ats', 'ats'],
        ['GET', 'dashboard/crm', 'dashboard_crm', 'crm'],
        ['GET', 'search', 'search_page', 'user'],

        ['GET', 'jobs', 'jobs_index', 'ats'],
        ['GET', 'jobs/new', 'jobs_form', 'ats'],
        ['POST', 'jobs/new', 'jobs_save', 'ats'],
        ['GET', 'jobs/(\d+)', 'jobs_show', 'ats'],
        ['GET', 'jobs/(\d+)/edit', 'jobs_form', 'ats'],
        ['POST', 'jobs/(\d+)/edit', 'jobs_save', 'ats'],
        ['POST', 'jobs/(\d+)/status', 'jobs_status', 'ats'],
        ['POST', 'jobs/(\d+)/duplicate', 'jobs_duplicate', 'ats'],
        ['POST', 'jobs/(\d+)/delete', 'jobs_delete', 'ats'],

        ['GET', 'candidates', 'candidates_index', 'ats'],
        ['GET', 'candidates/export', 'candidates_export', 'ats'],
        ['GET', 'candidates/new', 'candidates_form', 'ats'],
        ['POST', 'candidates/new', 'candidates_create', 'ats'],
        ['GET', 'candidates/(\d+)', 'candidates_show', 'ats'],
        ['GET', 'candidates/(\d+)/edit', 'candidates_edit', 'ats'],
        ['POST', 'candidates/(\d+)/edit', 'candidates_update', 'ats'],
        ['POST', 'candidates/(\d+)/apply', 'candidates_add_to_job', 'ats'],
        ['POST', 'candidates/(\d+)/files', 'candidates_upload', 'ats'],
        ['POST', 'candidates/(\d+)/delete', 'candidates_delete', 'ats'],

        ['GET', 'pipeline', 'pipeline_board', 'ats'],
        ['GET', 'applications/(\d+)', 'applications_show', 'interviews'],
        ['POST', 'applications/(\d+)/stage', 'applications_stage', 'ats'],
        ['POST', 'applications/(\d+)/note', 'applications_note', 'interviews'],
        ['POST', 'applications/(\d+)/email', 'applications_email', 'ats'],
        ['GET', 'applications/(\d+)/email-preview', 'applications_email_preview', 'ats'],
        ['POST', 'applications/(\d+)/offer', 'applications_offer', 'ats'],
        ['POST', 'applications/(\d+)/owner', 'applications_owner', 'ats'],

        ['GET', 'interviews', 'interviews_index', 'interviews'],
        ['GET', 'interviews/new', 'interviews_form', 'ats'],
        ['POST', 'interviews/new', 'interviews_save', 'ats'],
        ['GET', 'interviews/(\d+)', 'interviews_show', 'interviews'],
        ['GET', 'interviews/(\d+)/edit', 'interviews_form', 'ats'],
        ['POST', 'interviews/(\d+)/edit', 'interviews_save', 'ats'],
        ['POST', 'interviews/(\d+)/status', 'interviews_status', 'ats'],
        ['POST', 'interviews/(\d+)/feedback', 'interviews_feedback', 'interviews'],
        ['GET', 'interviews/(\d+)/ics', 'interviews_ics', 'interviews'],

        ['GET', 'leads', 'leads_index', 'crm'],
        ['GET', 'leads/board', 'leads_board', 'crm'],
        ['GET', 'leads/export', 'leads_export', 'crm'],
        ['GET', 'leads/new', 'leads_form', 'crm'],
        ['POST', 'leads/new', 'leads_create', 'crm'],
        ['GET', 'leads/(\d+)', 'leads_show', 'crm'],
        ['POST', 'leads/(\d+)/update', 'leads_update', 'crm'],
        ['POST', 'leads/(\d+)/stage', 'leads_stage', 'crm'],
        ['POST', 'leads/(\d+)/activity', 'leads_activity', 'crm'],
        ['POST', 'leads/(\d+)/email', 'leads_email', 'crm'],
        ['GET', 'leads/(\d+)/email-preview', 'leads_email_preview', 'crm'],
        ['POST', 'leads/(\d+)/delete', 'leads_delete', 'crm'],

        ['GET', 'contacts', 'contacts_index', 'crm'],
        ['GET', 'contacts/new', 'contacts_form', 'crm'],
        ['POST', 'contacts/new', 'contacts_save', 'crm'],
        ['GET', 'contacts/(\d+)', 'contacts_show', 'crm'],
        ['POST', 'contacts/(\d+)', 'contacts_save', 'crm'],
        ['POST', 'contacts/(\d+)/delete', 'contacts_delete', 'crm'],

        ['GET', 'tasks', 'tasks_index', 'user'],
        ['POST', 'tasks/new', 'tasks_create', 'user'],
        ['POST', 'tasks/(\d+)/toggle', 'tasks_toggle', 'user'],
        ['POST', 'tasks/(\d+)/delete', 'tasks_delete', 'user'],

        ['POST', 'activities/(\d+)/delete', 'activities_delete', 'user'],
        ['GET', 'files/(\d+)', 'files_download', 'interviews'],
        ['POST', 'files/(\d+)/delete', 'files_delete', 'ats'],

        ['GET', 'team', 'team_index', 'team'],
        ['GET', 'team/new', 'team_form', 'team'],
        ['POST', 'team/new', 'team_save', 'team'],
        ['GET', 'team/(\d+)', 'team_form', 'team'],
        ['POST', 'team/(\d+)', 'team_save', 'team'],

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
                require_can($access);
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
    ];
    if (user_can('ats')) {
        $first = first_stage_id(ats_stages(), 'active');
        $c['new_apps'] = $first ? (int) db()->value("SELECT COUNT(*) FROM applications WHERE stage_id = ? AND status = 'active'", [$first]) : 0;
    }
    if (user_can('crm')) {
        $first = first_stage_id(lead_stages(), 'open');
        $c['new_leads'] = $first ? (int) db()->value("SELECT COUNT(*) FROM leads WHERE stage_id = ? AND status = 'open'", [$first]) : 0;
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

/** Options for a user <select>. */
function user_options(?int $selected, string $emptyLabel = 'Unassigned', array $roles = []): string
{
    $html = '<option value="">' . e($emptyLabel) . '</option>';
    foreach (active_users() as $u) {
        if ($roles && !in_array($u['role'], $roles, true)) {
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
