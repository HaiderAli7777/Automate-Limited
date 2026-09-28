<?php
/*
 * Payroll. A run covers one month for everyone, one department or one person.
 * Each payslip starts from the employee's basic salary and recurring lines;
 * allowances and deductions can then be edited until the payslip is confirmed.
 */
declare(strict_types=1);

function payroll_index(): void
{
    $runs = db()->all(
        "SELECT r.*, d.name AS department_name, CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
            (SELECT COUNT(*) FROM payslips s WHERE s.run_id = r.id) AS slips,
            (SELECT COALESCE(SUM(s.gross), 0) FROM payslips s WHERE s.run_id = r.id) AS gross,
            (SELECT COALESCE(SUM(s.deductions), 0) FROM payslips s WHERE s.run_id = r.id) AS deductions,
            (SELECT COALESCE(SUM(s.net), 0) FROM payslips s WHERE s.run_id = r.id) AS net,
            (SELECT MIN(s.currency) FROM payslips s WHERE s.run_id = r.id) AS currency
         FROM payroll_runs r LEFT JOIN departments d ON d.id = r.department_id LEFT JOIN employees e ON e.id = r.employee_id
         ORDER BY r.period DESC, r.id DESC LIMIT 100"
    );
    $month = date('Y-m');
    $stats = [
        'month_net' => (float) db()->value('SELECT COALESCE(SUM(net), 0) FROM payslips WHERE period = ?', [$month]),
        'month_slips' => (int) db()->value('SELECT COUNT(*) FROM payslips WHERE period = ?', [$month]),
        'headcount' => (int) db()->value("SELECT COUNT(*) FROM employees WHERE status IN ('probation', 'active', 'notice')"),
        'ytd' => (float) db()->value('SELECT COALESCE(SUM(gross), 0) FROM payslips WHERE period >= ?', [date('Y') . '-01']),
        'drafts' => (int) db()->value("SELECT COUNT(*) FROM payslips WHERE status = 'draft'"),
        'no_salary' => (int) db()->value("SELECT COUNT(*) FROM employees WHERE status IN ('probation', 'active', 'notice') AND basic_salary <= 0"),
    ];
    admin_view('payroll/index', ['title' => 'Payroll', 'nav' => 'payroll', 'runs' => $runs, 'stats' => $stats]);
}

function payroll_form(): void
{
    $period = valid_period(input('period')) ? input('period') : date('Y-m');
    $scope = in_array(input('scope'), ['all', 'department', 'employee'], true) ? input('scope') : 'all';
    $preview = payable_employees($period, $scope, input_int('department'), input_int('employee'));
    admin_view('payroll/form', [
        'title' => 'Run payroll', 'nav' => 'payroll', 'period' => $period, 'scope' => $scope,
        'preview' => $scope === 'all' || input('department') !== '' || input('employee') !== '' ? $preview : [],
    ]);
}

function payroll_create(): void
{
    $period = input('period');
    $scope = in_array(input('scope'), ['all', 'department', 'employee'], true) ? input('scope') : 'all';
    $deptId = input_int('department');
    $empId = input_int('employee');
    $back = admin_url('payroll/new') . '?' . http_build_query(['period' => $period, 'scope' => $scope, 'department' => $deptId ?: null, 'employee' => $empId ?: null]);
    if (!valid_period($period)) {
        flash('error', 'Choose the month to pay.');
        redirect($back);
    }
    if ($scope === 'department' && !isset(departments()[$deptId])) {
        flash('error', 'Choose a department.');
        redirect($back);
    }
    if ($scope === 'employee' && !db()->value('SELECT id FROM employees WHERE id = ?', [$empId])) {
        flash('error', 'Choose an employee.');
        redirect($back);
    }
    $people = array_values(array_filter(payable_employees($period, $scope, $deptId, $empId), static fn ($e) => !$e['existing_run']));
    if (!$people) {
        flash('error', 'Nobody left to pay for ' . period_label($period) . ' in this selection. Everyone already has a payslip, or nobody was employed that month.');
        redirect($back);
    }
    $label = match ($scope) {
        'department' => departments()[$deptId],
        'employee' => employee_name($people[0]),
        default => 'All employees',
    };
    $runId = db()->tx(static function (Db $db) use ($period, $scope, $deptId, $empId, $people, $label): int {
        $runId = $db->insert('payroll_runs', [
            'period' => $period, 'title' => mb_substr(period_label($period) . ': ' . $label, 0, 160), 'scope' => $scope,
            'department_id' => $scope === 'department' ? $deptId : null, 'employee_id' => $scope === 'employee' ? $empId : null,
            'pay_date' => parse_dt(input('pay_date'), false), 'notes' => nullable((string) ($_POST['notes'] ?? '')),
            'status' => 'draft', 'created_by' => auth_id(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($people as $emp) {
            $slipId = $db->insert('payslips', [
                'run_id' => $runId, 'employee_id' => (int) $emp['id'], 'period' => $period,
                'emp_name' => employee_name($emp), 'emp_code' => $emp['employee_code'], 'currency' => $emp['currency'] ?: 'PKR',
                'status' => 'draft', 'created_at' => now(), 'updated_at' => now(),
            ]);
            payslip_fill_from_profile($slipId, $emp);
        }
        return $runId;
    });
    log_activity('payroll', $runId, 'created', 'Payroll run created with ' . plural(count($people), 'payslip'));
    flash('success', plural(count($people), 'payslip') . ' created for ' . period_label($period) . '. Review them, add any allowances or deductions, then confirm.');
    redirect(admin_url('payroll/' . $runId));
}

function payroll_run(int $id): array
{
    return db()->one(
        "SELECT r.*, d.name AS department_name, u.name AS created_name, cu.name AS confirmed_name
         FROM payroll_runs r LEFT JOIN departments d ON d.id = r.department_id LEFT JOIN users u ON u.id = r.created_by LEFT JOIN users cu ON cu.id = r.confirmed_by
         WHERE r.id = ?",
        [$id]
    ) ?? abort(404);
}

function payroll_show(int $id): void
{
    $run = payroll_run($id);
    $slips = db()->all(
        'SELECT s.*, e.email, e.bank_name FROM payslips s JOIN employees e ON e.id = s.employee_id WHERE s.run_id = ? ORDER BY s.department, s.emp_name',
        [$id]
    );
    admin_view('payroll/show', [
        'title' => $run['title'], 'nav' => 'payroll', 'run' => $run, 'slips' => $slips, 'totals' => run_totals($id),
        'timeline' => activities_for([['payroll', [$id]]], 30),
    ]);
}

function payroll_confirm(int $id): void
{
    $run = payroll_run($id);
    if ($run['status'] === 'paid') {
        back(admin_url('payroll/' . $id));
    }
    $n = db()->run("UPDATE payslips SET status = 'confirmed', confirmed_at = ?, updated_at = ? WHERE run_id = ? AND status = 'draft'", [now(), now(), $id])->rowCount();
    run_sync_status($id);
    log_activity('payroll', $id, 'stage', 'Payroll confirmed', plural($n, 'payslip') . ' confirmed');
    flash('success', $n ? plural($n, 'payslip') . ' confirmed. The run is ready to pay.' : 'Everything was already confirmed.');
    redirect(admin_url('payroll/' . $id));
}

function payroll_paid(int $id): void
{
    $run = payroll_run($id);
    run_sync_status($id);
    $run = payroll_run($id);
    if ($run['status'] !== 'confirmed') {
        flash('error', 'Confirm every payslip before marking the run as paid.');
        back(admin_url('payroll/' . $id));
    }
    db()->update('payroll_runs', ['status' => 'paid', 'paid_at' => now(), 'pay_date' => $run['pay_date'] ?: today(), 'updated_at' => now()], 'id = ?', [$id]);
    log_activity('payroll', $id, 'stage', 'Marked as paid');
    flash('success', 'Marked as paid. Payslips are now locked.');
    redirect(admin_url('payroll/' . $id));
}

function payroll_delete(int $id): void
{
    $run = payroll_run($id);
    if ($run['status'] !== 'draft' || db()->value("SELECT COUNT(*) FROM payslips WHERE run_id = ? AND status = 'confirmed'", [$id])) {
        flash('error', 'Only a draft run with no confirmed payslips can be deleted. Reopen the payslips first.');
        back(admin_url('payroll/' . $id));
    }
    db()->delete('payroll_runs', 'id = ?', [$id]);
    db()->delete('activities', "entity_type = 'payroll' AND entity_id = ?", [$id]);
    flash('success', 'Draft payroll deleted. Those employees can be paid in a new run.');
    redirect(admin_url('payroll'));
}

function payroll_export(int $id): void
{
    $run = payroll_run($id);
    $rows = db()->all('SELECT s.*, e.bank_name, e.bank_account_title, e.email FROM payslips s JOIN employees e ON e.id = s.employee_id WHERE s.run_id = ? ORDER BY s.emp_name', [$id]);
    csv_download('payroll-' . $run['period'] . '-' . $id . '.csv',
        ['Employee code', 'Name', 'Department', 'Designation', 'Bank', 'Account title', 'IBAN', 'Currency', 'Basic', 'Allowances', 'Gross', 'Deductions', 'Net pay', 'Status'],
        array_map(static fn ($s) => [$s['emp_code'], $s['emp_name'], $s['department'], $s['designation'], $s['bank_name'], $s['bank_account_title'], $s['bank_iban'],
            $s['currency'], $s['basic'], $s['allowances'], $s['gross'], $s['deductions'], $s['net'], PAYROLL_STATUSES[$s['status']] ?? $s['status']], $rows)
    );
}

function payroll_email(int $id): void
{
    $run = payroll_run($id);
    $slips = db()->all("SELECT s.*, e.first_name, e.last_name, e.email FROM payslips s JOIN employees e ON e.id = s.employee_id WHERE s.run_id = ? AND s.status = 'confirmed'", [$id]);
    $sent = 0;
    $skipped = 0;
    foreach ($slips as $s) {
        if (!$s['email']) {
            $skipped++;
            continue;
        }
        [$ok] = send_template('payslip', (string) $s['email'], employee_vars($s + ['pay_date' => $run['pay_date']], $s), ['to_name' => employee_name($s)]);
        if ($ok) {
            db()->update('payslips', ['emailed_at' => now()], 'id = ?', [(int) $s['id']]);
            $sent++;
        }
    }
    log_activity('payroll', $id, 'email', 'Payslips emailed', plural($sent, 'employee') . ' emailed' . ($skipped ? ', ' . $skipped . ' without an email address' : ''));
    flash($sent ? 'success' : 'error', $sent ? plural($sent, 'payslip') . ' emailed.' . ($skipped ? ' ' . plural($skipped, 'employee') . ' had no email address.' : '') : 'No payslips were emailed. Confirm them first, and check Settings, Email.');
    redirect(admin_url('payroll/' . $id));
}

/* ------------------------------------------------------------------ one payslip */
function payslip_get(int $id): array
{
    return db()->one(
        'SELECT s.*, r.title AS run_title, r.status AS run_status, r.pay_date, e.first_name, e.last_name, e.email, e.cnic, e.join_date, e.bank_name, e.bank_account_title, e.tax_number
         FROM payslips s JOIN payroll_runs r ON r.id = s.run_id JOIN employees e ON e.id = s.employee_id WHERE s.id = ?',
        [$id]
    ) ?? abort(404);
}

function payslip_show(int $id): void
{
    $slip = payslip_get($id);
    $lines = db()->all('SELECT * FROM payslip_lines WHERE payslip_id = ? ORDER BY type, sort_order, id', [$id]);
    $siblings = db()->column('SELECT id FROM payslips WHERE run_id = ? ORDER BY department, emp_name', [(int) $slip['run_id']]);
    $pos = array_search((string) $id, array_map('strval', $siblings), true);
    admin_view('payroll/payslip', [
        'title' => 'Payslip: ' . $slip['emp_name'], 'nav' => 'payroll', 'slip' => $slip, 'lines' => $lines,
        'prev' => $pos !== false && $pos > 0 ? (int) $siblings[$pos - 1] : null,
        'next' => $pos !== false && $pos < count($siblings) - 1 ? (int) $siblings[$pos + 1] : null,
        'position' => $pos === false ? 0 : $pos + 1, 'count' => count($siblings),
    ]);
}

function payslip_editable(array $slip): bool
{
    return $slip['status'] === 'draft' && $slip['run_status'] !== 'paid';
}

function payslip_save(int $id): void
{
    $slip = payslip_get($id);
    if (!payslip_editable($slip)) {
        flash('error', 'This payslip is confirmed. Reopen it to make changes.');
        back(admin_url('payslips/' . $id));
    }
    $types = input_array('line_type');
    $labels = input_array('line_label');
    $amounts = input_array('line_amount');
    $rows = [];
    foreach ($labels as $i => $label) {
        $label = mb_substr(trim((string) $label), 0, 80);
        $amount = str_replace(',', '', trim((string) ($amounts[$i] ?? '')));
        if ($label === '' && $amount === '') {
            continue;
        }
        if ($label === '' || !is_numeric($amount) || (float) $amount < 0) {
            flash('error', 'Each line needs a name and an amount of zero or more.');
            back(admin_url('payslips/' . $id));
        }
        $rows[] = ['type' => ($types[$i] ?? '') === 'deduction' ? 'deduction' : 'allowance', 'label' => $label, 'amount' => round((float) $amount, 2)];
    }
    db()->tx(static function (Db $db) use ($id, $rows): void {
        $db->delete('payslip_lines', 'payslip_id = ?', [$id]);
        foreach ($rows as $i => $r) {
            $db->insert('payslip_lines', ['payslip_id' => $id, 'sort_order' => $i] + $r);
        }
        $db->update('payslips', ['notes' => nullable((string) ($_POST['notes'] ?? ''))], 'id = ?', [$id]);
    });
    payslip_recalc($id);
    if (input('then') === 'confirm') {
        payslip_confirm($id);
    }
    flash('success', 'Payslip saved.');
    redirect(admin_url('payslips/' . $id));
}

function payslip_confirm(int $id): void
{
    $slip = payslip_get($id);
    if ($slip['run_status'] === 'paid') {
        back(admin_url('payslips/' . $id));
    }
    db()->update('payslips', ['status' => 'confirmed', 'confirmed_at' => now(), 'updated_at' => now()], 'id = ?', [$id]);
    run_sync_status((int) $slip['run_id']);
    $next = db()->value("SELECT id FROM payslips WHERE run_id = ? AND status = 'draft' ORDER BY department, emp_name LIMIT 1", [(int) $slip['run_id']]);
    flash('success', 'Payslip for ' . $slip['emp_name'] . ' confirmed.' . ($next ? '' : ' Every payslip in this run is confirmed.'));
    redirect($next ? admin_url('payslips/' . $next) : admin_url('payroll/' . $slip['run_id']));
}

function payslip_reopen(int $id): void
{
    $slip = payslip_get($id);
    if ($slip['run_status'] === 'paid') {
        flash('error', 'This run is paid, so its payslips are locked.');
        back(admin_url('payslips/' . $id));
    }
    db()->update('payslips', ['status' => 'draft', 'confirmed_at' => null, 'updated_at' => now()], 'id = ?', [$id]);
    run_sync_status((int) $slip['run_id']);
    flash('success', 'Reopened for changes.');
    redirect(admin_url('payslips/' . $id));
}

function payslip_refresh(int $id): void
{
    $slip = payslip_get($id);
    if (!payslip_editable($slip)) {
        back(admin_url('payslips/' . $id));
    }
    $emp = db()->one('SELECT e.*, d.name AS department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id WHERE e.id = ?', [(int) $slip['employee_id']]);
    payslip_fill_from_profile($id, $emp);
    flash('success', 'Basic salary and monthly lines reloaded from the profile.');
    redirect(admin_url('payslips/' . $id));
}

function payslip_print(int $id): void
{
    $slip = payslip_get($id);
    $lines = db()->all('SELECT * FROM payslip_lines WHERE payslip_id = ? ORDER BY type, sort_order, id', [$id]);
    render('admin/payroll/print', [
        'slips' => [['slip' => $slip, 'lines' => $lines]],
        'title' => 'Payslip ' . $slip['emp_code'] . ' ' . period_label($slip['period']),
        'back' => admin_url('payslips/' . $id),
    ]);
}

/** Every payslip in a run, one per printed page, for filing or handing out. */
function payroll_print(int $id): void
{
    $run = db()->one('SELECT * FROM payroll_runs WHERE id = ?', [$id]) ?? abort(404);
    $ids = db()->column('SELECT id FROM payslips WHERE run_id = ? ORDER BY department, emp_name', [$id]);
    if (!$ids) {
        flash('error', 'This payroll has no payslips to print.');
        redirect(admin_url('payroll/' . $id));
    }
    $lines = [];
    foreach (db()->all('SELECT l.* FROM payslip_lines l JOIN payslips s ON s.id = l.payslip_id WHERE s.run_id = ? ORDER BY l.type, l.sort_order, l.id', [$id]) as $l) {
        $lines[(int) $l['payslip_id']][] = $l;
    }
    $slips = array_map(static fn ($sid) => ['slip' => payslip_get((int) $sid), 'lines' => $lines[(int) $sid] ?? []], $ids);
    render('admin/payroll/print', ['slips' => $slips, 'title' => 'Payslips ' . period_label((string) $run['period']), 'back' => admin_url('payroll/' . $id)]);
}
