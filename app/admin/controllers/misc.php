<?php
/* Tasks, files, timeline entries and global search. */
declare(strict_types=1);

/* ------------------------------------------------------------------ tasks */
function tasks_index(): void
{
    $view = input('view', 'mine');
    $status = input('status', 'open');
    $where = [];
    $params = [];
    if ($view === 'mine' || !(user_can('ats') || user_can('crm'))) {
        $where[] = 't.assigned_to = ?';
        $params[] = auth_id();
        $view = 'mine';
    } elseif ($view === 'created') {
        $where[] = 't.created_by = ?';
        $params[] = auth_id();
    }
    if ($status === 'done') {
        $where[] = 't.completed_at IS NOT NULL';
    } else {
        $where[] = 't.completed_at IS NULL';
    }
    $sql = 'SELECT t.*, u.name AS assignee FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ($status === 'done' ? ' ORDER BY t.completed_at DESC LIMIT 200' : ' ORDER BY t.due_at IS NULL, t.due_at, t.id');
    $tasks = db()->all($sql, $params);
    admin_view('tasks/index', ['title' => 'Tasks', 'nav' => 'tasks', 'tasks' => task_links($tasks), 'view' => $view, 'status' => $status]);
}

/** Attach a link and label for whatever each task is about. */
function task_links(array $tasks): array
{
    foreach ($tasks as &$t) {
        $t['link'] = null;
        $t['about'] = null;
        if ($t['entity_type'] === 'lead') {
            $t['about'] = db()->value('SELECT title FROM leads WHERE id = ?', [(int) $t['entity_id']]);
            $t['link'] = admin_url('leads/' . $t['entity_id']);
        } elseif ($t['entity_type'] === 'application') {
            $row = db()->one('SELECT c.first_name, c.last_name, j.title FROM applications a JOIN candidates c ON c.id = a.candidate_id JOIN jobs j ON j.id = a.job_id WHERE a.id = ?', [(int) $t['entity_id']]);
            $t['about'] = $row ? candidate_name($row) . ', ' . $row['title'] : null;
            $t['link'] = admin_url('applications/' . $t['entity_id']);
        } elseif ($t['entity_type'] === 'contact') {
            $t['about'] = db()->value('SELECT name FROM contacts WHERE id = ?', [(int) $t['entity_id']]);
            $t['link'] = admin_url('contacts/' . $t['entity_id']);
        }
    }
    return $tasks;
}

function tasks_create(): void
{
    $title = input('title');
    if ($title === '') {
        flash('error', 'Give the task a title.');
        back(admin_url('tasks'));
    }
    $type = input('entity_type');
    $eid = input_int('entity_id');
    if (!in_array($type, ['lead', 'application', 'contact'], true) || !$eid) {
        $type = null;
        $eid = null;
    }
    $assignee = input_int('assigned_to') ?: auth_id();
    $due = parse_dt(input('due_at'));
    if ($due !== null && !str_contains(input('due_at'), ':') && !str_contains(input('due_at'), 'T')) {
        $due = substr($due, 0, 10) . ' 17:00:00';
    }
    $id = db()->insert('tasks', [
        'title' => mb_substr($title, 0, 190),
        'notes' => nullable(input('notes')),
        'entity_type' => $type,
        'entity_id' => $eid,
        'assigned_to' => $assignee,
        'due_at' => $due,
        'created_by' => auth_id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($type) {
        log_activity($type, (int) $eid, 'task', 'Task added: ' . $title, '', ['task_id' => $id, 'due' => $due, 'assignee' => user_name($assignee)]);
    }
    if ($assignee !== auth_id()) {
        notify([$assignee], 'New task from ' . auth_user()['name'] . ': ' . $title, admin_url('tasks'), $due ? 'Due ' . fmt_datetime($due) : '', 'check-square');
        $email = db()->value('SELECT email FROM users WHERE id = ? AND is_active = 1', [$assignee]);
        if ($email) {
            Mailer::send((string) $email, 'New task: ' . $title, auth_user()['name'] . " assigned you a task.\n\n" . $title . ($due ? "\nDue: " . fmt_datetime($due) : '') . "\n\n" . site_origin() . admin_url('tasks'));
        }
    }
    flash('success', 'Task added.');
    back(admin_url('tasks'));
}

function tasks_toggle(int $id): void
{
    $t = db()->one('SELECT * FROM tasks WHERE id = ?', [$id]) ?? abort(404);
    $done = $t['completed_at'] === null;
    db()->update('tasks', ['completed_at' => $done ? now() : null, 'updated_at' => now()], 'id = ?', [$id]);
    if ($done && $t['entity_type']) {
        log_activity((string) $t['entity_type'], (int) $t['entity_id'], 'task', 'Task done: ' . $t['title']);
    }
    if (is_ajax()) {
        json_out(['ok' => true, 'done' => $done]);
    }
    back(admin_url('tasks'));
}

function tasks_delete(int $id): void
{
    $t = db()->one('SELECT * FROM tasks WHERE id = ?', [$id]) ?? abort(404);
    if ((int) $t['created_by'] !== auth_id() && (int) $t['assigned_to'] !== auth_id() && !user_can('data.delete')) {
        abort(403);
    }
    db()->delete('tasks', 'id = ?', [$id]);
    flash('success', 'Task deleted.');
    back(admin_url('tasks'));
}

/* ------------------------------------------------------------------ timeline */
function activities_delete(int $id): void
{
    $a = db()->one('SELECT * FROM activities WHERE id = ?', [$id]) ?? abort(404);
    $editable = in_array($a['type'], array_keys(ACTIVITY_KINDS), true);
    if (!$editable || ((int) $a['user_id'] !== auth_id() && !user_can('data.delete'))) {
        abort(403, 'Only the person who wrote a note can delete it.');
    }
    db()->delete('activities', 'id = ?', [$id]);
    flash('success', 'Deleted.');
    back(admin_url());
}

/* ------------------------------------------------------------------ files */
function files_download(int $id): void
{
    $file = db()->one('SELECT * FROM files WHERE id = ?', [$id]) ?? abort(404);
    // interviewers can open CVs only for candidates they are interviewing
    if (!user_can('ats')) {
        $allowed = false;
        if ($file['entity_type'] === 'candidate') {
            foreach (db()->column('SELECT id FROM applications WHERE candidate_id = ?', [(int) $file['entity_id']]) as $appId) {
                if (can_view_application((int) $appId)) {
                    $allowed = true;
                    break;
                }
            }
        }
        if (!$allowed) {
            abort(403);
        }
    }
    $path = file_path_for($file);
    if (!is_file($path)) {
        abort(404, 'The file is missing from storage.');
    }
    $inline = input('inline') === '1' && $file['mime'] === 'application/pdf';
    header_remove('Content-Security-Policy');
    header('Content-Type: ' . $file['mime']);
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . str_replace(['"', "\r", "\n"], '', $file['original_name']) . '"; filename*=UTF-8\'\'' . rawurlencode($file['original_name']));
    readfile($path);
    exit;
}

function files_delete(int $id): void
{
    $file = db()->one('SELECT * FROM files WHERE id = ?', [$id]) ?? abort(404);
    db()->run('UPDATE applications SET resume_file_id = NULL WHERE resume_file_id = ?', [$id]);
    delete_file_record($file);
    log_activity((string) $file['entity_type'], (int) $file['entity_id'], 'file', 'File removed: ' . $file['original_name']);
    flash('success', 'File deleted.');
    back(admin_url());
}

/* ------------------------------------------------------------------ search */
function search_page(): void
{
    $q = input('q');
    $results = ['candidates' => [], 'leads' => [], 'contacts' => [], 'jobs' => []];
    if (mb_strlen($q) >= 2) {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        if (user_can('ats')) {
            $results['candidates'] = db()->all(
                "SELECT c.*, (SELECT a.id FROM applications a WHERE a.candidate_id = c.id ORDER BY a.applied_at DESC LIMIT 1) AS app_id
                 FROM candidates c WHERE CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.tags LIKE ? OR c.current_company LIKE ?
                 ORDER BY c.updated_at DESC LIMIT 25",
                [$like, $like, $like, $like, $like]
            );
            $results['jobs'] = db()->all('SELECT * FROM jobs WHERE title LIKE ? OR department LIKE ? OR location LIKE ? ORDER BY updated_at DESC LIMIT 10', [$like, $like, $like]);
        }
        if (user_can('crm')) {
            $results['leads'] = db()->all(
                'SELECT l.*, c.name AS contact_name, c.company AS contact_company FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id
                 WHERE (l.title LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.phone LIKE ?)' . crm_scope('l') . ' ORDER BY l.updated_at DESC LIMIT 25',
                [$like, $like, $like, $like, $like]
            );
            $results['contacts'] = db()->all('SELECT * FROM contacts c WHERE (c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.phone LIKE ?)' . contact_scope('c') . ' ORDER BY c.updated_at DESC LIMIT 25', [$like, $like, $like, $like]);
        }
    }
    admin_view('search', ['title' => 'Search', 'nav' => 'search', 'q' => $q, 'results' => $results]);
}
