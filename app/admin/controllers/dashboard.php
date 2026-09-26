<?php
/*
 * Dashboards: an overview for everyone, plus an ATS dashboard and a CRM dashboard.
 * Charts are single-series in one hue, server-rendered as HTML; every chart has
 * hover/focus tooltips and a "View as table" twin so no value hides behind a hover.
 */
declare(strict_types=1);

const PERIODS = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'];

function dash_period(): array
{
    $days = (int) input('period', '30');
    if (!isset(PERIODS[$days])) {
        $days = 30;
    }
    $end = time();
    $start = $end - $days * 86400;
    return [
        'days' => $days,
        'label' => PERIODS[$days],
        'from' => date('Y-m-d H:i:s', $start),
        'to' => date('Y-m-d H:i:s', $end),
        'prev_from' => date('Y-m-d H:i:s', $start - $days * 86400),
    ];
}

/** Percentage change, or null when there is nothing to compare with. */
function pct_change(float $now, float $before): ?float
{
    if ($before <= 0) {
        return null;
    }
    return ($now - $before) / $before * 100;
}

/* ------------------------------------------------------------------ chart helpers */
function kpi(string $label, string $value, string $icon, ?float $delta = null, bool $upIsGood = true, string $foot = '', string $href = '', string $extraClass = ''): string
{
    $deltaHtml = '';
    if ($delta !== null) {
        $dir = abs($delta) < 0.5 ? 'flat' : ($delta > 0 ? 'up' : 'down');
        $good = $dir === 'flat' ? 'flat' : ((($dir === 'up') === $upIsGood) ? 'up' : 'down');
        $deltaHtml = '<span class="delta delta--' . $good . '">' . ($dir === 'flat' ? '' : icon($dir === 'up' ? 'trend-up' : 'trend-down'))
            . ($delta > 0 ? '+' : '') . number_format($delta, 0) . '%</span>';
    }
    $tag = $href !== '' ? 'a' : 'div';
    return '<' . $tag . ' class="kpi' . ($extraClass ? ' ' . $extraClass : '') . '"' . ($href !== '' ? ' href="' . e($href) . '"' : '') . '>'
        . '<span class="kpi__label">' . icon($icon) . e($label) . '</span>'
        . '<span class="kpi__value">' . $value . '</span>'
        . (($deltaHtml || $foot) ? '<span class="kpi__foot">' . $deltaHtml . ($foot !== '' ? '<span>' . e($foot) . '</span>' : '') . '</span>' : '')
        . '</' . $tag . '>';
}

/**
 * Horizontal bars, one hue. @param array<int,array{label:string,value:float,display?:string,sub?:string,href?:string}> $rows
 */
function chart_hbars(array $rows, string $caption, string $empty = 'No data for this period yet.'): string
{
    if (!$rows || max(array_column($rows, 'value')) <= 0) {
        return '<div class="empty empty--sm"><p>' . e($empty) . '</p></div>';
    }
    $max = max(array_column($rows, 'value'));
    $html = '<div class="hbars" role="img" aria-label="' . e($caption) . '">';
    $table = '';
    foreach ($rows as $r) {
        $display = $r['display'] ?? number_format($r['value']);
        $pct = $max > 0 ? max(0.6, $r['value'] / $max * 100) : 0;
        $label = e($r['label']);
        if (!empty($r['href'])) {
            $label = '<a href="' . e($r['href']) . '">' . $label . '</a>';
        }
        $html .= '<div class="hbar"' . tip($r['label'], $display . (!empty($r['sub']) ? ' · ' . $r['sub'] : '')) . '>'
            . '<span class="hbar__label"><span>' . $label . '</span></span>'
            . '<span class="hbar__track">' . ($r['value'] > 0 ? '<span class="hbar__fill" style="width:' . number_format($pct, 2, '.', '') . '%"></span>' : '') . '</span>'
            . '<span class="hbar__val">' . e($display) . (!empty($r['sub']) ? ' <small>' . e($r['sub']) . '</small>' : '') . '</span></div>';
        $table .= '<tr><td>' . e($r['label']) . '</td><td>' . e($display) . (!empty($r['sub']) ? ' (' . e($r['sub']) . ')' : '') . '</td></tr>';
    }
    return $html . '</div><details class="viz-table"><summary>View as table</summary><table><thead><tr><th>' . e($caption) . '</th><th>Value</th></tr></thead><tbody>' . $table . '</tbody></table></details>';
}

/** Round an axis maximum up to 1, 2, 2.5 or 5 times a power of ten. */
function nice_max(float $v): float
{
    if ($v <= 0) {
        return 4;
    }
    $mag = 10 ** floor(log10($v));
    foreach ([1, 2, 2.5, 5, 10] as $step) {
        if ($v <= $step * $mag) {
            $n = $step * $mag;
            return $n < 4 ? 4 : $n;
        }
    }
    return 10 * $mag;
}

/**
 * Column chart over time, one hue. Labels only the latest and the highest column.
 * @param array<int,array{label:string,value:float,tip:string}> $points
 */
function chart_columns(array $points, string $caption, string $unit = ''): string
{
    if (!$points || array_sum(array_column($points, 'value')) <= 0) {
        return '<div class="empty empty--sm"><p>Nothing recorded in this window yet.</p></div>';
    }
    $values = array_column($points, 'value');
    $max = nice_max((float) max($values));
    $maxIdx = array_search(max($values), $values, true);
    $lastIdx = count($points) - 1;
    $ticks = '';
    $grid = '';
    for ($i = 0; $i <= 4; $i++) {
        $v = $max / 4 * $i;
        $ticks .= '<span style="bottom:' . ($i * 25) . '%">' . e(compact_number($v)) . '</span>';
        if ($i > 0) {
            $grid .= '<i style="bottom:' . ($i * 25) . '%"></i>';
        }
    }
    $cols = '';
    $xs = '';
    $table = '';
    $every = count($points) > 8 ? 2 : 1;
    foreach ($points as $i => $p) {
        $h = $max > 0 ? $p['value'] / $max * 100 : 0;
        $showLabel = ($i === $lastIdx || $i === $maxIdx) && $p['value'] > 0;
        $cols .= '<div class="col"' . tip($p['tip'], number_format($p['value']) . $unit) . ' style="--h:' . number_format($h, 2, '.', '') . '%">'
            . ($showLabel ? '<span class="col__lab">' . e(compact_number($p['value'])) . '</span>' : '')
            . ($p['value'] > 0 ? '<span class="col__bar" style="height:' . number_format($h, 2, '.', '') . '%"></span>' : '') . '</div>';
        $xs .= '<span>' . (($i % $every === ($lastIdx % $every)) ? e($p['label']) : '') . '</span>';
        $table .= '<tr><td>' . e($p['tip']) . '</td><td>' . e(number_format($p['value'])) . '</td></tr>';
    }
    return '<div class="cols" role="img" aria-label="' . e($caption) . '"><div class="cols__axis">' . $ticks . '</div>'
        . '<div class="cols__plot"><div class="cols__grid">' . $grid . '</div>' . $cols . '</div><div class="cols__x">' . $xs . '</div></div>'
        . '<details class="viz-table"><summary>View as table</summary><table><thead><tr><th>Week</th><th>' . e($caption) . '</th></tr></thead><tbody>' . $table . '</tbody></table></details>';
}

/** Count rows per week for the last N weeks, from a table's datetime column. */
function weekly_counts(string $table, string $column, int $weeks = 12, string $extraWhere = '', array $params = []): array
{
    $monday = strtotime('monday this week');
    $start = strtotime('-' . ($weeks - 1) . ' weeks', $monday);
    $rows = db()->pairs(
        'SELECT DATE(' . $column . ') AS d, COUNT(*) FROM ' . $table . ' WHERE ' . $column . ' >= ?' . ($extraWhere ? ' AND ' . $extraWhere : '') . ' GROUP BY d',
        array_merge([date('Y-m-d 00:00:00', $start)], $params)
    );
    $points = [];
    for ($w = 0; $w < $weeks; $w++) {
        $ws = strtotime('+' . $w . ' weeks', $start);
        $n = 0;
        for ($d = 0; $d < 7; $d++) {
            $n += (int) ($rows[date('Y-m-d', strtotime('+' . $d . ' days', $ws))] ?? 0);
        }
        $points[] = ['label' => date('j M', $ws), 'value' => $n, 'tip' => 'Week of ' . date('j M', $ws)];
    }
    return $points;
}

function period_filter(array $period, string $path): string
{
    $html = '<form class="dash-filter" method="get" action="' . e(admin_url($path)) . '" data-autosubmit><label class="muted" for="period">Showing</label>'
        . '<select class="select select--sm" id="period" name="period" style="width:auto">';
    foreach (PERIODS as $d => $label) {
        $html .= '<option value="' . $d . '"' . selected($period['days'], $d) . '>' . e($label) . '</option>';
    }
    return $html . '</select><span class="muted">Changes compare with the ' . e(strtolower(str_replace('Last ', 'previous ', $period['label']))) . '. Pipeline figures are as of now.</span><noscript><button class="btn btn--quiet btn--sm" type="submit">Apply</button></noscript></form>';
}

/** Lets dashboard SQL limit leads to the viewer's own when they can't see every lead. */
function set_crm_scope_var(): void
{
    db()->run('SET @crm_owner = ?', [user_can('crm.all') ? null : auth_id()]);
}

/* ------------------------------------------------------------------ overview */
function dashboard_overview(): void
{
    $uid = auth_id();
    $tasks = task_links(db()->all(
        'SELECT t.*, u.name AS assignee FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to
         WHERE t.assigned_to = ? AND t.completed_at IS NULL AND (t.due_at IS NULL OR t.due_at <= ?) ORDER BY t.due_at IS NULL, t.due_at LIMIT 8',
        [$uid, date('Y-m-d 23:59:59', strtotime('+2 days'))]
    ));
    $interviews = db()->all(
        "SELECT i.*, c.first_name, c.last_name, j.title AS job_title,
            (SELECT COUNT(*) FROM interview_feedback f WHERE f.interview_id = i.id AND f.user_id = ?) AS my_feedback
         FROM interviews i JOIN interview_panel p ON p.interview_id = i.id JOIN applications a ON a.id = i.application_id
         JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id
         WHERE p.user_id = ? AND i.status = 'scheduled' AND i.scheduled_at >= ? ORDER BY i.scheduled_at LIMIT 6",
        [$uid, $uid, date('Y-m-d 00:00:00')]
    );
    $weekAgo = date('Y-m-d H:i:s', time() - 7 * 86400);
    $twoWeeksAgo = date('Y-m-d H:i:s', time() - 14 * 86400);
    $stats = [];
    if (user_can('ats')) {
        $stats['apps'] = (int) db()->value('SELECT COUNT(*) FROM applications WHERE applied_at >= ?', [$weekAgo]);
        $stats['apps_prev'] = (int) db()->value('SELECT COUNT(*) FROM applications WHERE applied_at >= ? AND applied_at < ?', [$twoWeeksAgo, $weekAgo]);
        $stats['to_review'] = nav_counts()['new_apps'];
        $stats['interviews_week'] = (int) db()->value("SELECT COUNT(*) FROM interviews WHERE status = 'scheduled' AND scheduled_at >= ? AND scheduled_at < ?", [date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+7 days'))]);
        $stats['open_jobs'] = (int) db()->value("SELECT COUNT(*) FROM jobs WHERE status = 'open'");
    }
    if (user_can('crm')) {
        set_crm_scope_var();
        $stats['leads'] = (int) db()->value('SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ?', [$weekAgo]);
        $stats['leads_prev'] = (int) db()->value('SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ? AND created_at < ?', [$twoWeeksAgo, $weekAgo]);
        $stats['pipeline'] = (float) db()->value("SELECT COALESCE(SUM(value), 0) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'open'");
        $stats['follow_overdue'] = (int) db()->value("SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'open' AND next_follow_up < ?", [now()]);
        $stats['uncontacted'] = nav_counts()['new_leads'];
    }
    $recentLeads = user_can('crm') ? db()->all('SELECT l.*, c.name AS contact_name, c.company AS contact_company FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id WHERE (@crm_owner IS NULL OR l.owner_id = @crm_owner) ORDER BY l.created_at DESC LIMIT 5') : [];
    $recentApps = user_can('ats') ? db()->all('SELECT a.id, a.stage_id, a.applied_at, c.first_name, c.last_name, j.title AS job_title FROM applications a JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id ORDER BY a.applied_at DESC LIMIT 5') : [];
    admin_view('dashboard/overview', [
        'title' => 'Dashboard', 'nav' => 'home', 'tasks' => $tasks, 'interviews' => $interviews, 'stats' => $stats,
        'recentLeads' => $recentLeads, 'recentApps' => $recentApps,
    ]);
}

/* ------------------------------------------------------------------ ATS */
function dashboard_ats(): void
{
    $p = dash_period();
    $stages = ats_stages();
    $m = [];
    $m['apps'] = (int) db()->value('SELECT COUNT(*) FROM applications WHERE applied_at >= ?', [$p['from']]);
    $m['apps_prev'] = (int) db()->value('SELECT COUNT(*) FROM applications WHERE applied_at >= ? AND applied_at < ?', [$p['prev_from'], $p['from']]);
    $m['hires'] = (int) db()->value("SELECT COUNT(*) FROM applications WHERE status = 'hired' AND hired_at >= ?", [$p['from']]);
    $m['hires_prev'] = (int) db()->value("SELECT COUNT(*) FROM applications WHERE status = 'hired' AND hired_at >= ? AND hired_at < ?", [$p['prev_from'], $p['from']]);
    $m['interviews'] = (int) db()->value("SELECT COUNT(*) FROM interviews WHERE status <> 'cancelled' AND scheduled_at >= ? AND scheduled_at <= ?", [$p['from'], $p['to']]);
    $m['interviews_prev'] = (int) db()->value("SELECT COUNT(*) FROM interviews WHERE status <> 'cancelled' AND scheduled_at >= ? AND scheduled_at < ?", [$p['prev_from'], $p['from']]);
    $m['active'] = (int) db()->value("SELECT COUNT(*) FROM applications WHERE status = 'active'");
    $m['open_jobs'] = (int) db()->value("SELECT COUNT(*) FROM jobs WHERE status = 'open'");
    $m['openings'] = (int) db()->value("SELECT COALESCE(SUM(openings), 0) FROM jobs WHERE status = 'open'");
    $m['time_to_hire'] = db()->value("SELECT AVG(TIMESTAMPDIFF(HOUR, applied_at, hired_at)) / 24 FROM applications WHERE status = 'hired' AND hired_at >= ?", [$p['from']]);
    $offers = db()->pairs("SELECT offer_status, COUNT(*) FROM applications WHERE offer_status IN ('accepted', 'declined') AND updated_at >= ? GROUP BY offer_status", [$p['from']]);
    $decided = (int) ($offers['accepted'] ?? 0) + (int) ($offers['declined'] ?? 0);
    $m['offer_rate'] = $decided ? (int) ($offers['accepted'] ?? 0) / $decided * 100 : null;
    $m['upcoming'] = (int) db()->value("SELECT COUNT(*) FROM interviews WHERE status = 'scheduled' AND scheduled_at >= ? AND scheduled_at < ?", [now(), date('Y-m-d H:i:s', strtotime('+7 days'))]);

    // current funnel: active applications by stage, in pipeline order
    $byStage = db()->pairs("SELECT stage_id, COUNT(*) FROM applications WHERE status = 'active' GROUP BY stage_id");
    $funnel = [];
    foreach ($stages as $sid => $s) {
        if ($s['kind'] === 'active') {
            $funnel[] = ['label' => $s['name'], 'value' => (float) ($byStage[$sid] ?? 0), 'href' => admin_url('candidates') . '?stage=' . $sid . '&status=active'];
        }
    }
    // how far this period's applicants got
    $reached = db()->pairs('SELECT stage_id, COUNT(*) FROM applications WHERE applied_at >= ? GROUP BY stage_id', [$p['from']]);
    $sources = db()->all('SELECT source, COUNT(*) AS n FROM applications WHERE applied_at >= ? GROUP BY source ORDER BY n DESC', [$p['from']]);
    $sourceRows = array_map(static fn ($r) => ['label' => CANDIDATE_SOURCES[$r['source']] ?? ucfirst((string) $r['source'] ?: 'Unknown'), 'value' => (float) $r['n']], $sources);
    $reasons = db()->all("SELECT COALESCE(NULLIF(rejection_reason, ''), 'No reason given') AS r, COUNT(*) AS n FROM applications WHERE status = 'rejected' AND rejected_at >= ? GROUP BY r ORDER BY n DESC LIMIT 8", [$p['from']]);
    $reasonRows = array_map(static fn ($r) => ['label' => $r['r'], 'value' => (float) $r['n']], $reasons);

    $first = first_stage_id($stages, 'active') ?? 0;
    $jobs = db()->all(
        "SELECT j.id, j.title, j.openings, j.published_at, j.created_at,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.applied_at >= ?) AS period_apps,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'active' AND a.stage_id = ?) AS to_review,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'active') AS active,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'hired') AS hired,
            (SELECT COUNT(*) FROM interviews i JOIN applications a ON a.id = i.application_id WHERE a.job_id = j.id AND i.status = 'scheduled' AND i.scheduled_at >= ?) AS interviews
         FROM jobs j WHERE j.status = 'open' ORDER BY to_review DESC, active DESC LIMIT 12",
        [$p['from'], $first, now()]
    );
    $upcoming = db()->all(
        "SELECT i.*, c.first_name, c.last_name, j.title AS job_title FROM interviews i JOIN applications a ON a.id = i.application_id
         JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id
         WHERE i.status = 'scheduled' AND i.scheduled_at >= ? ORDER BY i.scheduled_at LIMIT 6",
        [now()]
    );
    $stale = db()->all(
        "SELECT a.id, a.stage_id, a.stage_changed_at, c.first_name, c.last_name, j.title AS job_title FROM applications a
         JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id
         WHERE a.status = 'active' AND a.stage_changed_at < ? ORDER BY a.stage_changed_at LIMIT 8",
        [date('Y-m-d H:i:s', time() - 7 * 86400)]
    );
    $awaitingScore = (int) db()->value(
        "SELECT COUNT(*) FROM interviews i JOIN interview_panel p ON p.interview_id = i.id
         WHERE i.status IN ('scheduled', 'completed') AND i.scheduled_at < ? AND NOT EXISTS (SELECT 1 FROM interview_feedback f WHERE f.interview_id = i.id AND f.user_id = p.user_id)",
        [now()]
    );
    admin_view('dashboard/ats', [
        'title' => 'ATS dashboard', 'nav' => 'ats', 'p' => $p, 'm' => $m, 'funnel' => $funnel, 'reached' => $reached,
        'sourceRows' => $sourceRows, 'reasonRows' => $reasonRows, 'weekly' => weekly_counts('applications', 'applied_at'),
        'jobs' => $jobs, 'upcoming' => $upcoming, 'stale' => $stale, 'awaitingScore' => $awaitingScore,
    ]);
}

/* ------------------------------------------------------------------ CRM */
function dashboard_crm(): void
{
    $p = dash_period();
    set_crm_scope_var();
    $stages = lead_stages();
    $cur = (string) setting('default_currency', 'PKR');
    $m = [];
    $m['leads'] = (int) db()->value('SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ?', [$p['from']]);
    $m['leads_prev'] = (int) db()->value('SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ? AND created_at < ?', [$p['prev_from'], $p['from']]);
    $won = db()->one("SELECT COUNT(*) AS n, COALESCE(SUM(value), 0) AS v FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'won' AND won_at >= ?", [$p['from']]);
    $wonPrev = db()->one("SELECT COUNT(*) AS n, COALESCE(SUM(value), 0) AS v FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'won' AND won_at >= ? AND won_at < ?", [$p['prev_from'], $p['from']]);
    $lost = (int) db()->value("SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'lost' AND lost_at >= ?", [$p['from']]);
    $lostPrev = (int) db()->value("SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'lost' AND lost_at >= ? AND lost_at < ?", [$p['prev_from'], $p['from']]);
    $m['won_n'] = (int) $won['n'];
    $m['won_v'] = (float) $won['v'];
    $m['won_v_prev'] = (float) $wonPrev['v'];
    $m['win_rate'] = ($m['won_n'] + $lost) > 0 ? $m['won_n'] / ($m['won_n'] + $lost) * 100 : null;
    $prevClosed = (int) $wonPrev['n'] + $lostPrev;
    $m['win_rate_prev'] = $prevClosed > 0 ? (int) $wonPrev['n'] / $prevClosed * 100 : null;
    $m['avg_deal'] = $m['won_n'] ? $m['won_v'] / $m['won_n'] : null;
    $open = db()->all("SELECT id, stage_id, value FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'open'");
    $m['open_n'] = count($open);
    $m['open_v'] = array_sum(array_map(static fn ($l) => (float) $l['value'], $open));
    $m['weighted'] = array_sum(array_map('weighted_value', $open));
    $m['overdue'] = (int) db()->value("SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'open' AND next_follow_up < ?", [now()]);
    $m['no_follow'] = (int) db()->value("SELECT COUNT(*) FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'open' AND next_follow_up IS NULL");
    $m['cycle'] = db()->value("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, won_at)) / 24 FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'won' AND won_at >= ?", [$p['from']]);

    $byStage = [];
    foreach ($open as $l) {
        $sid = (int) $l['stage_id'];
        $byStage[$sid]['n'] = ($byStage[$sid]['n'] ?? 0) + 1;
        $byStage[$sid]['v'] = ($byStage[$sid]['v'] ?? 0) + (float) $l['value'];
    }
    $pipeRows = [];
    foreach ($stages as $sid => $s) {
        if ($s['kind'] === 'open') {
            $v = (float) ($byStage[$sid]['v'] ?? 0);
            $n = (int) ($byStage[$sid]['n'] ?? 0);
            $pipeRows[] = ['label' => $s['name'], 'value' => $v, 'display' => compact_money($v, $cur), 'sub' => plural($n, 'deal'), 'href' => admin_url('leads') . '?status=open&stage=' . $sid];
        }
    }
    $countRows = [];
    foreach ($stages as $sid => $s) {
        if ($s['kind'] === 'open') {
            $countRows[] = ['label' => $s['name'], 'value' => (float) ($byStage[$sid]['n'] ?? 0)];
        }
    }
    $sourceRows = array_map(static fn ($r) => ['label' => LEAD_SOURCES[$r['source']] ?? ucfirst((string) $r['source']), 'value' => (float) $r['n']],
        db()->all('SELECT source, COUNT(*) AS n FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ? GROUP BY source ORDER BY n DESC', [$p['from']]));
    $serviceRows = array_map(static fn ($r) => ['label' => $r['s'], 'value' => (float) $r['n'], 'sub' => (float) $r['v'] > 0 ? compact_money($r['v'], $cur) . ' won' : ''],
        db()->all("SELECT COALESCE(NULLIF(service, ''), 'Not specified') AS s, COUNT(*) AS n, SUM(CASE WHEN status = 'won' THEN value ELSE 0 END) AS v FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND created_at >= ? GROUP BY s ORDER BY n DESC LIMIT 8", [$p['from']]));
    $topicRows = array_map(static fn ($r) => ['label' => $r['topic'], 'value' => (float) $r['n']],
        db()->all("SELECT topic, COUNT(*) AS n FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND topic IS NOT NULL AND topic <> '' AND created_at >= ? GROUP BY topic ORDER BY n DESC LIMIT 8", [$p['from']]));
    $lostRows = array_map(static fn ($r) => ['label' => $r['r'], 'value' => (float) $r['n']],
        db()->all("SELECT COALESCE(NULLIF(lost_reason, ''), 'No reason given') AS r, COUNT(*) AS n FROM leads WHERE (@crm_owner IS NULL OR owner_id = @crm_owner) AND status = 'lost' AND lost_at >= ? GROUP BY r ORDER BY n DESC LIMIT 8", [$p['from']]));
    $owners = db()->all(
        "SELECT u.id, u.name,
            (SELECT COUNT(*) FROM leads l WHERE l.owner_id = u.id AND l.status = 'open') AS open_n,
            (SELECT COALESCE(SUM(l.value), 0) FROM leads l WHERE l.owner_id = u.id AND l.status = 'open') AS open_v,
            (SELECT COUNT(*) FROM leads l WHERE l.owner_id = u.id AND l.status = 'won' AND l.won_at >= ?) AS won_n,
            (SELECT COALESCE(SUM(l.value), 0) FROM leads l WHERE l.owner_id = u.id AND l.status = 'won' AND l.won_at >= ?) AS won_v
         FROM users u WHERE u.is_active = 1 AND (@crm_owner IS NULL OR u.id = @crm_owner) ORDER BY won_v DESC, open_v DESC",
        [$p['from'], $p['from']]
    );
    $owners = array_values(array_filter($owners, static fn ($o) => (int) $o['open_n'] + (int) $o['won_n'] > 0));
    $followUps = db()->all(
        "SELECT l.*, c.name AS contact_name, c.company AS contact_company FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id
         WHERE (@crm_owner IS NULL OR l.owner_id = @crm_owner) AND l.status = 'open' AND l.next_follow_up <= ? ORDER BY l.next_follow_up LIMIT 8",
        [date('Y-m-d 23:59:59')]
    );
    $topDeals = db()->all(
        "SELECT l.*, c.name AS contact_name, c.company AS contact_company FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id
         WHERE (@crm_owner IS NULL OR l.owner_id = @crm_owner) AND l.status = 'open' AND l.value IS NOT NULL ORDER BY l.value DESC LIMIT 6"
    );
    admin_view('dashboard/crm', [
        'title' => 'CRM dashboard', 'nav' => 'crm', 'p' => $p, 'm' => $m, 'cur' => $cur, 'pipeRows' => $pipeRows, 'countRows' => $countRows,
        'sourceRows' => $sourceRows, 'serviceRows' => $serviceRows, 'topicRows' => $topicRows, 'lostRows' => $lostRows, 'owners' => $owners,
        'followUps' => $followUps, 'topDeals' => $topDeals, 'weekly' => weekly_counts('leads', 'created_at'),
    ]);
}
