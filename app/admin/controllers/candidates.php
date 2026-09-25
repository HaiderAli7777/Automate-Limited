<?php
/* Candidates: everyone who applied or was added by hand, with every application they have. */
declare(strict_types=1);

/** Shared filter for the list and the CSV export. */
function candidate_query(): array
{
    $where = [];
    $params = [];
    $q = input('q');
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        $where[] = "(CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.current_title LIKE ? OR c.current_company LIKE ? OR c.tags LIKE ?)";
        array_push($params, $like, $like, $like, $like, $like, $like);
    }
    if ($job = input_int('job')) {
        $where[] = 'a.job_id = ?';
        $params[] = $job;
    }
    if ($stage = input_int('stage')) {
        $where[] = 'a.stage_id = ?';
        $params[] = $stage;
    }
    $status = input('status');
    if (in_array($status, ['active', 'hired', 'rejected'], true)) {
        $where[] = 'a.status = ?';
        $params[] = $status;
    } elseif ($status === 'pool') {
        $where[] = 'a.id IS NULL';
    }
    if (($source = input('source')) !== '' && isset(CANDIDATE_SOURCES[$source])) {
        $where[] = 'COALESCE(a.source, c.source) = ?';
        $params[] = $source;
    }
    if (($tag = input('tag')) !== '') {
        $where[] = 'CONCAT(\',\', REPLACE(c.tags, \', \', \',\'), \',\') LIKE ?';
        $params[] = '%,' . $tag . ',%';
    }
    $sql = ' FROM candidates c LEFT JOIN applications a ON a.candidate_id = c.id LEFT JOIN jobs j ON j.id = a.job_id'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
    return [$sql, $params];
}

function candidates_index(): void
{
    [$from, $params] = candidate_query();
    $p = paginate((int) db()->value('SELECT COUNT(*)' . $from, $params), 30);
    $rows = db()->all(
        'SELECT c.*, a.id AS app_id, a.stage_id, a.status AS app_status, a.applied_at, a.stage_changed_at, a.source AS app_source, j.title AS job_title,
            (SELECT AVG(f.rating) FROM interview_feedback f JOIN interviews i ON i.id = f.interview_id WHERE i.application_id = a.id) AS avg_rating'
        . $from . ' ORDER BY COALESCE(a.applied_at, c.created_at) DESC LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'],
        $params
    );
    $jobs = db()->all('SELECT id, title, status FROM jobs ORDER BY status = \'open\' DESC, title');
    admin_view('candidates/index', ['title' => 'Candidates', 'nav' => 'candidates', 'rows' => $rows, 'p' => $p, 'jobs' => $jobs]);
}

function candidates_export(): void
{
    [$from, $params] = candidate_query();
    $rows = db()->all('SELECT c.*, a.stage_id, a.status AS app_status, a.applied_at, a.source AS app_source, a.rejection_reason, j.title AS job_title' . $from . ' ORDER BY COALESCE(a.applied_at, c.created_at) DESC', $params);
    $stages = ats_stages();
    csv_download('candidates-' . date('Y-m-d') . '.csv',
        ['First name', 'Last name', 'Email', 'Phone', 'Location', 'Current title', 'Current company', 'Years of experience', 'Expected salary', 'Notice period', 'LinkedIn', 'Portfolio', 'Tags', 'Job', 'Stage', 'Status', 'Rejection reason', 'Source', 'Applied'],
        array_map(static fn ($r) => [
            $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['location'], $r['current_title'], $r['current_company'], $r['experience_years'],
            $r['expected_salary'], $r['notice_period'], $r['linkedin_url'], $r['portfolio_url'], $r['tags'], $r['job_title'],
            $stages[(int) $r['stage_id']]['name'] ?? '', $r['app_status'] ?? 'talent pool', $r['rejection_reason'],
            CANDIDATE_SOURCES[$r['app_source'] ?? $r['source'] ?? ''] ?? ($r['app_source'] ?? $r['source']), $r['applied_at'],
        ], $rows)
    );
}

function candidates_form(): void
{
    $jobs = db()->all("SELECT id, title, status FROM jobs WHERE status IN ('open', 'paused', 'draft') ORDER BY status = 'open' DESC, title");
    admin_view('candidates/form', ['title' => 'Add a candidate', 'nav' => 'candidates', 'candidate' => null, 'jobs' => $jobs]);
}

/** Validate candidate profile fields from the request. */
function candidate_input(array &$errors): array
{
    $email = strtolower(input('email'));
    $data = [
        'first_name' => mb_substr(input('first_name'), 0, 80),
        'last_name' => mb_substr(input('last_name'), 0, 80),
        'email' => $email,
        'phone' => nullable(mb_substr(input('phone'), 0, 40)),
        'location' => nullable(mb_substr(input('location'), 0, 120)),
        'current_title' => nullable(mb_substr(input('current_title'), 0, 120)),
        'current_company' => nullable(mb_substr(input('current_company'), 0, 120)),
        'experience_years' => input('experience_years') === '' ? null : max(0, min(60, round((float) input('experience_years'), 1))),
        'expected_salary' => nullable(mb_substr(input('expected_salary'), 0, 80)),
        'notice_period' => nullable(mb_substr(input('notice_period'), 0, 80)),
        'linkedin_url' => nullable(mb_substr(input('linkedin_url') !== '' ? normalise_url(input('linkedin_url')) : '', 0, 255)),
        'portfolio_url' => nullable(mb_substr(input('portfolio_url') !== '' ? normalise_url(input('portfolio_url')) : '', 0, 255)),
        'source' => isset(CANDIDATE_SOURCES[input('source')]) ? input('source') : 'other',
        'tags' => nullable(implode(', ', tag_list(input('tags')))),
    ];
    if ($data['first_name'] === '') {
        $errors['first_name'] = 'Enter a first name.';
    }
    if (!valid_email($email)) {
        $errors['email'] = 'Enter a valid email.';
    }
    if (input('experience_years') !== '' && !is_numeric(input('experience_years'))) {
        $errors['experience_years'] = 'Enter a number.';
    }
    return $data;
}

function candidates_create(): void
{
    $errors = [];
    $data = candidate_input($errors);
    $jobId = input_int('job_id');
    if ($jobId && !db()->value('SELECT id FROM jobs WHERE id = ?', [$jobId])) {
        $errors['job_id'] = 'Choose a job.';
    }
    $existing = valid_email($data['email']) ? db()->one('SELECT id FROM candidates WHERE email = ?', [$data['email']]) : null;
    if ($existing) {
        $errors['email'] = 'A candidate with this email already exists. Open their profile to add them to another job.';
    }
    $upload = null;
    if (!$errors && has_upload('resume')) {
        $upload = store_upload('resume', RESUME_EXTENSIONS);
        if (!$upload['ok']) {
            $errors['resume'] = $upload['error'];
        }
    }
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('candidates/new') . ($jobId ? '?job=' . $jobId : ''));
    }
    $now = now();
    [$candId, $appId] = db()->tx(static function (Db $db) use ($data, $jobId, $upload, $now): array {
        $candId = $db->insert('candidates', $data + ['created_at' => $now, 'updated_at' => $now]);
        $fileId = null;
        if ($upload && $upload['ok']) {
            $fileId = file_record('candidate', $candId, $upload, 'resume');
        }
        log_activity('candidate', $candId, 'created', 'Added by hand');
        $appId = null;
        if ($jobId) {
            $stageId = input_int('stage_id');
            if (!isset(ats_stages()[$stageId])) {
                $stageId = first_stage_id(ats_stages(), 'active');
            }
            $status = ats_stages()[$stageId]['kind'] === 'hired' ? 'hired' : (ats_stages()[$stageId]['kind'] === 'rejected' ? 'rejected' : 'active');
            $appId = $db->insert('applications', [
                'job_id' => $jobId, 'candidate_id' => $candId, 'stage_id' => $stageId, 'status' => $status,
                'resume_file_id' => $fileId, 'source' => $data['source'], 'owner_id' => auth_id(),
                'applied_at' => $now, 'stage_changed_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
            log_activity('application', $appId, 'created', 'Added to the pipeline by hand');
        }
        $note = trim((string) ($_POST['note'] ?? ''));
        if ($note !== '') {
            log_activity($appId ? 'application' : 'candidate', $appId ?: $candId, 'note', 'Note', $note);
        }
        return [$candId, $appId];
    });
    flash('success', 'Candidate added.');
    redirect($appId ? admin_url('applications/' . $appId) : admin_url('candidates/' . $candId));
}

function candidates_show(int $id): void
{
    $c = db()->one('SELECT * FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    $apps = db()->all('SELECT a.*, j.title AS job_title, j.status AS job_status FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.candidate_id = ? ORDER BY a.applied_at DESC', [$id]);
    $files = db()->all("SELECT * FROM files WHERE entity_type = 'candidate' AND entity_id = ? ORDER BY created_at DESC", [$id]);
    $appIds = array_map('intval', array_column($apps, 'id'));
    $timeline = activities_for([['candidate', [$id]], ['application', $appIds]]);
    $openJobs = db()->all("SELECT id, title FROM jobs WHERE status IN ('open', 'paused', 'draft')" . ($appIds ? ' AND id NOT IN (SELECT job_id FROM applications WHERE candidate_id = ' . $id . ')' : '') . ' ORDER BY title');
    admin_view('candidates/show', ['title' => candidate_name($c), 'nav' => 'candidates', 'c' => $c, 'apps' => $apps, 'files' => $files, 'timeline' => $timeline, 'openJobs' => $openJobs]);
}

function candidates_edit(int $id): void
{
    $c = db()->one('SELECT * FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    admin_view('candidates/form', ['title' => 'Edit ' . candidate_name($c), 'nav' => 'candidates', 'candidate' => $c, 'jobs' => []]);
}

function candidates_update(int $id): void
{
    db()->one('SELECT id FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    $errors = [];
    $data = candidate_input($errors);
    if (!$errors && db()->value('SELECT id FROM candidates WHERE email = ? AND id <> ?', [$data['email'], $id])) {
        $errors['email'] = 'Another candidate already uses this email.';
    }
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('candidates/' . $id . '/edit'));
    }
    db()->update('candidates', $data + ['updated_at' => now()], 'id = ?', [$id]);
    log_activity('candidate', $id, 'updated', 'Profile updated');
    flash('success', 'Profile saved.');
    redirect(admin_url('candidates/' . $id));
}

function candidates_add_to_job(int $id): void
{
    $c = db()->one('SELECT * FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    $jobId = input_int('job_id');
    $job = db()->one('SELECT * FROM jobs WHERE id = ?', [$jobId]);
    if (!$job) {
        flash('error', 'Choose a job.');
        redirect(admin_url('candidates/' . $id));
    }
    if (db()->value('SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?', [$jobId, $id])) {
        flash('error', candidate_name($c) . ' is already in the pipeline for ' . $job['title'] . '.');
        redirect(admin_url('candidates/' . $id));
    }
    $resume = db()->value("SELECT id FROM files WHERE entity_type = 'candidate' AND entity_id = ? AND kind = 'resume' ORDER BY id DESC LIMIT 1", [$id]);
    $now = now();
    $appId = db()->insert('applications', [
        'job_id' => $jobId, 'candidate_id' => $id, 'stage_id' => first_stage_id(ats_stages(), 'active'), 'status' => 'active',
        'resume_file_id' => $resume ?: null, 'source' => $c['source'] ?: 'other', 'owner_id' => auth_id(),
        'applied_at' => $now, 'stage_changed_at' => $now, 'created_at' => $now, 'updated_at' => $now,
    ]);
    log_activity('application', $appId, 'created', 'Added to ' . $job['title'] . ' by hand');
    flash('success', 'Added to ' . $job['title'] . '.');
    redirect(admin_url('applications/' . $appId));
}

function candidates_upload(int $id): void
{
    db()->one('SELECT id FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    $kind = input('kind') === 'resume' ? 'resume' : 'attachment';
    $upload = store_upload('file', $kind === 'resume' ? RESUME_EXTENSIONS : ATTACHMENT_EXTENSIONS);
    if (!$upload['ok']) {
        flash('error', $upload['error']);
        back(admin_url('candidates/' . $id));
    }
    $fileId = file_record('candidate', $id, $upload, $kind);
    if ($kind === 'resume') {
        // the newest CV becomes the one shown on open applications
        db()->run("UPDATE applications SET resume_file_id = ? WHERE candidate_id = ? AND status = 'active'", [$fileId, $id]);
    }
    log_activity('candidate', $id, 'file', ($kind === 'resume' ? 'New CV uploaded: ' : 'File uploaded: ') . $upload['original']);
    flash('success', 'Uploaded.');
    back(admin_url('candidates/' . $id));
}

function candidates_delete(int $id): void
{
    $c = db()->one('SELECT * FROM candidates WHERE id = ?', [$id]) ?? abort(404);
    $appIds = array_map('intval', db()->column('SELECT id FROM applications WHERE candidate_id = ?', [$id]));
    db()->tx(static function (Db $db) use ($id, $appIds): void {
        foreach ($db->all("SELECT * FROM files WHERE entity_type = 'candidate' AND entity_id = ?", [$id]) as $f) {
            delete_file_record($f);
        }
        $db->delete('activities', "entity_type = 'candidate' AND entity_id = ?", [$id]);
        if ($appIds) {
            $db->run("DELETE FROM activities WHERE entity_type = 'application' AND entity_id IN (" . in_list($appIds) . ')', $appIds);
            $db->run("DELETE FROM tasks WHERE entity_type = 'application' AND entity_id IN (" . in_list($appIds) . ')', $appIds);
        }
        $db->delete('candidates', 'id = ?', [$id]);
    });
    flash('success', candidate_name($c) . ' and their files were deleted.');
    redirect(admin_url('candidates'));
}
