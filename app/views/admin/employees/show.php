<?php
/**
 * Employee profile.
 * @var array $emp @var bool $pay @var array $components @var array $payslips @var array $files @var array $reports @var array $timeline @var array|null $application
 */
$name = employee_name($emp);
$edit = user_can('hr.manage');
$age = age_from($emp['date_of_birth']);
$lineTotals = ['allowance' => 0.0, 'deduction' => 0.0];
foreach ($components as $c) {
    $lineTotals[$c['type']] += (float) $c['amount'];
}
$dash = '<span class="muted">-</span>';
$show = static fn ($v): string => ($v === null || $v === '') ? '<span class="muted">-</span>' : e((string) $v);
?>
<a class="crumb" href="<?= e(admin_url('employees')) ?>"><?= icon('arrow-left') ?>Employees</a>
<div class="profile profile--emp">
  <?= avatar($name, 'lg') ?>
  <div>
    <div class="row"><h1><?= e($name) ?></h1><?= badge(EMPLOYEE_STATUSES[$emp['status']] ?? $emp['status'], EMPLOYEE_STATUS_COLORS[$emp['status']] ?? 'slate') ?></div>
    <div class="profile__meta">
      <span><?= icon('identification-card') ?><?= e($emp['employee_code']) ?></span>
      <?php if ($emp['designation']): ?><span><?= icon('briefcase') ?><?= e($emp['designation']) ?><?= $emp['department_name'] ? ', ' . e($emp['department_name']) : '' ?></span><?php endif; ?>
      <?php if ($emp['email']): ?><span><?= icon('envelope-simple') ?><a class="link" href="mailto:<?= e($emp['email']) ?>"><?= e($emp['email']) ?></a></span><?php endif; ?>
      <?php if ($emp['phone']): ?><span><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $emp['phone'])) ?>"><?= e($emp['phone']) ?></a></span><?php endif; ?>
      <span><?= icon('calendar-dots') ?>Joined <?= e(fmt_date($emp['join_date'])) ?> (<?= e(tenure($emp['join_date'], $emp['exit_date'])) ?>)</span>
    </div>
  </div>
  <?php if ($edit): ?>
  <div class="profile__actions">
    <a class="btn btn--primary" href="<?= e(admin_url('employees/' . $emp['id'] . '/edit')) ?>"><?= icon('pencil-simple') ?>Edit profile</a>
    <details class="dropdown">
      <summary class="btn btn--quiet"><?= icon('arrows-clockwise') ?>Change status</summary>
      <div class="dropdown__menu">
        <?php if ($emp['status'] === 'probation'): ?><button type="button" data-open-dialog="statusDialog" data-status-to="active" data-status-title="Confirm after probation"><?= icon('seal-check') ?>Confirm after probation</button><?php endif; ?>
        <?php if (in_array($emp['status'], ['probation', 'active'], true)): ?><button type="button" data-open-dialog="statusDialog" data-status-to="notice" data-status-title="Put on notice"><?= icon('hourglass') ?>Serving notice</button><?php endif; ?>
        <?php if ($emp['status'] !== 'left'): ?><button type="button" class="is-danger" data-open-dialog="statusDialog" data-status-to="left" data-status-title="Record that they left"><?= icon('sign-out') ?>Mark as left</button><?php endif; ?>
        <?php if (in_array($emp['status'], ['left', 'notice'], true)): ?><form method="post" action="<?= e(admin_url('employees/' . $emp['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="active"><button type="submit"><?= icon('arrow-left') ?>Back to active</button></form><?php endif; ?>
        <?php if (user_can('data.delete')): ?>
          <div class="dropdown__sep"></div>
          <form method="post" action="<?= e(admin_url('employees/' . $emp['id'] . '/delete')) ?>" data-confirm="Delete <?= e($name) ?>'s profile and documents? This can't be undone."><?= csrf_field() ?><button type="submit" class="is-danger"><?= icon('trash') ?>Delete profile</button></form>
        <?php endif; ?>
      </div>
    </details>
  </div>
  <?php endif; ?>
</div>

<?php if ($emp['status'] === 'probation' && $emp['probation_end']): $late = $emp['probation_end'] <= today(); ?>
  <div class="notice<?= $late ? '' : ' notice--info' ?>" style="margin-bottom:18px"><?= $late ? 'Probation ended on ' : 'Probation ends on ' ?><strong><?= e(fmt_date($emp['probation_end'])) ?></strong>.<?= $late && $edit ? ' Confirm them from Change status once you have reviewed their performance.' : '' ?></div>
<?php elseif ($emp['status'] === 'notice' && $emp['exit_date']): ?>
  <div class="notice" style="margin-bottom:18px">Serving notice. Last working day: <strong><?= e(fmt_date($emp['exit_date'])) ?></strong>.</div>
<?php elseif ($emp['status'] === 'left'): ?>
  <div class="notice notice--bad" style="margin-bottom:18px">Left on <?= e(fmt_date($emp['exit_date'])) ?><?= $emp['exit_reason'] ? ': ' . e($emp['exit_reason']) : '' ?>.</div>
<?php endif; ?>

<div class="split split--wide-side">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Job</h2></div>
      <div class="panel__body"><dl class="dl dl--2col">
        <dt>Department</dt><dd><?= $show($emp['department_name']) ?></dd>
        <dt>Designation</dt><dd><?= $show($emp['designation']) ?></dd>
        <dt>Employment type</dt><dd><?= e(EMPLOYMENT_TYPES[$emp['employment_type']] ?? $emp['employment_type']) ?></dd>
        <dt>Reports to</dt><dd><?= $emp['manager_id'] ? '<a class="link" href="' . e(admin_url('employees/' . $emp['manager_id'])) . '">' . e($emp['manager_name']) . '</a>' : $dash ?></dd>
        <dt>Work location</dt><dd><?= $show($emp['work_location']) ?></dd>
        <dt>Joining date</dt><dd><?= e(fmt_date($emp['join_date'])) ?></dd>
        <dt>Probation ends</dt><dd><?= $emp['probation_end'] ? e(fmt_date($emp['probation_end'])) : $dash ?></dd>
        <dt>Confirmed on</dt><dd><?= $emp['confirmed_on'] ? e(fmt_date($emp['confirmed_on'])) : $dash ?></dd>
      </dl></div>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Personal</h2></div>
      <div class="panel__body"><dl class="dl dl--2col">
        <dt>CNIC</dt><dd><?= $show($emp['cnic']) ?></dd>
        <dt>Date of birth</dt><dd><?= $emp['date_of_birth'] ? e(fmt_date($emp['date_of_birth'])) . ' <span class="muted">(' . $age . ')</span>' : $dash ?></dd>
        <dt>Gender</dt><dd><?= $emp['gender'] ? e(GENDERS[$emp['gender']] ?? $emp['gender']) : $dash ?></dd>
        <dt>Marital status</dt><dd><?= $emp['marital_status'] ? e(MARITAL_STATUSES[$emp['marital_status']] ?? $emp['marital_status']) : $dash ?></dd>
        <dt>Address</dt><dd class="pre"><?= e(implode("\n", array_filter([$emp['address'], $emp['city']]))) ?: $dash ?></dd>
        <dt>Emergency contact</dt><dd><?= $emp['emergency_name'] ? e($emp['emergency_name']) . ($emp['emergency_relation'] ? ' <span class="muted">(' . e($emp['emergency_relation']) . ')</span>' : '') . ($emp['emergency_phone'] ? '<br><a href="tel:' . e(preg_replace('/[^0-9+]/', '', (string) $emp['emergency_phone'])) . '">' . e($emp['emergency_phone']) . '</a>' : '') : $dash ?></dd>
      </dl>
      <?php if ($emp['notes']): ?><div class="tl__body" style="margin-top:14px"><?= e($emp['notes']) ?></div><?php endif; ?>
      </div>
    </div>

    <?php if ($pay): ?>
    <form method="post" action="<?= e(admin_url('employees/' . $emp['id'] . '/components')) ?>" class="panel" id="pay">
      <?= csrf_field() ?>
      <div class="panel__head"><h2>Pay</h2><span class="muted small"><?= $emp['bank_name'] ? e($emp['bank_name']) . ($emp['bank_iban'] ? ', ' . e($emp['bank_iban']) : '') : 'No bank details yet' ?></span></div>
      <div class="panel__body">
        <p class="help" style="margin-bottom:14px">Recurring monthly lines. Each new payslip starts with the basic salary and these; you can still change a single month's payslip.</p>
        <?php partial('admin/partials/line-editor', ['prefix' => 'comp', 'lines' => $components, 'basic' => (float) $emp['basic_salary'], 'currency' => (string) $emp['currency']]); ?>
        <div class="row" style="margin-top:16px"><button class="btn btn--primary btn--sm" type="submit">Save monthly lines</button><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('employees/' . $emp['id'] . '/edit')) ?>">Change basic salary or bank</a></div>
      </div>
    </form>

    <div class="panel">
      <div class="panel__head"><h2>Payslips</h2><?php if (user_can('payroll.manage')): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('payroll/new') . '?scope=employee&employee=' . $emp['id']) ?>">Run payroll for <?= e($emp['first_name']) ?></a><?php endif; ?></div>
      <?php if (!$payslips): ?><div class="empty empty--sm"><p>No payslips yet.</p></div><?php else: ?>
      <div class="table-wrap"><table class="tbl">
        <thead><tr><th>Month</th><th class="num">Gross</th><th class="num">Deductions</th><th class="num">Net</th><th>Status</th><th></th></tr></thead>
        <tbody><?php foreach ($payslips as $s): ?>
          <tr><td><a class="t-strong" href="<?= e(admin_url('payslips/' . $s['id'])) ?>"><?= e(period_label($s['period'])) ?></a></td><td class="num"><?= e(fmt_money($s['gross'], $s['currency'])) ?></td><td class="num"><?= e(fmt_money($s['deductions'], $s['currency'])) ?></td><td class="num t-strong"><?= e(fmt_money($s['net'], $s['currency'])) ?></td><td><?= badge(PAYROLL_STATUSES[$s['run_status'] === 'paid' ? 'paid' : $s['status']] ?? $s['status'], PAYROLL_STATUS_COLORS[$s['run_status'] === 'paid' ? 'paid' : $s['status']] ?? 'slate') ?></td><td class="t-right"><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('payslips/' . $s['id'] . '/print')) ?>" target="_blank" rel="noopener"><?= icon('file-text') ?>Payslip</a></td></tr>
        <?php endforeach; ?></tbody>
      </table></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="panel" id="history">
      <div class="panel__head"><h2>History</h2><span class="muted">Status changes, notes and documents</span></div>
      <div class="panel__body">
        <?php if ($edit): ?>
        <form method="post" action="<?= e(admin_url('employees/' . $emp['id'] . '/note')) ?>" class="composer" style="margin-bottom:22px">
          <?= csrf_field() ?>
          <div class="composer__kinds" role="radiogroup" aria-label="What are you logging?">
            <?php foreach (['note' => 'Note', 'meeting' => 'Meeting', 'call' => 'Call'] as $k => $label): ?><label><input type="radio" name="kind" value="<?= $k ?>"<?= $k === 'note' ? ' checked' : '' ?>><?= icon(activity_icon($k)) ?><?= e($label) ?></label><?php endforeach; ?>
          </div>
          <textarea class="textarea" name="body" rows="2" placeholder="Performance review, a raise agreed, a warning issued" aria-label="Details" required></textarea>
          <div><button class="btn btn--primary btn--sm" type="submit">Save</button></div>
        </form>
        <?php endif; ?>
        <?php partial('admin/partials/timeline', ['items' => $timeline]); ?>
      </div>
    </div>
  </div>

  <aside class="stack">
    <?php if ($pay): ?>
    <div class="panel paycard">
      <div class="panel__body">
        <p class="paycard__label">Monthly net pay</p>
        <p class="paycard__value"><?= e(fmt_money((float) $emp['basic_salary'] + $lineTotals['allowance'] - $lineTotals['deduction'], $emp['currency'])) ?></p>
        <dl class="paycard__rows">
          <dt>Basic</dt><dd><?= e(fmt_money($emp['basic_salary'], $emp['currency'])) ?></dd>
          <dt>Allowances</dt><dd>+ <?= e(number_format($lineTotals['allowance'], 2)) ?></dd>
          <dt>Deductions</dt><dd>- <?= e(number_format($lineTotals['deduction'], 2)) ?></dd>
          <?php if ($emp['tax_number']): ?><dt>NTN</dt><dd><?= e($emp['tax_number']) ?></dd><?php endif; ?>
        </dl>
      </div>
    </div>
    <?php endif; ?>

    <div id="documents"><?php partial('admin/partials/files-panel', [
        'files' => $files, 'title' => 'Documents', 'canManage' => $edit,
        'uploadUrl' => $edit ? admin_url('employees/' . $emp['id'] . '/files') : null,
        'kinds' => EMPLOYEE_FILE_KINDS, 'kindLabels' => EMPLOYEE_FILE_LABELS,
    ]); ?></div>

    <?php if ($reports): ?>
    <div class="panel">
      <div class="panel__head"><h2>Direct reports</h2><span class="muted"><?= count($reports) ?></span></div>
      <div class="list"><?php foreach ($reports as $r): ?>
        <div class="list__item"><?= avatar(employee_name($r), 'sm') ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('employees/' . $r['id'])) ?>"><?= e(employee_name($r)) ?></a><span class="list__sub"><?= e((string) $r['designation']) ?></span></div></div>
      <?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <?php if ($application): ?>
    <div class="panel">
      <div class="panel__head"><h2>Hiring record</h2></div>
      <div class="panel__body small">
        Applied for <a class="link" href="<?= e(admin_url('applications/' . $application['id'])) ?>"><?= e($application['title']) ?></a> on <?= e(fmt_date($application['applied_at'])) ?><?= $application['hired_at'] ? ', hired ' . e(fmt_date($application['hired_at'])) : '' ?>. Their CV, interview scorecards and offer stay there.
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<?php if ($edit): ?>
<dialog class="modal" id="statusDialog" aria-labelledby="statusTitle">
  <form method="post" action="<?= e(admin_url('employees/' . $emp['id'] . '/status')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="status" value="" data-status-field>
    <div class="modal__head"><h2 id="statusTitle" data-status-heading>Change status</h2></div>
    <div class="modal__body">
      <div class="field"><label for="st-effective">Effective date</label><input class="input" id="st-effective" type="date" name="effective" value="<?= e(today()) ?>"><p class="help">For notice, this is their last working day.</p></div>
      <div class="field" data-status-only="left"><label for="st-reason">Reason for leaving</label><select class="select" id="st-reason" name="reason"><?php foreach (EXIT_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit">Save</button></div>
  </form>
</dialog>
<?php endif; ?>
