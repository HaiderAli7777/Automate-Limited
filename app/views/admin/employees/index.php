<?php /** @var array $rows @var array $p @var array $stats */
$status = input('status', 'current');
$pay = user_can('payroll.manage');
?>
<div class="phead">
  <div><h1>Employees</h1><p class="phead__sub">Everyone on the payroll, with their job, contact and pay details in one profile.</p></div>
  <div class="phead__actions">
    <?php if (user_can('data.export')): ?><a class="btn btn--quiet" href="<?= e(admin_url('employees/export') . qs()) ?>"><?= icon('download-simple') ?>Export CSV</a><?php endif; ?>
    <?php if (user_can('hr.manage')): ?><a class="btn btn--primary" href="<?= e(admin_url('employees/new')) ?>"><?= icon('user-plus') ?>Add employee</a><?php endif; ?>
  </div>
</div>

<div class="kpis kpis--5">
  <a class="kpi" href="<?= e(admin_url('employees')) ?>"><span class="kpi__label"><?= icon('users-three') ?>Headcount</span><span class="kpi__value"><?= number_format($stats['headcount']) ?></span><span class="kpi__foot">Active, on probation or serving notice</span></a>
  <a class="kpi" href="<?= e(admin_url('employees') . '?status=probation') ?>"><span class="kpi__label"><?= icon('hourglass') ?>On probation</span><span class="kpi__value"><?= number_format($stats['probation']) ?></span><span class="kpi__foot">Waiting for confirmation</span></a>
  <div class="kpi"><span class="kpi__label"><?= icon('user-plus') ?>Joined this month</span><span class="kpi__value"><?= number_format($stats['joiners']) ?></span><span class="kpi__foot"><?= e(date('F Y')) ?></span></div>
  <a class="kpi" href="<?= e(admin_url('employees') . '?status=left') ?>"><span class="kpi__label"><?= icon('sign-out') ?>Left this year</span><span class="kpi__value"><?= number_format($stats['leavers']) ?></span><span class="kpi__foot">Since 1 January</span></a>
  <a class="kpi" href="<?= e(admin_url('candidates') . '?status=hired') ?>"><span class="kpi__label"><?= icon('identification-card') ?>Hires to onboard</span><span class="kpi__value"><?= number_format($stats['ready']) ?></span><span class="kpi__foot">Hired, no employee profile yet</span></a>
</div>

<nav class="tabs" aria-label="Employee status">
  <?php foreach (['current' => 'Current staff', 'probation' => 'On probation', 'notice' => 'Serving notice', 'left' => 'Former staff', 'all' => 'Everyone'] as $k => $label): ?>
    <a href="<?= e(qs(['status' => $k, 'page' => null])) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
<form class="filters" method="get" action="<?= e(admin_url('employees')) ?>" data-autosubmit>
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input class="input" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Name, code, email, phone, designation" aria-label="Search employees">
  <select class="select" name="department" aria-label="Department"><option value="">All departments</option><?= options(departments(), input('department')) ?></select>
  <select class="select" name="type" aria-label="Employment type"><option value="">Any type</option><?= options(EMPLOYMENT_TYPES, input('type')) ?></select>
  <?php if (input('q') !== '' || input('department') !== '' || input('type') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('employees') . '?status=' . $status) ?>">Clear</a><?php endif; ?>
</form>

<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('identification-card') ?><h3>No employees here yet.</h3><p>Add people directly, or open a hired candidate and choose <b>Convert to employee</b>. Their details carry over from the application.</p><?php if (user_can('hr.manage')): ?><a class="btn btn--primary" href="<?= e(admin_url('employees/new')) ?>">Add employee</a><?php endif; ?></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Employee</th><th>Department</th><th>Type</th><th>Joined</th><th>Reports to</th><th>Status</th><?php if ($pay): ?><th class="num">Basic salary</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $name = employee_name($r); ?>
      <tr>
        <td><div class="person"><?= avatar($name) ?><span><a class="t-strong" href="<?= e(admin_url('employees/' . $r['id'])) ?>"><?= e($name) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$r['employee_code'], $r['designation']]))) ?></span></span></div></td>
        <td><?= $r['department_name'] ? e($r['department_name']) : '<span class="muted">-</span>' ?></td>
        <td class="muted"><?= e(EMPLOYMENT_TYPES[$r['employment_type']] ?? $r['employment_type']) ?></td>
        <td class="nowrap"><?= e(fmt_date($r['join_date'])) ?><span class="t-sub"><?= e(tenure($r['join_date'], $r['exit_date'])) ?></span></td>
        <td><?= $r['manager_name'] ? e($r['manager_name']) : '<span class="muted">-</span>' ?></td>
        <td><?= badge(EMPLOYEE_STATUSES[$r['status']] ?? $r['status'], EMPLOYEE_STATUS_COLORS[$r['status']] ?? 'slate') ?><?php if ($r['status'] === 'probation' && $r['probation_end']): ?><span class="t-sub<?= $r['probation_end'] < today() ? ' error' : '' ?>">Ends <?= e(fmt_date($r['probation_end'], 'j M')) ?></span><?php endif; ?></td>
        <?php if ($pay): ?><td class="num nowrap"><?= (float) $r['basic_salary'] > 0 ? e(fmt_money($r['basic_salary'], $r['currency'])) : '<span class="muted">Not set</span>' ?></td><?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
