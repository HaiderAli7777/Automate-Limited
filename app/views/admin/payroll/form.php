<?php /** @var string $period @var string $scope @var array $preview */
$new = array_filter($preview, static fn ($e) => !$e['existing_run']);
$done = array_filter($preview, static fn ($e) => $e['existing_run']);
$cur = (string) setting('default_currency', 'PKR');
$total = array_sum(array_map(static fn ($e) => (float) $e['basic_salary'], $new));
?>
<a class="crumb" href="<?= e(admin_url('payroll')) ?>"><?= icon('arrow-left') ?>Payroll</a>
<div class="phead"><div><h1>Run payroll</h1><p class="phead__sub">Choose the month and who to pay. Payslips are created as drafts you can adjust before confirming.</p></div></div>

<div class="split">
  <form method="post" action="<?= e(admin_url('payroll/new')) ?>" class="panel" data-payroll-form>
    <?= csrf_field() ?>
    <div class="panel__body">
      <fieldset class="fieldset">
        <legend>Month and pay date</legend>
        <div class="grid-2">
          <div class="field"><label for="pr-period">Month</label><input class="input" id="pr-period" type="month" name="period" value="<?= e($period) ?>" required data-preview-field></div>
          <div class="field"><label for="pr-paydate">Pay date <span class="opt">(optional)</span></label><input class="input" id="pr-paydate" type="date" name="pay_date" value="<?= e(date('Y-m-t', strtotime($period . '-01'))) ?>"></div>
        </div>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Who to pay</legend>
        <div class="rolecards rolecards--3" role="radiogroup" aria-label="Who to pay">
          <?php foreach (['all' => ['users-three', 'All employees', 'Everyone employed during the month'], 'department' => ['buildings', 'One department', 'Only the people in a department'], 'employee' => ['user-circle', 'One employee', 'A single person, for example a new joiner']] as $k => [$ic, $label, $help]): ?>
            <label class="rolecard"><input type="radio" name="scope" value="<?= $k ?>"<?= checked($scope === $k) ?> data-preview-field><span class="rolecard__ic"><?= icon($ic) ?></span><span class="rolecard__text"><b><?= e($label) ?></b><span><?= e($help) ?></span></span></label>
          <?php endforeach; ?>
        </div>
        <div class="field" data-scope-only="department"<?= $scope === 'department' ? '' : ' hidden' ?>><label for="pr-dept">Department</label><select class="select" id="pr-dept" name="department" data-preview-field><option value="">Choose a department</option><?= options(departments(), input('department')) ?></select></div>
        <div class="field" data-scope-only="employee"<?= $scope === 'employee' ? '' : ' hidden' ?>><label for="pr-emp">Employee</label><select class="select" id="pr-emp" name="employee" data-preview-field><?= employee_options(input_int('employee') ?: null, 'Choose an employee') ?></select></div>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Note</legend>
        <?= ft('notes', 'Note for this run', '', ['optional' => true, 'attrs' => ['rows' => 2], 'placeholder' => 'Eid bonus included']) ?>
      </fieldset>
      <div class="form-actions"><button class="btn btn--primary" type="submit"<?= $new ? '' : ' disabled' ?>><?= icon('check') ?>Create <?= $new ? plural(count($new), 'payslip') : 'payslips' ?></button><a class="btn btn--ghost" href="<?= e(admin_url('payroll')) ?>">Cancel</a></div>
    </div>
  </form>

  <aside class="panel">
    <div class="panel__head"><h2>Who will be paid</h2><span class="muted"><?= e(period_label($period)) ?></span></div>
    <?php if (!$preview): ?>
      <div class="empty empty--sm"><p><?= $scope === 'all' ? 'Nobody was employed in ' . e(period_label($period)) . '. Add employees first.' : 'Choose ' . ($scope === 'department' ? 'a department' : 'an employee') . ' to see who will be paid.' ?></p></div>
    <?php else: ?>
      <div class="list">
        <?php foreach ($new as $e): ?>
          <div class="list__item"><?= avatar(employee_name($e), 'sm') ?><div class="list__main"><span class="list__title"><?= e(employee_name($e)) ?></span><span class="list__sub"><?= e(implode(' · ', array_filter([$e['employee_code'], $e['department_name']]))) ?></span></div><span class="list__side num<?= (float) $e['basic_salary'] <= 0 ? ' error' : '' ?>"><?= (float) $e['basic_salary'] > 0 ? e(fmt_money($e['basic_salary'], $e['currency'])) : 'No salary' ?></span></div>
        <?php endforeach; ?>
        <?php foreach ($done as $e): ?>
          <div class="list__item is-muted"><?= avatar(employee_name($e), 'sm') ?><div class="list__main"><span class="list__title"><?= e(employee_name($e)) ?></span><span class="list__sub">Already has a payslip for this month</span></div><a class="list__side link" href="<?= e(admin_url('payroll/' . $e['existing_run'])) ?>">View</a></div>
        <?php endforeach; ?>
      </div>
      <div class="panel__foot row row--between"><span><?= plural(count($new), 'new payslip') ?></span><strong class="num">Basic <?= e(fmt_money($total, $cur)) ?></strong></div>
    <?php endif; ?>
  </aside>
</div>
