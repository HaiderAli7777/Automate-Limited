<?php
/* Job postings: create, publish to the careers page, screening questions, per-job pipeline. */
declare(strict_types=1);

function jobs_index(): void
{
    $status = input('status', 'open');
    $q = input('q');
    $where = [];
    $params = [];
    if (isset(JOB_STATUSES[$status])) {
        $where[] = 'j.status = ?';
        $params[] = $status;
    } else {
        $status = 'all';
    }
    if ($q !== '') {
        $where[] = '(j.title LIKE ? OR j.department LIKE ? OR j.location LIKE ?)';
        array_push($params, "%$q%", "%$q%", "%$q%");
    }
    $first = first_stage_id(ats_stages(), 'active') ?? 0;
    $jobs = db()->all(
        "SELECT j.*, u.name AS manager_name,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS total_apps,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'active') AS active_apps,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'active' AND a.stage_id = ?) AS new_apps,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status = 'hired') AS hired_apps
         FROM jobs j LEFT JOIN users u ON u.id = j.hiring_manager_id"
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY FIELD(j.status, \'open\', \'draft\', \'paused\', \'closed\'), j.updated_at DESC',
        array_merge([$first], $params)
    );
    $counts = db()->pairs('SELECT status, COUNT(*) FROM jobs GROUP BY status');
    admin_view('jobs/index', ['title' => 'Jobs', 'nav' => 'jobs', 'jobs' => $jobs, 'status' => $status, 'q' => $q, 'counts' => $counts]);
}

function jobs_form(int $id = 0): void
{
    $job = $id ? (db()->one('SELECT * FROM jobs WHERE id = ?', [$id]) ?? abort(404)) : null;
    $questions = $id ? db()->all('SELECT * FROM job_questions WHERE job_id = ? ORDER BY sort_order, id', [$id]) : [];
    $departments = db()->column('SELECT DISTINCT department FROM jobs WHERE department IS NOT NULL AND department <> \'\' ORDER BY department');
    $locations = db()->column('SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL AND location <> \'\' ORDER BY location');
    admin_view('jobs/form', [
        'title' => $job ? 'Edit ' . $job['title'] : 'New job',
        'nav' => 'jobs',
        'job' => $job,
        'questions' => $questions,
        'departments' => $departments,
        'locations' => $locations,
    ]);
}

function jobs_save(int $id = 0): void
{
    $job = $id ? (db()->one('SELECT * FROM jobs WHERE id = ?', [$id]) ?? abort(404)) : null;
    $errors = [];
    $title = input('title');
    if ($title === '') {
        $errors['title'] = 'Give the job a title.';
    }
    $status = isset(JOB_STATUSES[input('status')]) ? input('status') : 'draft';
    $salaryMin = input('salary_min') === '' ? null : (float) str_replace(',', '', input('salary_min'));
    $salaryMax = input('salary_max') === '' ? null : (float) str_replace(',', '', input('salary_max'));
    if ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin) {
        $errors['salary_max'] = 'The top of the range is below the bottom.';
    }
    $closes = parse_dt(input('closes_at'), false);
    if (input('closes_at') !== '' && $closes === null) {
        $errors['closes_at'] = 'Enter a valid date.';
    }
    if ($errors) {
        remember_input($errors);
        redirect($id ? admin_url('jobs/' . $id . '/edit') : admin_url('jobs/new'));
    }

    $data = [
        'title' => mb_substr($title, 0, 160),
        'department' => nullable(mb_substr(input('department'), 0, 80)),
        'location' => nullable(mb_substr(input('location'), 0, 120)),
        'employment_type' => isset(EMPLOYMENT_TYPES[input('employment_type')]) ? input('employment_type') : 'full_time',
        'workplace' => isset(WORKPLACES[input('workplace')]) ? input('workplace') : 'onsite',
        'experience_level' => isset(EXPERIENCE_LEVELS[input('experience_level')]) ? nullable(input('experience_level')) : null,
        'openings' => max(1, input_int('openings', 1)),
        'salary_min' => $salaryMin,
        'salary_max' => $salaryMax,
        'salary_currency' => in_array(input('salary_currency'), currencies(), true) ? input('salary_currency') : 'PKR',
        'salary_period' => input('salary_period') === 'year' ? 'year' : 'month',
        'salary_visible' => input('salary_visible') === '1' ? 1 : 0,
        'summary' => nullable(mb_substr(input('summary'), 0, 400)),
        'description' => nullable((string) ($_POST['description'] ?? '')),
        'requirements' => nullable((string) ($_POST['requirements'] ?? '')),
        'benefits' => nullable((string) ($_POST['benefits'] ?? '')),
        'status' => $status,
        'listed' => input('listed', '1') === '1' ? 1 : 0,
        'hiring_manager_id' => input_int('hiring_manager_id') ?: null,
        'closes_at' => $closes,
        'updated_at' => now(),
    ];
    if ($status === 'open' && (!$job || !$job['published_at'])) {
        $data['published_at'] = now();
    }

    $id = db()->tx(static function (Db $db) use ($id, $job, $data): int {
        if ($job) {
            $db->update('jobs', $data, 'id = ?', [$id]);
        } else {
            $data['slug'] = unique_job_slug($data['title']);
            $data['created_by'] = auth_id();
            $data['created_at'] = now();
            $id = $db->insert('jobs', $data);
        }
        // screening questions: rewrite the set, keeping ids so old answers still read well
        $qIds = input_array('q_id');
        $qText = input_array('q_text');
        $qType = input_array('q_type');
        $qOpts = input_array('q_options');
        $qReq = input_array('q_required');
        $keep = [];
        foreach ($qText as $i => $text) {
            $text = mb_substr(trim((string) $text), 0, 255);
            if ($text === '') {
                continue;
            }
            $row = [
                'job_id' => $id,
                'question' => $text,
                'qtype' => isset(QUESTION_TYPES[$qType[$i] ?? '']) ? $qType[$i] : 'text',
                'options' => nullable(implode("\n", array_filter(array_map('trim', explode("\n", (string) ($qOpts[$i] ?? '')))))),
                'required' => ($qReq[$i] ?? '') === '1' ? 1 : 0,
                'sort_order' => ($i + 1) * 10,
            ];
            $qid = (int) ($qIds[$i] ?? 0);
            if ($qid && $db->value('SELECT id FROM job_questions WHERE id = ? AND job_id = ?', [$qid, $id])) {
                $db->update('job_questions', $row, 'id = ?', [$qid]);
                $keep[] = $qid;
            } else {
                $keep[] = $db->insert('job_questions', $row);
            }
        }
        if ($keep) {
            $db->run('DELETE FROM job_questions WHERE job_id = ? AND id NOT IN (' . in_list($keep) . ')', array_merge([$id], $keep));
        } else {
            $db->delete('job_questions', 'job_id = ?', [$id]);
        }
        return $id;
    });

    flash('success', $job ? 'Job saved.' : ($status === 'open' ? 'Job published. It\'s live on the careers page.' : 'Job saved as ' . strtolower(JOB_STATUSES[$status]) . '.'));
    redirect(admin_url('jobs/' . $id));
}

function jobs_show(int $id): void
{
    $job = db()->one('SELECT j.*, u.name AS manager_name FROM jobs j LEFT JOIN users u ON u.id = j.hiring_manager_id WHERE j.id = ?', [$id]) ?? abort(404);
    $tab = in_array(input('tab'), ['pipeline', 'list', 'details'], true) ? input('tab') : 'pipeline';
    $apps = db()->all(
        "SELECT a.*, c.first_name, c.last_name, c.email, c.phone, c.location, c.current_title,
            (SELECT AVG(f.rating) FROM interview_feedback f JOIN interviews i ON i.id = f.interview_id WHERE i.application_id = a.id) AS avg_rating,
            (SELECT MIN(i.scheduled_at) FROM interviews i WHERE i.application_id = a.id AND i.status = 'scheduled' AND i.scheduled_at >= ?) AS next_interview
         FROM applications a JOIN candidates c ON c.id = a.candidate_id WHERE a.job_id = ? ORDER BY a.stage_changed_at DESC",
        [now(), $id]
    );
    $questions = db()->all('SELECT * FROM job_questions WHERE job_id = ? ORDER BY sort_order, id', [$id]);
    admin_view('jobs/show', ['title' => $job['title'], 'nav' => 'jobs', 'job' => $job, 'apps' => $apps, 'tab' => $tab, 'questions' => $questions, 'wide' => $tab === 'pipeline']);
}

function jobs_status(int $id): void
{
    $job = db()->one('SELECT * FROM jobs WHERE id = ?', [$id]) ?? abort(404);
    $status = input('status');
    if (!isset(JOB_STATUSES[$status])) {
        abort(400);
    }
    $data = ['status' => $status, 'updated_at' => now()];
    if ($status === 'open' && !$job['published_at']) {
        $data['published_at'] = now();
    }
    db()->update('jobs', $data, 'id = ?', [$id]);
    $msg = ['open' => 'Job is live on the careers page.', 'paused' => 'Job paused. It\'s hidden from the careers page.', 'closed' => 'Job closed.', 'draft' => 'Job moved back to draft.'][$status];
    flash('success', $msg);
    back(admin_url('jobs/' . $id));
}

function jobs_duplicate(int $id): void
{
    $job = db()->one('SELECT * FROM jobs WHERE id = ?', [$id]) ?? abort(404);
    $newId = db()->tx(static function (Db $db) use ($job, $id): int {
        $copy = $job;
        unset($copy['id']);
        $copy['title'] = mb_substr($job['title'] . ' (copy)', 0, 160);
        $copy['slug'] = unique_job_slug($copy['title']);
        $copy['status'] = 'draft';
        $copy['listed'] = 1;
        $copy['published_at'] = null;
        $copy['created_by'] = auth_id();
        $copy['created_at'] = now();
        $copy['updated_at'] = now();
        $newId = $db->insert('jobs', $copy);
        foreach ($db->all('SELECT * FROM job_questions WHERE job_id = ?', [$id]) as $q) {
            unset($q['id']);
            $q['job_id'] = $newId;
            $db->insert('job_questions', $q);
        }
        return $newId;
    });
    flash('success', 'Copied as a draft. Edit it, then publish.');
    redirect(admin_url('jobs/' . $newId . '/edit'));
}

function jobs_delete(int $id): void
{
    $job = db()->one('SELECT * FROM jobs WHERE id = ?', [$id]) ?? abort(404);
    $n = (int) db()->value('SELECT COUNT(*) FROM applications WHERE job_id = ?', [$id]);
    if ($n > 0) {
        flash('error', 'This job has ' . plural($n, 'application') . '. Close it instead, so the candidates and their history stay.');
        redirect(admin_url('jobs/' . $id));
    }
    db()->delete('jobs', 'id = ?', [$id]);
    flash('success', 'Deleted "' . $job['title'] . '".');
    redirect(admin_url('jobs'));
}
