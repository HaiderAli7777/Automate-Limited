<?php
/* One candidate's application for one job: the recruiter's workspace. Also the hiring pipeline board. */
declare(strict_types=1);

function applications_show(int $id): void
{
    $app = application_full($id) ?? abort(404);
    if (!can_view_application($id)) {
        abort(403, 'You can only open candidates you are interviewing.');
    }
    $full = user_can('ats');
    $answers = db()->all('SELECT * FROM application_answers WHERE application_id = ? ORDER BY id', [$id]);
    $resume = $app['resume_file_id'] ? db()->one('SELECT * FROM files WHERE id = ?', [(int) $app['resume_file_id']]) : null;
    $files = db()->all("SELECT * FROM files WHERE entity_type = 'candidate' AND entity_id = ? ORDER BY created_at DESC", [(int) $app['candidate_id']]);
    $interviews = db()->all('SELECT * FROM interviews WHERE application_id = ? ORDER BY scheduled_at DESC', [$id]);
    foreach ($interviews as &$iv) {
        $iv['panel'] = db()->all('SELECT u.id, u.name FROM interview_panel p JOIN users u ON u.id = p.user_id WHERE p.interview_id = ? ORDER BY u.name', [(int) $iv['id']]);
        $iv['feedback'] = db()->all('SELECT f.*, u.name AS user_name FROM interview_feedback f JOIN users u ON u.id = f.user_id WHERE f.interview_id = ? ORDER BY f.created_at', [(int) $iv['id']]);
    }
    unset($iv);
    $timeline = activities_for([['application', [$id]], ['candidate', [(int) $app['candidate_id']]]]);
    $others = db()->all('SELECT a.id, a.stage_id, j.title FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.candidate_id = ? AND a.id <> ? ORDER BY a.applied_at DESC', [(int) $app['candidate_id'], $id]);
    $templates = db()->all("SELECT tkey, name FROM email_templates WHERE module = 'ats' ORDER BY name");
    admin_view('applications/show', [
        'title' => candidate_name($app) . ', ' . $app['job_title'],
        'nav' => $full ? 'pipeline' : 'interviews',
        'app' => $app,
        'full' => $full,
        'edit' => user_can('ats.manage'),
        'answers' => $answers,
        'resume' => $resume,
        'files' => $files,
        'interviews' => $interviews,
        'timeline' => $timeline,
        'others' => $others,
        'tasks' => $full ? open_tasks_for('application', $id) : [],
        'templates' => $templates,
        'summary' => feedback_summary($id),
    ]);
}

function applications_stage(int $id): void
{
    $app = application_full($id) ?? abort(404);
    $stageId = input_int('stage_id');
    $reason = mb_substr(input('reason'), 0, 120);
    $to = application_move($id, $stageId, $reason, ['revisit_on' => input('revisit_on'), 'note' => (string) ($_POST['note'] ?? '')]);
    $message = '';
    if ($to) {
        $message = candidate_name($app) . ' moved to ' . $to['name'] . '.';
        $tpl = ['rejected' => 'application_rejected', 'pool' => 'application_on_hold'][$to['kind']] ?? null;
        if ($tpl && input('notify') === '1') {
            [$ok, $subject, $body] = send_template($tpl, (string) $app['email'], candidate_vars($app), [
                'to_name' => candidate_name($app),
                'reply_to' => (string) auth_user()['email'],
            ]);
            if ($ok) {
                log_activity('application', $id, 'email', 'Email sent: ' . $subject, $body);
                $message .= ' The candidate was emailed.';
            } else {
                $message .= ' The email to the candidate failed: ' . Mailer::$lastError;
            }
        }
    }
    if (is_ajax()) {
        json_out(['ok' => true, 'message' => $message]);
    }
    if ($message) {
        flash('success', $message);
    }
    back(admin_url('applications/' . $id));
}

function applications_note(int $id): void
{
    application_full($id) ?? abort(404);
    if (!can_view_application($id)) {
        abort(403);
    }
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    $kind = isset(ACTIVITY_KINDS[input('kind')]) ? input('kind') : 'note';
    if ($body === '') {
        flash('error', 'Write something first.');
        back(admin_url('applications/' . $id));
    }
    $titles = ['note' => 'Note', 'call' => 'Call logged', 'meeting' => 'Meeting logged', 'email' => 'Email logged', 'whatsapp' => 'WhatsApp logged'];
    log_activity('application', $id, $kind, $titles[$kind], $body);
    db()->update('applications', ['updated_at' => now()], 'id = ?', [$id]);
    flash('success', 'Saved to the timeline.');
    redirect(admin_url('applications/' . $id) . '#timeline');
}

function applications_email_preview(int $id): void
{
    $app = application_full($id) ?? abort(404);
    $tpl = email_template(input('template'));
    if (!$tpl || $tpl['module'] !== 'ats') {
        json_out(['ok' => false, 'error' => 'Template not found.'], 404);
    }
    $vars = candidate_vars($app);
    $next = db()->one("SELECT * FROM interviews WHERE application_id = ? AND status = 'scheduled' ORDER BY scheduled_at LIMIT 1", [$id]);
    if ($next) {
        $vars += interview_vars($next);
    }
    json_out(['ok' => true, 'subject' => fill_template($tpl['subject'], $vars), 'body' => fill_template($tpl['body'], $vars)]);
}

function applications_email(int $id): void
{
    $app = application_full($id) ?? abort(404);
    $subject = input('subject');
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    if ($subject === '' || $body === '') {
        flash('error', 'Add a subject and a message.');
        back(admin_url('applications/' . $id));
    }
    $ok = Mailer::send((string) $app['email'], $subject, $body, ['to_name' => candidate_name($app), 'reply_to' => (string) auth_user()['email']]);
    if ($ok) {
        log_activity('application', $id, 'email', 'Email sent: ' . $subject, $body);
        flash('success', 'Email sent to ' . $app['email'] . '.');
    } else {
        flash('error', 'The email didn\'t send: ' . Mailer::$lastError);
    }
    redirect(admin_url('applications/' . $id) . '#timeline');
}

function applications_offer(int $id): void
{
    $app = application_full($id) ?? abort(404);
    $status = array_key_exists(input('offer_status'), OFFER_STATUSES) ? input('offer_status') : '';
    $data = [
        'offer_salary' => nullable(mb_substr(input('offer_salary'), 0, 80)),
        'offer_start_date' => parse_dt(input('offer_start_date'), false),
        'offer_status' => nullable($status),
        'offer_notes' => nullable((string) ($_POST['offer_notes'] ?? '')),
        'updated_at' => now(),
    ];
    db()->update('applications', $data, 'id = ?', [$id]);
    if ($status !== (string) $app['offer_status']) {
        log_activity('application', $id, 'offer', 'Offer: ' . OFFER_STATUSES[$status], trim(implode("\n", array_filter([
            $data['offer_salary'] ? 'Salary: ' . $data['offer_salary'] : null,
            $data['offer_start_date'] ? 'Start: ' . fmt_date($data['offer_start_date']) : null,
        ]))));
    }
    // an accepted offer usually means the hire is done
    if ($status === 'accepted' && $app['status'] !== 'hired' && input('mark_hired') === '1') {
        $hired = first_stage_id(ats_stages(), 'hired');
        if ($hired) {
            application_move($id, $hired);
        }
    }
    flash('success', 'Offer saved.');
    redirect(admin_url('applications/' . $id));
}

function applications_owner(int $id): void
{
    application_full($id) ?? abort(404);
    $app = application_full($id);
    $owner = input_int('owner_id') ?: null;
    db()->update('applications', ['owner_id' => $owner, 'updated_at' => now()], 'id = ?', [$id]);
    if ($owner && $app) {
        notify([$owner], 'Candidate assigned to you: ' . candidate_name($app), admin_url('applications/' . $id), $app['job_title'], 'user-plus');
    }
    log_activity('application', $id, 'updated', $owner ? 'Owner set to ' . user_name($owner) : 'Owner removed');
    flash('success', 'Owner updated.');
    redirect(admin_url('applications/' . $id));
}

/** Move several applications at once from the candidates list. */
function applications_bulk(): void
{
    $ids = array_values(array_unique(array_filter(array_map('intval', input_array('ids')))));
    $stageId = input_int('stage_id');
    $stage = ats_stages()[$stageId] ?? null;
    if (!$ids || !$stage) {
        flash('error', 'Tick at least one candidate and choose a stage.');
        back(admin_url('candidates'));
    }
    $reason = input('reason');
    $done = 0;
    $emailed = 0;
    $tpl = ['rejected' => 'application_rejected', 'pool' => 'application_on_hold'][$stage['kind']] ?? null;
    foreach ($ids as $id) {
        $app = application_full($id);
        if (!$app || !application_move($id, $stageId, $reason, ['revisit_on' => input('revisit_on')])) {
            continue;
        }
        $done++;
        if ($tpl && input('notify') === '1') {
            [$ok, $subject, $body] = send_template($tpl, (string) $app['email'], candidate_vars($app), ['to_name' => candidate_name($app), 'reply_to' => (string) auth_user()['email']]);
            if ($ok) {
                log_activity('application', $id, 'email', 'Email sent: ' . $subject, $body);
                $emailed++;
            }
        }
    }
    flash($done ? 'success' : 'info', $done ? plural($done, 'candidate') . ' moved to ' . $stage['name'] . ($emailed ? ', ' . $emailed . ' emailed' : '') . '.' : 'Nothing changed.');
    back(admin_url('candidates'));
}

/* ------------------------------------------------------------------ hiring pipeline board */
function pipeline_board(): void
{
    $jobId = input_int('job');
    $q = input('q');
    $jobs = db()->all("SELECT id, title, status FROM jobs WHERE status IN ('open', 'paused') ORDER BY title");
    $where = ["j.status IN ('open', 'paused')"];
    $params = [now()];
    if ($jobId) {
        $where = ['a.job_id = ?'];
        $params[] = $jobId;
    }
    if ($q !== '') {
        $where[] = "(CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.email LIKE ?)";
        array_push($params, "%$q%", "%$q%");
    }
    // finished applications only stay on the board for 30 days; the talent pool stays
    $where[] = "(a.status IN ('active', 'pool') OR a.stage_changed_at >= ?)";
    $params[] = date('Y-m-d H:i:s', time() - 30 * 86400);
    $apps = db()->all(
        "SELECT a.*, c.first_name, c.last_name, c.email, c.current_title, j.title AS job_title,
            (SELECT AVG(f.rating) FROM interview_feedback f JOIN interviews i ON i.id = f.interview_id WHERE i.application_id = a.id) AS avg_rating,
            (SELECT MIN(i.scheduled_at) FROM interviews i WHERE i.application_id = a.id AND i.status = 'scheduled' AND i.scheduled_at >= ?) AS next_interview
         FROM applications a JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id
         WHERE " . implode(' AND ', $where) . ' ORDER BY a.stage_changed_at DESC LIMIT 600',
        $params
    );
    admin_view('applications/pipeline', ['title' => 'Hiring pipeline', 'nav' => 'pipeline', 'apps' => $apps, 'jobs' => $jobs, 'jobId' => $jobId, 'q' => $q, 'wide' => true]);
}

/* ------------------------------------------------------------------ talent pool */
function talent_pool(): void
{
    $where = ["a.status = 'pool'"];
    $params = [];
    if ($jobId = input_int('job')) {
        $where[] = 'a.job_id = ?';
        $params[] = $jobId;
    }
    if (in_array(input('reason'), POOL_REASONS, true)) {
        $where[] = 'a.pool_reason = ?';
        $params[] = input('reason');
    }
    if (input('due') === '1') {
        $where[] = 'a.revisit_on IS NOT NULL AND a.revisit_on <= ?';
        $params[] = today();
    }
    if (($q = input('q')) !== '') {
        $where[] = "(CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.email LIKE ? OR c.current_title LIKE ?)";
        array_push($params, "%$q%", "%$q%", "%$q%");
    }
    $rows = db()->all(
        'SELECT a.*, c.first_name, c.last_name, c.email, c.current_title, c.current_company, c.experience_years, c.expected_salary, c.location, j.title AS job_title, j.status AS job_status,
            (SELECT AVG(f.rating) FROM interview_feedback f JOIN interviews i ON i.id = f.interview_id WHERE i.application_id = a.id) AS avg_rating
         FROM applications a JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY a.revisit_on IS NULL, a.revisit_on, a.pooled_at DESC LIMIT 500',
        $params
    );
    $jobs = db()->all("SELECT j.id, j.title, COUNT(a.id) AS n FROM jobs j JOIN applications a ON a.job_id = j.id AND a.status = 'pool' GROUP BY j.id, j.title ORDER BY j.title");
    $due = (int) db()->value("SELECT COUNT(*) FROM applications WHERE status = 'pool' AND revisit_on IS NOT NULL AND revisit_on <= ?", [today()]);
    admin_view('applications/pool', ['title' => 'Talent pool', 'nav' => 'pool', 'rows' => $rows, 'jobs' => $jobs, 'due' => $due]);
}
