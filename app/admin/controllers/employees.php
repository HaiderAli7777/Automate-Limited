<?php
/* Employee profiles and departments. Salaries and bank details need payroll access. */
declare(strict_types=1);

function employee_query(): array
{
    $where = [];
    $params = [];
    $status = input('status', 'current');
    if ($status === 'current') {
        $where[] = "e.status IN ('probation', 'active', 'notice')";
    } elseif (isset(EMPLOYEE_STATUSES[$status])) {
        $where[] = 'e.status = ?';
        $params[] = $status;
    }
    if ($dept = input_int('department')) {
        $where[] = 'e.department_id = ?';
        $params[] = $dept;
    }
    if (isset(EMPLOYMENT_TYPES[input('type')])) {
        $where[] = 'e.employment_type = ?';
        $params[] = input('type');
    }
    if (($q = input('q')) !== '') {
        $where[] = "(CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR e.email LIKE ? OR e.employee_code LIKE ? OR e.designation LIKE ? OR e.phone LIKE ?)";
        array_push($params, "%$q%", "%$q%", "%$q%", "%$q%", "%$q%");
    }
    $sql = ' FROM employees e LEFT JOIN departments d ON d.id = e.department_id LEFT JOIN employees m ON m.id = e.manager_id'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
    return [$sql, $params];
}

function employees_index(): void
{
    [$from, $params] = employee_query();
    $p = paginate((int) db()->value('SELECT COUNT(*)' . $from, $params), 40);
    $rows = db()->all(
        "SELECT e.*, d.name AS department_name, CONCAT(m.first_name, ' ', m.last_name) AS manager_name" . $from
        . ' ORDER BY e.first_name, e.last_name LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'],
        $params
    );
    $monthStart = date('Y-m-01');
    $stats = [
        'headcount' => (int) db()->value("SELECT COUNT(*) FROM employees WHERE status IN ('probation', 'active', 'notice')"),
        'probation' => (int) db()->value("SELECT COUNT(*) FROM employees WHERE status = 'probation'"),
        'joiners' => (int) db()->value('SELECT COUNT(*) FROM employees WHERE join_date >= ?', [$monthStart]),
        'leavers' => (int) db()->value("SELECT COUNT(*) FROM employees WHERE status = 'left' AND exit_date >= ?", [date('Y-01-01')]),
        'ready' => (int) db()->value("SELECT COUNT(*) FROM applications a WHERE a.status = 'hired' AND NOT EXISTS (SELECT 1 FROM employees e WHERE e.application_id = a.id)"),
    ];
    admin_view('employees/index', ['title' => 'Employees', 'nav' => 'employees', 'rows' => $rows, 'p' => $p, 'stats' => $stats]);
}

function employees_export(): void
{
    [$from, $params] = employee_query();
    $rows = db()->all("SELECT e.*, d.name AS department_name, CONCAT(m.first_name, ' ', m.last_name) AS manager_name" . $from . ' ORDER BY e.employee_code', $params);
    $pay = user_can('payroll.manage');
    $header = ['Code', 'First name', 'Last name', 'Email', 'Phone', 'CNIC', 'Department', 'Designation', 'Type', 'Manager', 'Join date', 'Probation ends', 'Status', 'Exit date'];
    if ($pay) {
        array_push($header, 'Basic salary', 'Currency', 'Bank', 'Account title', 'IBAN', 'Tax number');
    }
    csv_download('employees-' . date('Y-m-d') . '.csv', $header, array_map(static function ($r) use ($pay) {
        $row = [$r['employee_code'], $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['cnic'], $r['department_name'], $r['designation'],
            EMPLOYMENT_TYPES[$r['employment_type']] ?? $r['employment_type'], $r['manager_name'], $r['join_date'], $r['probation_end'],
            EMPLOYEE_STATUSES[$r['status']] ?? $r['status'], $r['exit_date']];
        if ($pay) {
            array_push($row, $r['basic_salary'], $r['currency'], $r['bank_name'], $r['bank_account_title'], $r['bank_iban'], $r['tax_number']);
        }
        return $row;
    }, $rows));
}

/** Prefill a new profile from a hired application. */
function employee_from_application(int $appId): array
{
    $app = application_full($appId) ?? abort(404);
    $job = db()->one('SELECT * FROM jobs WHERE id = ?', [(int) $app['job_id']]);
    // "PKR 250,000 / month" -> 250000
    $salary = preg_match('/(\d[\d,]*(?:\.\d+)?)/', (string) $app['offer_salary'], $m) ? (float) str_replace(',', '', $m[1]) : 0.0;
    $join = $app['offer_start_date'] ?: today();
    $probation = (int) setting('probation_months', '3');
    return [
        'application_id' => $appId,
        'candidate_id' => (int) $app['candidate_id'],
        'first_name' => $app['first_name'], 'last_name' => $app['last_name'], 'email' => $app['email'], 'phone' => $app['phone'],
        'city' => $app['location'],
        'designation' => $app['job_title'],
        'department_id' => $job && $job['department'] ? department_id_for((string) $job['department']) : null,
        'employment_type' => $job['employment_type'] ?? 'full_time',
        'work_location' => $job['location'] ?? null,
        'join_date' => $join,
        'probation_end' => $probation > 0 ? date('Y-m-d', strtotime($join . ' +' . $probation . ' months')) : null,
        'status' => $probation > 0 ? 'probation' : 'active',
        'basic_salary' => $salary > 0 ? $salary : '',
        'currency' => $job['salary_currency'] ?? setting('default_currency', 'PKR'),
    ];
}

function employees_form(int $id = 0): void
{
    $emp = $id ? (employee_full($id) ?? abort(404)) : null;
    $source = null;
    if (!$emp && ($appId = input_int('application'))) {
        if ($existing = db()->value('SELECT id FROM employees WHERE application_id = ?', [$appId])) {
            flash('info', 'This hire already has an employee profile.');
            redirect(admin_url('employees/' . $existing));
        }
        $emp = employee_from_application($appId);
        $source = application_full($appId);
    }
    admin_view('employees/form', [
        'title' => $id ? 'Edit ' . employee_name($emp) : 'Add an employee', 'nav' => 'employees',
        'emp' => $emp, 'isNew' => !$id, 'source' => $source, 'code' => $id ? $emp['employee_code'] : next_employee_code(),
    ]);
}

function employees_save(int $id = 0): void
{
    $old = $id ? (db()->one('SELECT * FROM employees WHERE id = ?', [$id]) ?? abort(404)) : null;
    $back = $id ? admin_url('employees/' . $id . '/edit') : admin_url('employees/new') . (input('application_id') !== '' ? '?application=' . input_int('application_id') : '');
    $errors = [];
    $date = static function (string $k, bool $required = false) use (&$errors): ?string {
        $v = input($k);
        if ($v === '') {
            if ($required) {
                $errors[$k] = 'Enter a date.';
            }
            return null;
        }
        $d = parse_dt($v, false);
        if (!$d) {
            $errors[$k] = 'Enter a valid date.';
        }
        return $d;
    };
    $data = [
        'employee_code' => mb_substr(input('employee_code'), 0, 20),
        'first_name' => mb_substr(input('first_name'), 0, 80),
        'last_name' => mb_substr(input('last_name'), 0, 80),
        'email' => nullable(strtolower(input('email'))),
        'phone' => nullable(mb_substr(input('phone'), 0, 40)),
        'cnic' => nullable(mb_substr(input('cnic'), 0, 30)),
        'date_of_birth' => $date('date_of_birth'),
        'gender' => isset(GENDERS[input('gender')]) ? nullable(input('gender')) : null,
        'marital_status' => isset(MARITAL_STATUSES[input('marital_status')]) ? nullable(input('marital_status')) : null,
        'address' => nullable((string) ($_POST['address'] ?? '')),
        'city' => nullable(mb_substr(input('city'), 0, 80)),
        'emergency_name' => nullable(mb_substr(input('emergency_name'), 0, 120)),
        'emergency_relation' => nullable(mb_substr(input('emergency_relation'), 0, 60)),
        'emergency_phone' => nullable(mb_substr(input('emergency_phone'), 0, 40)),
        'department_id' => isset(departments()[input_int('department_id')]) ? input_int('department_id') : null,
        'designation' => nullable(mb_substr(input('designation'), 0, 120)),
        'employment_type' => isset(EMPLOYMENT_TYPES[input('employment_type')]) ? input('employment_type') : 'full_time',
        'work_location' => nullable(mb_substr(input('work_location'), 0, 120)),
        'manager_id' => input_int('manager_id') ?: null,
        'join_date' => $date('join_date', true),
        'probation_end' => $date('probation_end'),
        'status' => isset(EMPLOYEE_STATUSES[input('status')]) ? input('status') : 'active',
        'notes' => nullable((string) ($_POST['notes'] ?? '')),
        'updated_at' => now(),
    ];
    if ($data['employee_code'] === '') {
        $data['employee_code'] = next_employee_code();
    } elseif (db()->value('SELECT id FROM employees WHERE employee_code = ? AND id <> ?', [$data['employee_code'], $id])) {
        $errors['employee_code'] = 'Another employee already has this code.';
    }
    if ($data['first_name'] === '') {
        $errors['first_name'] = 'Enter a first name.';
    }
    if ($data['email'] !== null && !valid_email($data['email'])) {
        $errors['email'] = 'Enter a valid email.';
    }
    if ($data['manager_id'] && ($data['manager_id'] === $id || !db()->value('SELECT id FROM employees WHERE id = ?', [$data['manager_id']]))) {
        $errors['manager_id'] = 'Choose someone else as manager.';
    }
    if ($data['status'] === 'left') {
        $data['exit_date'] = $date('exit_date', true);
        $data['exit_reason'] = in_array(input('exit_reason'), EXIT_REASONS, true) ? input('exit_reason') : null;
    } elseif ($old && $old['status'] === 'left') {
        $data['exit_date'] = null;
        $data['exit_reason'] = null;
    }
    // pay details only change for people who can see them
    if (user_can('payroll.manage')) {
        $basic = str_replace(',', '', input('basic_salary'));
        if ($basic !== '' && (!is_numeric($basic) || (float) $basic < 0)) {
            $errors['basic_salary'] = 'Enter the monthly basic salary as a number.';
        }
        $data['basic_salary'] = $basic === '' ? 0 : round((float) $basic, 2);
        $data['currency'] = in_array(input('currency'), currencies(), true) ? input('currency') : (string) setting('default_currency', 'PKR');
        $data['bank_name'] = nullable(mb_substr(input('bank_name'), 0, 120));
        $data['bank_account_title'] = nullable(mb_substr(input('bank_account_title'), 0, 120));
        $data['bank_iban'] = nullable(strtoupper(str_replace(' ', '', mb_substr(input('bank_iban'), 0, 60))));
        $data['tax_number'] = nullable(mb_substr(input('tax_number'), 0, 40));
    } elseif (!$old) {
        $data['currency'] = (string) setting('default_currency', 'PKR');
    }
    $appId = !$old ? input_int('application_id') : 0;
    if ($appId) {
        $app = db()->one("SELECT id, candidate_id, status FROM applications WHERE id = ?", [$appId]);
        if (!$app) {
            $errors['form'] = 'That application no longer exists.';
        } elseif ($existing = db()->value('SELECT id FROM employees WHERE application_id = ?', [$appId])) {
            redirect(admin_url('employees/' . $existing));
        }
    }
    if ($errors) {
        remember_input($errors);
        if (isset($errors['form'])) {
            flash('error', $errors['form']);
        }
        redirect($back);
    }

    if ($old) {
        db()->update('employees', $data, 'id = ?', [$id]);
        $changes = [];
        foreach (['status' => 'Status', 'department_id' => 'Department', 'designation' => 'Designation', 'manager_id' => 'Manager', 'basic_salary' => 'Basic salary'] as $k => $label) {
            if (array_key_exists($k, $data) && (string) ($old[$k] ?? '') !== (string) ($data[$k] ?? '') && !($k === 'basic_salary' && (float) $old[$k] === (float) $data[$k])) {
                $changes[] = $k === 'status' ? 'Status: ' . EMPLOYEE_STATUSES[$data['status']] : ($k === 'basic_salary' ? 'Basic salary changed' : $label . ' changed');
            }
        }
        log_activity('employee', $id, 'updated', $changes ? implode(', ', $changes) : 'Profile updated');
        flash('success', 'Saved.');
    } else {
        $data += ['created_by' => auth_id(), 'created_at' => now()];
        if ($appId) {
            $data['application_id'] = $appId;
            $data['candidate_id'] = (int) $app['candidate_id'];
        }
        $id = db()->insert('employees', $data);
        log_activity('employee', $id, 'created', $appId ? 'Employee profile created from their hiring' : 'Employee profile created');
        if ($appId) {
            log_activity('application', $appId, 'system', 'Converted to employee ' . $data['employee_code']);
            if ($app['status'] !== 'hired' && ($hired = first_stage_id(ats_stages(), 'hired'))) {
                application_move($appId, $hired);
            }
        }
        flash('success', employee_name($data) . ' is now on the team as ' . $data['employee_code'] . '.');
    }
    redirect(admin_url('employees/' . $id));
}

function employees_show(int $id): void
{
    $emp = employee_full($id) ?? abort(404);
    $pay = user_can('payroll.manage');
    admin_view('employees/show', [
        'title' => employee_name($emp), 'nav' => 'employees', 'emp' => $emp, 'pay' => $pay,
        'components' => $pay ? db()->all('SELECT * FROM employee_components WHERE employee_id = ? ORDER BY type, id', [$id]) : [],
        'payslips' => $pay ? db()->all('SELECT s.*, r.title AS run_title, r.status AS run_status FROM payslips s JOIN payroll_runs r ON r.id = s.run_id WHERE s.employee_id = ? ORDER BY s.period DESC LIMIT 12', [$id]) : [],
        'files' => db()->all("SELECT * FROM files WHERE entity_type = 'employee' AND entity_id = ? ORDER BY created_at DESC", [$id]),
        'reports' => db()->all("SELECT id, first_name, last_name, designation, status FROM employees WHERE manager_id = ? AND status <> 'left' ORDER BY first_name", [$id]),
        'timeline' => activities_for(array_filter([['employee', [$id]], $emp['application_id'] ? ['application', [(int) $emp['application_id']]] : null])),
        'application' => $emp['application_id'] ? db()->one('SELECT a.id, a.applied_at, a.hired_at, j.title FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.id = ?', [(int) $emp['application_id']]) : null,
    ]);
}

function employees_status(int $id): void
{
    $emp = employee_full($id) ?? abort(404);
    $to = input('status');
    if (!isset(EMPLOYEE_STATUSES[$to]) || $to === $emp['status']) {
        back(admin_url('employees/' . $id));
    }
    $data = ['status' => $to, 'updated_at' => now()];
    $body = '';
    if ($to === 'active' && $emp['status'] === 'probation') {
        $data['confirmed_on'] = parse_dt(input('effective'), false) ?: today();
        $body = 'Confirmed on ' . fmt_date($data['confirmed_on']);
    }
    if ($to === 'left') {
        $data['exit_date'] = parse_dt(input('effective'), false) ?: today();
        $data['exit_reason'] = in_array(input('reason'), EXIT_REASONS, true) ? input('reason') : null;
        $body = 'Last day ' . fmt_date($data['exit_date']) . ($data['exit_reason'] ? '. ' . $data['exit_reason'] : '');
    }
    if ($to === 'notice') {
        $data['exit_date'] = parse_dt(input('effective'), false);
        $body = $data['exit_date'] ? 'Last day ' . fmt_date($data['exit_date']) : '';
    }
    if ($emp['status'] === 'left' && $to !== 'left') {
        $data['exit_date'] = null;
        $data['exit_reason'] = null;
    }
    db()->update('employees', $data, 'id = ?', [$id]);
    log_activity('employee', $id, 'stage', 'Status: ' . EMPLOYEE_STATUSES[$to], $body, ['from' => EMPLOYEE_STATUSES[$emp['status']] ?? $emp['status'], 'to' => EMPLOYEE_STATUSES[$to]]);
    flash('success', employee_name($emp) . ': ' . mb_strtolower(EMPLOYEE_STATUSES[$to]) . '.');
    redirect(admin_url('employees/' . $id));
}

function employees_components(int $id): void
{
    db()->one('SELECT id FROM employees WHERE id = ?', [$id]) ?? abort(404);
    $types = input_array('comp_type');
    $labels = input_array('comp_label');
    $amounts = input_array('comp_amount');
    $rows = [];
    foreach ($labels as $i => $label) {
        $label = mb_substr(trim((string) $label), 0, 80);
        $amount = str_replace(',', '', trim((string) ($amounts[$i] ?? '')));
        if ($label === '' && $amount === '') {
            continue;
        }
        if ($label === '' || !is_numeric($amount) || (float) $amount < 0) {
            flash('error', 'Each line needs a name and an amount of zero or more.');
            back(admin_url('employees/' . $id));
        }
        $rows[] = ['type' => ($types[$i] ?? '') === 'deduction' ? 'deduction' : 'allowance', 'label' => $label, 'amount' => round((float) $amount, 2)];
    }
    db()->tx(static function (Db $db) use ($id, $rows): void {
        $db->delete('employee_components', 'employee_id = ?', [$id]);
        foreach ($rows as $r) {
            $db->insert('employee_components', ['employee_id' => $id] + $r);
        }
    });
    log_activity('employee', $id, 'updated', 'Monthly allowances and deductions updated');
    flash('success', 'Saved. New payslips will include these lines.');
    redirect(admin_url('employees/' . $id) . '#pay');
}

function employees_note(int $id): void
{
    db()->one('SELECT id FROM employees WHERE id = ?', [$id]) ?? abort(404);
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    if ($body === '') {
        flash('error', 'Write something first.');
        back(admin_url('employees/' . $id));
    }
    $kind = isset(ACTIVITY_KINDS[input('kind')]) ? input('kind') : 'note';
    log_activity('employee', $id, $kind, ['note' => 'Note', 'call' => 'Call logged', 'meeting' => 'Meeting logged', 'email' => 'Email logged', 'whatsapp' => 'WhatsApp logged'][$kind], $body);
    flash('success', 'Saved to the history.');
    redirect(admin_url('employees/' . $id) . '#history');
}

function employees_upload(int $id): void
{
    db()->one('SELECT id FROM employees WHERE id = ?', [$id]) ?? abort(404);
    $kind = isset(EMPLOYEE_FILE_KINDS[input('kind')]) ? input('kind') : 'attachment';
    $upload = store_upload('file', ATTACHMENT_EXTENSIONS);
    if (!$upload['ok']) {
        flash('error', $upload['error']);
        back(admin_url('employees/' . $id));
    }
    file_record('employee', $id, $upload, $kind);
    log_activity('employee', $id, 'file', 'Document uploaded: ' . $upload['original']);
    flash('success', 'Uploaded.');
    redirect(admin_url('employees/' . $id) . '#documents');
}

function employees_delete(int $id): void
{
    $emp = db()->one('SELECT * FROM employees WHERE id = ?', [$id]) ?? abort(404);
    if (db()->value('SELECT COUNT(*) FROM payslips WHERE employee_id = ?', [$id])) {
        flash('error', 'This employee has payslips, so the profile is kept for your records. Mark them as Left instead.');
        back(admin_url('employees/' . $id));
    }
    foreach (db()->all("SELECT * FROM files WHERE entity_type = 'employee' AND entity_id = ?", [$id]) as $f) {
        delete_file_record($f);
    }
    db()->run('UPDATE employees SET manager_id = NULL WHERE manager_id = ?', [$id]);
    db()->run('UPDATE departments SET head_employee_id = NULL WHERE head_employee_id = ?', [$id]);
    db()->delete('activities', "entity_type = 'employee' AND entity_id = ?", [$id]);
    db()->delete('employees', 'id = ?', [$id]);
    flash('success', employee_name($emp) . ' was deleted.');
    redirect(admin_url('employees'));
}

/* ------------------------------------------------------------------ departments */
function departments_index(): void
{
    $rows = db()->all(
        "SELECT d.*, CONCAT(h.first_name, ' ', h.last_name) AS head_name,
            (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id AND e.status <> 'left') AS headcount,
            (SELECT COALESCE(SUM(e.basic_salary), 0) FROM employees e WHERE e.department_id = d.id AND e.status <> 'left') AS basic_total
         FROM departments d LEFT JOIN employees h ON h.id = d.head_employee_id ORDER BY d.name"
    );
    admin_view('employees/departments', ['title' => 'Departments', 'nav' => 'departments', 'rows' => $rows]);
}

function departments_save(int $id = 0): void
{
    $name = mb_substr(input('name'), 0, 80);
    if ($name === '') {
        flash('error', 'Give the department a name.');
        back(admin_url('departments'));
    }
    if (db()->value('SELECT id FROM departments WHERE name = ? AND id <> ?', [$name, $id])) {
        flash('error', 'There is already a department called ' . $name . '.');
        back(admin_url('departments'));
    }
    $head = input_int('head_employee_id') ?: null;
    if ($id) {
        db()->one('SELECT id FROM departments WHERE id = ?', [$id]) ?? abort(404);
        db()->update('departments', ['name' => $name, 'head_employee_id' => $head, 'updated_at' => now()], 'id = ?', [$id]);
    } else {
        db()->insert('departments', ['name' => $name, 'head_employee_id' => $head, 'created_at' => now(), 'updated_at' => now()]);
    }
    flash('success', 'Saved.');
    redirect(admin_url('departments'));
}

function departments_delete(int $id): void
{
    $d = db()->one('SELECT * FROM departments WHERE id = ?', [$id]) ?? abort(404);
    if (db()->value('SELECT COUNT(*) FROM employees WHERE department_id = ?', [$id])) {
        flash('error', 'Move everyone out of ' . $d['name'] . ' before deleting it.');
        back(admin_url('departments'));
    }
    db()->delete('departments', 'id = ?', [$id]);
    flash('success', $d['name'] . ' was deleted.');
    redirect(admin_url('departments'));
}
