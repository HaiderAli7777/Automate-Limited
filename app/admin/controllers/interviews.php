<?php
/* Interviews: scheduling, calendar invitations, the agenda and month views, and scorecards. */
declare(strict_types=1);

function interview_row(int $id): array
{
    $iv = db()->one(
        'SELECT i.*, a.candidate_id, a.job_id, c.first_name, c.last_name, c.email, c.phone, j.title AS job_title
         FROM interviews i JOIN applications a ON a.id = i.application_id JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id WHERE i.id = ?',
        [$id]
    ) ?? abort(404);
    $iv['panel'] = db()->all('SELECT u.id, u.name, u.email FROM interview_panel p JOIN users u ON u.id = p.user_id WHERE p.interview_id = ? ORDER BY u.name', [$id]);
    return $iv;
}

function on_panel(array $iv): bool
{
    return in_array(auth_id(), array_map('intval', array_column($iv['panel'], 'id')), true);
}

function interviews_index(): void
{
    $view = in_array(input('view'), ['agenda', 'month', 'past'], true) ? input('view') : 'agenda';
    $mine = !user_can('ats') || input('who') === 'mine';
    $join = 'FROM interviews i JOIN applications a ON a.id = i.application_id JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id';
    $where = [];
    $params = [];
    if ($mine) {
        $where[] = 'EXISTS (SELECT 1 FROM interview_panel p WHERE p.interview_id = i.id AND p.user_id = ?)';
        $params[] = auth_id();
    }
    $month = null;
    if ($view === 'month') {
        $month = preg_match('/^\d{4}-\d{2}$/', input('month')) ? input('month') : date('Y-m');
        $start = strtotime($month . '-01');
        $gridStart = strtotime('-' . ((int) date('N', $start) - 1) . ' days', $start);
        $gridEnd = strtotime('+42 days', $gridStart);
        $where[] = 'i.scheduled_at >= ? AND i.scheduled_at < ?';
        array_push($params, date('Y-m-d 00:00:00', $gridStart), date('Y-m-d 00:00:00', $gridEnd));
        $order = 'i.scheduled_at';
    } elseif ($view === 'past') {
        $where[] = 'i.scheduled_at < ?';
        $params[] = date('Y-m-d 00:00:00');
        $order = 'i.scheduled_at DESC';
    } else {
        $where[] = 'i.scheduled_at >= ?';
        $params[] = date('Y-m-d 00:00:00');
        $order = 'i.scheduled_at';
    }
    $rows = db()->all(
        'SELECT i.*, c.first_name, c.last_name, j.title AS job_title,
            (SELECT GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR \', \') FROM interview_panel p JOIN users u ON u.id = p.user_id WHERE p.interview_id = i.id) AS panel_names,
            (SELECT COUNT(*) FROM interview_panel p WHERE p.interview_id = i.id) AS panel_count,
            (SELECT COUNT(*) FROM interview_feedback f WHERE f.interview_id = i.id) AS feedback_count,
            (SELECT COUNT(*) FROM interview_feedback f WHERE f.interview_id = i.id AND f.user_id = ?) AS my_feedback
         ' . $join . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order . ' LIMIT 300',
        array_merge([auth_id()], $params)
    );
    $pending = db()->all(
        "SELECT i.id, i.title, i.scheduled_at, c.first_name, c.last_name FROM interviews i JOIN interview_panel p ON p.interview_id = i.id
         JOIN applications a ON a.id = i.application_id JOIN candidates c ON c.id = a.candidate_id
         WHERE p.user_id = ? AND i.status IN ('scheduled', 'completed') AND i.scheduled_at < ?
           AND NOT EXISTS (SELECT 1 FROM interview_feedback f WHERE f.interview_id = i.id AND f.user_id = p.user_id)
         ORDER BY i.scheduled_at DESC LIMIT 10",
        [auth_id(), now()]
    );
    admin_view('interviews/index', ['title' => $mine && !user_can('ats') ? 'My interviews' : 'Interviews', 'nav' => 'interviews', 'rows' => $rows, 'view' => $view, 'mine' => $mine, 'month' => $month, 'pending' => $pending]);
}

function interviews_form(int $id = 0): void
{
    if ($id) {
        $iv = interview_row($id);
        $appId = (int) $iv['application_id'];
    } else {
        $iv = null;
        $appId = input_int('application');
    }
    $app = $appId ? application_full($appId) : null;
    if (!$app) {
        flash('info', 'Open a candidate\'s application and choose Schedule interview.');
        redirect(admin_url('pipeline'));
    }
    $upcoming = db()->all(
        "SELECT i.scheduled_at, i.duration_minutes, u.name FROM interviews i JOIN interview_panel p ON p.interview_id = i.id JOIN users u ON u.id = p.user_id
         WHERE i.status = 'scheduled' AND i.scheduled_at >= ? AND i.scheduled_at < ?" . ($id ? ' AND i.id <> ' . $id : '') . ' ORDER BY i.scheduled_at',
        [date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+14 days'))]
    );
    admin_view('interviews/form', ['title' => $iv ? 'Edit interview' : 'Schedule an interview', 'nav' => 'interviews', 'iv' => $iv, 'app' => $app, 'upcoming' => $upcoming]);
}

function interviews_save(int $id = 0): void
{
    $existing = $id ? interview_row($id) : null;
    $appId = $existing ? (int) $existing['application_id'] : input_int('application_id');
    $app = application_full($appId) ?? abort(404);
    $errors = [];
    $type = isset(INTERVIEW_TYPES[input('itype')]) ? input('itype') : 'video';
    $when = parse_dt(input('date') . ' ' . input('time'));
    if (input('date') === '' || input('time') === '' || $when === null) {
        $errors['date'] = 'Choose a date and a start time.';
    }
    $duration = max(10, min(480, input_int('duration_minutes', 45)));
    $meeting = input('meeting_url');
    if ($meeting !== '' && !valid_url(normalise_url($meeting))) {
        $errors['meeting_url'] = 'Enter a full link, e.g. https://meet.google.com/...';
    }
    $panel = array_values(array_unique(array_filter(array_map('intval', input_array('panel')))));
    $validIds = array_map('intval', array_column(active_users(), 'id'));
    $panel = array_values(array_intersect($panel, $validIds));
    if (!$panel) {
        $errors['panel'] = 'Choose at least one interviewer.';
    }
    if ($errors) {
        remember_input($errors);
        redirect($id ? admin_url('interviews/' . $id . '/edit') : admin_url('interviews/new') . '?application=' . $appId);
    }

    $title = input('title') ?: INTERVIEW_TYPES[$type] . ' interview';
    $data = [
        'title' => mb_substr($title, 0, 160),
        'itype' => $type,
        'scheduled_at' => $when,
        'duration_minutes' => $duration,
        'location' => nullable(mb_substr(input('location'), 0, 255)),
        'meeting_url' => $meeting !== '' ? mb_substr(normalise_url($meeting), 0, 500) : null,
        'notes' => nullable((string) ($_POST['notes'] ?? '')),
        'updated_at' => now(),
    ];
    $changedTime = $existing && ($existing['scheduled_at'] !== $when || (int) $existing['duration_minutes'] !== $duration
        || (string) $existing['location'] !== (string) $data['location'] || (string) $existing['meeting_url'] !== (string) $data['meeting_url']);

    $id = db()->tx(static function (Db $db) use ($id, $existing, $data, $appId, $panel, $changedTime): int {
        if ($existing) {
            if ($changedTime) {
                $data['sequence'] = (int) $existing['sequence'] + 1;
                if ($existing['status'] !== 'scheduled') {
                    $data['status'] = 'scheduled';
                }
            }
            $db->update('interviews', $data, 'id = ?', [$id]);
            $db->delete('interview_panel', 'interview_id = ?', [$id]);
        } else {
            $id = $db->insert('interviews', $data + ['application_id' => $appId, 'status' => 'scheduled', 'created_by' => auth_id(), 'created_at' => now()]);
        }
        foreach ($panel as $uid) {
            $db->insert('interview_panel', ['interview_id' => $id, 'user_id' => $uid]);
        }
        return $id;
    });

    $iv = interview_row($id);
    $when12 = fmt_day($iv['scheduled_at']) . ' at ' . fmt_time($iv['scheduled_at']);
    log_activity('application', $appId, 'interview', ($existing ? ($changedTime ? 'Interview rescheduled: ' : 'Interview updated: ') : 'Interview scheduled: ') . $iv['title'], $when12 . ' with ' . implode(', ', array_column($iv['panel'], 'name')));

    notify(array_column($iv['panel'], 'id'), ($existing ? 'Interview changed: ' : 'You\'re on an interview panel: ') . candidate_name($app), admin_url('interviews/' . $id), $when12 . ', ' . $app['job_title'], 'calendar-dots');
    $notes = [];
    $ics = [['name' => 'interview.ics', 'content' => interview_ics($iv), 'mime' => 'text/calendar; charset=utf-8; method=PUBLISH']];
    if (input('notify_candidate') === '1' && (!$existing || $changedTime)) {
        [$ok, $subject, $body] = send_template($existing ? 'interview_update' : 'interview_invite', (string) $app['email'], candidate_vars($app) + interview_vars($iv), [
            'to_name' => candidate_name($app), 'reply_to' => (string) auth_user()['email'], 'attachments' => $ics,
        ]);
        if ($ok) {
            log_activity('application', $appId, 'email', 'Email sent: ' . $subject, $body);
            $notes[] = 'the candidate was sent a calendar invitation';
        } else {
            $notes[] = 'the email to the candidate failed (' . Mailer::$lastError . ')';
        }
    }
    if (input('notify_panel') === '1') {
        $link = site_origin() . admin_url('interviews/' . $id);
        foreach ($iv['panel'] as $u) {
            Mailer::send((string) $u['email'], ($existing ? 'Updated: ' : 'Interview: ') . candidate_name($app) . ' for ' . $app['job_title'] . ', ' . $when12,
                "You're on the panel for this interview.\n\nCandidate: " . candidate_name($app) . "\nRole: " . $app['job_title'] . "\nWhen: " . $when12 . ' (' . (int) $iv['duration_minutes'] . " minutes)\n"
                . ($iv['meeting_url'] ? 'Link: ' . $iv['meeting_url'] . "\n" : '') . ($iv['location'] ? 'Where: ' . $iv['location'] . "\n" : '')
                . ($iv['notes'] ? "\nNotes for the panel:\n" . $iv['notes'] . "\n" : '')
                . "\nCV, details and your scorecard: " . $link, ['to_name' => $u['name'], 'attachments' => $ics]);
        }
        $notes[] = 'the panel was notified';
    }
    flash('success', ($existing ? 'Interview saved' : 'Interview scheduled') . ($notes ? ', and ' . implode(' and ', $notes) : '') . '.');
    redirect(admin_url('interviews/' . $id));
}

function interview_ics(array $iv): string
{
    $start = ts($iv['scheduled_at']) ?? time();
    $desc = 'Interview with ' . candidate_name($iv) . ' for ' . $iv['job_title'] . '.';
    if ($iv['meeting_url']) {
        $desc .= "\nJoin: " . $iv['meeting_url'];
    }
    return build_ics([
        'uid' => 'interview-' . $iv['id'] . '@' . (parse_url(site_origin(), PHP_URL_HOST) ?: 'automateltd.com'),
        'start' => $start,
        'end' => $start + (int) $iv['duration_minutes'] * 60,
        'summary' => $iv['title'] . ': ' . candidate_name($iv) . ' (' . $iv['job_title'] . ')',
        'description' => $desc,
        'location' => $iv['meeting_url'] ?: (string) $iv['location'],
        'sequence' => (int) $iv['sequence'],
        'cancelled' => $iv['status'] === 'cancelled',
    ]);
}

function interviews_show(int $id): void
{
    $iv = interview_row($id);
    if (!user_can('ats') && !on_panel($iv)) {
        abort(403, 'You can only open interviews you are on the panel for.');
    }
    $feedback = db()->all('SELECT f.*, u.name AS user_name FROM interview_feedback f JOIN users u ON u.id = f.user_id WHERE f.interview_id = ? ORDER BY f.created_at', [$id]);
    $mine = null;
    foreach ($feedback as $f) {
        if ((int) $f['user_id'] === auth_id()) {
            $mine = $f;
        }
    }
    // panel members see the others' scorecards only after writing their own, to keep scores independent
    $canSeeAll = user_can('ats') && !on_panel($iv) || $mine !== null || user_can('audit.view');
    $resume = db()->one('SELECT f.* FROM applications a JOIN files f ON f.id = a.resume_file_id WHERE a.id = ?', [(int) $iv['application_id']]);
    admin_view('interviews/show', ['title' => $iv['title'], 'nav' => 'interviews', 'iv' => $iv, 'feedback' => $feedback, 'mine' => $mine, 'canSeeAll' => $canSeeAll, 'onPanel' => on_panel($iv), 'resume' => $resume]);
}

function interviews_status(int $id): void
{
    $iv = interview_row($id);
    $status = input('status');
    if (!isset(INTERVIEW_STATUSES[$status])) {
        abort(400);
    }
    db()->update('interviews', ['status' => $status, 'updated_at' => now(), 'sequence' => (int) $iv['sequence'] + ($status === 'cancelled' ? 1 : 0)], 'id = ?', [$id]);
    log_activity('application', (int) $iv['application_id'], 'interview', 'Interview ' . strtolower(INTERVIEW_STATUSES[$status]) . ': ' . $iv['title']);
    $msg = 'Marked as ' . strtolower(INTERVIEW_STATUSES[$status]) . '.';
    if ($status === 'cancelled' && input('notify_candidate') === '1') {
        $iv = interview_row($id);
        $body = 'Hi ' . $iv['first_name'] . ",\n\nWe need to cancel your interview for the " . $iv['job_title'] . ' role on ' . fmt_day($iv['scheduled_at']) . ' at ' . fmt_time($iv['scheduled_at'])
            . ". We're sorry for the change and will be in touch about next steps.\n\nKind regards,\n" . auth_user()['name'] . "\n" . company_name();
        $ok = Mailer::send((string) $iv['email'], 'Interview cancelled: ' . $iv['job_title'], $body, [
            'to_name' => candidate_name($iv), 'reply_to' => (string) auth_user()['email'],
            'attachments' => [['name' => 'interview.ics', 'content' => interview_ics($iv), 'mime' => 'text/calendar; charset=utf-8; method=PUBLISH']],
        ]);
        if ($ok) {
            log_activity('application', (int) $iv['application_id'], 'email', 'Email sent: Interview cancelled', $body);
            $msg .= ' The candidate was told.';
        }
    }
    flash('success', $msg);
    redirect(admin_url('interviews/' . $id));
}

function interviews_feedback(int $id): void
{
    $iv = interview_row($id);
    if (!on_panel($iv) && !user_can('ats')) {
        abort(403);
    }
    $rating = input_int('rating');
    $rec = input('recommendation');
    if ($rating < 1 || $rating > 5 || !isset(RECOMMENDATIONS[$rec])) {
        flash('error', 'Choose a score from 1 to 5 and a recommendation.');
        redirect(admin_url('interviews/' . $id) . '#feedback');
    }
    $data = [
        'rating' => $rating,
        'recommendation' => $rec,
        'strengths' => nullable((string) ($_POST['strengths'] ?? '')),
        'concerns' => nullable((string) ($_POST['concerns'] ?? '')),
        'notes' => nullable((string) ($_POST['notes'] ?? '')),
        'updated_at' => now(),
    ];
    $existing = db()->one('SELECT id FROM interview_feedback WHERE interview_id = ? AND user_id = ?', [$id, auth_id()]);
    if ($existing) {
        db()->update('interview_feedback', $data, 'id = ?', [(int) $existing['id']]);
    } else {
        db()->insert('interview_feedback', $data + ['interview_id' => $id, 'user_id' => auth_id(), 'created_at' => now()]);
        log_activity('application', (int) $iv['application_id'], 'feedback', 'Scorecard: ' . RECOMMENDATIONS[$rec] . ', ' . $rating . '/5', '', ['interview' => $iv['title']]);
        $owner = (int) db()->value('SELECT owner_id FROM applications WHERE id = ?', [(int) $iv['application_id']]);
        notify([$owner, (int) $iv['created_by']], auth_user()['name'] . ' scored ' . candidate_name($iv) . ': ' . RECOMMENDATIONS[$rec], admin_url('interviews/' . $id), $rating . '/5 for ' . $iv['job_title'], 'star');
    }
    if ($iv['status'] === 'scheduled' && ts($iv['scheduled_at']) < time()) {
        db()->update('interviews', ['status' => 'completed', 'updated_at' => now()], 'id = ?', [$id]);
    }
    flash('success', 'Scorecard saved.');
    redirect(admin_url('interviews/' . $id));
}

function interviews_ics(int $id): void
{
    $iv = interview_row($id);
    if (!user_can('ats') && !on_panel($iv)) {
        abort(403);
    }
    header_remove('Content-Security-Policy');
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="interview-' . $id . '.ics"');
    echo interview_ics($iv);
    exit;
}
