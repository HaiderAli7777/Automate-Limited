<?php
/* Employees, departments and payroll: shared rules. */
declare(strict_types=1);

const EMPLOYEE_STATUSES = ['probation' => 'On probation', 'active' => 'Active', 'notice' => 'Serving notice', 'left' => 'Left'];
const EMPLOYEE_STATUS_COLORS = ['probation' => 'amber', 'active' => 'green', 'notice' => 'orange', 'left' => 'slate'];
/** Statuses that still count towards headcount and payroll. */
const EMPLOYED = ['probation', 'active', 'notice'];
const GENDERS = ['' => 'Not stated', 'female' => 'Female', 'male' => 'Male', 'other' => 'Other'];
const MARITAL_STATUSES = ['' => 'Not stated', 'single' => 'Single', 'married' => 'Married', 'other' => 'Other'];
const EXIT_REASONS = ['Resigned', 'Better opportunity', 'Relocation', 'Contract ended', 'Terminated', 'Retired', 'Other'];
const EMPLOYEE_FILE_KINDS = ['contract' => 'Contract or offer letter', 'id' => 'ID document (CNIC, passport)', 'certificate' => 'Degree or certificate', 'attachment' => 'Other document'];
const EMPLOYEE_FILE_LABELS = ['contract' => 'Contract', 'id' => 'ID', 'certificate' => 'Certificate'];

const ALLOWANCE_PRESETS = ['House rent', 'Medical', 'Transport', 'Fuel', 'Mobile', 'Overtime', 'Bonus', 'Commission', 'Arrears'];
const DEDUCTION_PRESETS = ['Income tax', 'EOBI', 'Provident fund', 'Advance', 'Loan repayment', 'Unpaid leave', 'Late arrivals'];
const PAYROLL_STATUSES = ['draft' => 'Draft', 'confirmed' => 'Confirmed', 'paid' => 'Paid'];
const PAYROLL_STATUS_COLORS = ['draft' => 'amber', 'confirmed' => 'blue', 'paid' => 'green'];

function employee_name(array $e): string
{
    return trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
}

function departments(): array
{
    static $d = null;
    return $d ??= db()->pairs('SELECT id, name FROM departments ORDER BY name');
}

/** Find a department by name, creating it when it doesn't exist yet. */
function department_id_for(string $name): ?int
{
    $name = trim($name);
    if ($name === '') {
        return null;
    }
    $id = db()->value('SELECT id FROM departments WHERE name = ?', [$name]);
    return $id ? (int) $id : db()->insert('departments', ['name' => mb_substr($name, 0, 80), 'created_at' => now(), 'updated_at' => now()]);
}

function next_employee_code(): string
{
    $prefix = (string) setting('employee_code_prefix', 'AL-');
    $n = (int) db()->value('SELECT COUNT(*) FROM employees') + 1;
    do {
        $code = $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
        $n++;
    } while (db()->value('SELECT id FROM employees WHERE employee_code = ?', [$code]));
    return $code;
}

function employee_full(int $id): ?array
{
    return db()->one(
        "SELECT e.*, d.name AS department_name, CONCAT(m.first_name, ' ', m.last_name) AS manager_name
         FROM employees e LEFT JOIN departments d ON d.id = e.department_id LEFT JOIN employees m ON m.id = e.manager_id
         WHERE e.id = ?",
        [$id]
    );
}

/** Active employees for a <select>, optionally keeping one selected even if they left. */
function employee_options(?int $selected, string $empty = 'Nobody', ?int $exclude = null): string
{
    $html = '<option value="">' . e($empty) . '</option>';
    $rows = db()->all("SELECT id, first_name, last_name, employee_code, status FROM employees WHERE status <> 'left' OR id = ? ORDER BY first_name, last_name", [(int) $selected]);
    foreach ($rows as $r) {
        if ($exclude && (int) $r['id'] === $exclude) {
            continue;
        }
        $html .= '<option value="' . (int) $r['id'] . '"' . selected($selected ?? '', $r['id']) . '>' . e(employee_name($r) . ' (' . $r['employee_code'] . ')') . '</option>';
    }
    return $html;
}

/** Whole years and months between two dates, for "tenure". */
function tenure(?string $from, ?string $to = null): string
{
    if (!$from) {
        return '';
    }
    $a = new DateTimeImmutable(substr($from, 0, 10));
    $b = new DateTimeImmutable($to ? substr($to, 0, 10) : 'today');
    if ($b < $a) {
        return 'Starts ' . fmt_date($from);
    }
    $d = $a->diff($b);
    $parts = [];
    if ($d->y) {
        $parts[] = plural($d->y, 'year');
    }
    if ($d->m || !$d->y) {
        $parts[] = $d->m ? plural($d->m, 'month') : ($d->days < 31 ? plural(max(1, $d->days), 'day') : '');
    }
    return implode(' ', array_filter($parts));
}

function age_from(?string $dob): ?int
{
    if (!$dob) {
        return null;
    }
    return (new DateTimeImmutable($dob))->diff(new DateTimeImmutable('today'))->y;
}

/* ------------------------------------------------------------------ payroll */
function period_label(string $period): string
{
    $t = strtotime($period . '-01');
    return $t ? date('F Y', $t) : $period;
}

function period_bounds(string $period): array
{
    $start = $period . '-01';
    return [$start, date('Y-m-t', strtotime($start))];
}

function valid_period(string $p): bool
{
    return (bool) preg_match('/^(20\d{2})-(0[1-9]|1[0-2])$/', $p);
}

/** Employees who should be paid for a month: joined by its end, not gone before its start. */
function payable_employees(string $period, string $scope, int $departmentId = 0, int $employeeId = 0): array
{
    [$start, $end] = period_bounds($period);
    $where = ["e.join_date <= ?", "(e.exit_date IS NULL OR e.exit_date >= ?)", "(e.status IN ('probation', 'active', 'notice') OR (e.status = 'left' AND e.exit_date >= ?))"];
    $params = [$end, $start, $start];
    if ($scope === 'department') {
        $where[] = 'e.department_id = ?';
        $params[] = $departmentId;
    } elseif ($scope === 'employee') {
        $where[] = 'e.id = ?';
        $params[] = $employeeId;
    }
    return db()->all(
        'SELECT e.*, d.name AS department_name, (SELECT s.run_id FROM payslips s WHERE s.employee_id = e.id AND s.period = ?) AS existing_run
         FROM employees e LEFT JOIN departments d ON d.id = e.department_id WHERE ' . implode(' AND ', $where) . ' ORDER BY e.first_name, e.last_name',
        array_merge([$period], $params)
    );
}

/** Add up a payslip's lines and store the totals. */
function payslip_recalc(int $id): void
{
    $slip = db()->one('SELECT basic FROM payslips WHERE id = ?', [$id]);
    if (!$slip) {
        return;
    }
    $allow = (float) db()->value("SELECT COALESCE(SUM(amount), 0) FROM payslip_lines WHERE payslip_id = ? AND type = 'allowance'", [$id]);
    $deduct = (float) db()->value("SELECT COALESCE(SUM(amount), 0) FROM payslip_lines WHERE payslip_id = ? AND type = 'deduction'", [$id]);
    $gross = round((float) $slip['basic'] + $allow, 2);
    db()->update('payslips', [
        'allowances' => round($allow, 2), 'deductions' => round($deduct, 2), 'gross' => $gross,
        'net' => round($gross - $deduct, 2), 'updated_at' => now(),
    ], 'id = ?', [$id]);
}

/** Copy an employee's basic salary and recurring components into a payslip. */
function payslip_fill_from_profile(int $slipId, array $emp): void
{
    db()->update('payslips', [
        'basic' => (float) $emp['basic_salary'], 'currency' => $emp['currency'] ?: (string) setting('default_currency', 'PKR'),
        'emp_name' => employee_name($emp), 'emp_code' => $emp['employee_code'], 'designation' => $emp['designation'],
        'department' => $emp['department_name'] ?? null, 'bank_iban' => $emp['bank_iban'], 'updated_at' => now(),
    ], 'id = ?', [$slipId]);
    db()->delete('payslip_lines', 'payslip_id = ?', [$slipId]);
    $i = 0;
    foreach (db()->all('SELECT * FROM employee_components WHERE employee_id = ? ORDER BY type, id', [(int) $emp['id']]) as $c) {
        db()->insert('payslip_lines', ['payslip_id' => $slipId, 'type' => $c['type'], 'label' => $c['label'], 'amount' => $c['amount'], 'sort_order' => $i++]);
    }
    payslip_recalc($slipId);
}

function run_totals(int $runId): array
{
    $r = db()->one(
        "SELECT COUNT(*) AS n, COALESCE(SUM(basic), 0) AS basic, COALESCE(SUM(allowances), 0) AS allowances, COALESCE(SUM(deductions), 0) AS deductions,
            COALESCE(SUM(gross), 0) AS gross, COALESCE(SUM(net), 0) AS net, SUM(status = 'confirmed') AS confirmed, MIN(currency) AS currency
         FROM payslips WHERE run_id = ?",
        [$runId]
    );
    return array_map(static fn ($v) => $v ?? 0, $r ?? []);
}

/** Keep the run's status in step with its payslips. */
function run_sync_status(int $runId): void
{
    $run = db()->one('SELECT status FROM payroll_runs WHERE id = ?', [$runId]);
    if (!$run || $run['status'] === 'paid') {
        return;
    }
    $t = run_totals($runId);
    $all = (int) $t['n'] > 0 && (int) $t['confirmed'] === (int) $t['n'];
    if ($all && $run['status'] !== 'confirmed') {
        db()->update('payroll_runs', ['status' => 'confirmed', 'confirmed_by' => auth_id(), 'confirmed_at' => now(), 'updated_at' => now()], 'id = ?', [$runId]);
    } elseif (!$all && $run['status'] === 'confirmed') {
        db()->update('payroll_runs', ['status' => 'draft', 'confirmed_by' => null, 'confirmed_at' => null, 'updated_at' => now()], 'id = ?', [$runId]);
    }
}

/** Whole-number amount in words for the payslip, using thousand / million. */
function amount_in_words(float $amount): string
{
    $n = (int) round($amount);
    if ($n === 0) {
        return 'Zero';
    }
    $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    $chunk = static function (int $x) use ($ones, $tens): string {
        $out = [];
        if ($x >= 100) {
            $out[] = $ones[intdiv($x, 100)] . ' hundred';
            $x %= 100;
        }
        if ($x >= 20) {
            $out[] = $tens[intdiv($x, 10)] . ($x % 10 ? ' ' . $ones[$x % 10] : '');
        } elseif ($x > 0) {
            $out[] = $ones[$x];
        }
        return implode(' ', $out);
    };
    $scales = [1000000000 => 'billion', 1000000 => 'million', 1000 => 'thousand'];
    $words = [];
    if ($n < 0) {
        $words[] = 'minus';
        $n = -$n;
    }
    foreach ($scales as $size => $name) {
        if ($n >= $size) {
            $words[] = $chunk(intdiv($n, $size)) . ' ' . $name;
            $n %= $size;
        }
    }
    if ($n > 0) {
        $words[] = $chunk($n);
    }
    return ucfirst(implode(' ', $words));
}

function employee_vars(array $slip, array $emp): array
{
    $cur = (string) $slip['currency'];
    return [
        'employee_name' => employee_name($emp), 'employee_first_name' => (string) $emp['first_name'],
        'period' => period_label((string) $slip['period']), 'basic' => fmt_money($slip['basic'], $cur),
        'allowances' => fmt_money($slip['allowances'], $cur), 'deductions' => fmt_money($slip['deductions'], $cur),
        'net' => fmt_money($slip['net'], $cur), 'pay_date' => fmt_date($slip['pay_date'] ?? null) ?: 'to be confirmed',
        'company_name' => company_name(),
    ];
}
