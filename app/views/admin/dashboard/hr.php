<?php
/** @var array $p @var array $m @var bool $pay @var string $cur @var array $funnel @var array $byDept @var array $byType @var array $payTrend @var array $payDept
 *  @var string|null $latestPeriod @var array $probationDue @var array $toOnboard @var array $poolDue @var array $recentRuns @var array $weekly */
?>
<div class="phead">
  <div><h1>HR dashboard</h1><p class="phead__sub">Hiring, headcount and payroll in one view.</p></div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('employees')) ?>"><?= icon('identification-card') ?>Employees</a>
    <?php if ($pay): ?><a class="btn btn--primary" href="<?= e(admin_url('payroll/new')) ?>"><?= icon('currency-circle-dollar') ?>Run payroll</a><?php endif; ?>
  </div>
</div>
<?= period_filter($p, 'dashboard/hr') ?>

<div class="kpis">
  <?= kpi('Active employees', number_format($m['headcount']), 'users-three', null, true, '+' . $m['joiners'] . ' joined, ' . $m['leavers'] . ' left, ' . strtolower($p['label']), admin_url('employees'), 'kpi--hero') ?>
  <?= kpi('New applications', number_format($m['applied']), 'user-plus', pct_change($m['applied'], $m['applied_prev']), true, strtolower($p['label']), admin_url('candidates')) ?>
  <?= kpi('Hired', number_format($m['hired']), 'handshake', pct_change($m['hired'], $m['hired_prev']), true, strtolower($p['label']), admin_url('candidates') . '?status=hired') ?>
  <?php if ($pay): ?>
    <?= kpi('Net payroll, ' . date('M Y'), e(compact_money($m['month_net'], $cur)), 'currency-circle-dollar', pct_change($m['month_net'], $m['month_prev']), false, $m['month_net'] > 0 ? 'vs last month' : 'Not run yet this month', admin_url('payroll')) ?>
    <?= kpi('Monthly basic salaries', e(compact_money($m['monthly_basic'], $cur)), 'chart-bar', null, true, 'Current staff, before allowances', admin_url('employees')) ?>
  <?php endif; ?>
  <?= kpi('On probation', number_format($m['probation']), 'hourglass', null, true, count($probationDue) . ' due in the next 30 days', admin_url('employees') . '?status=probation') ?>
  <?= kpi('Talent pool', number_format($m['pool']), 'user-list', null, true, 'Good candidates kept on file', admin_url('talent-pool')) ?>
  <?php if (!$pay): // payroll users get two pay tiles instead; the onboarding list below still shows these ?>
    <?= kpi('Hires to onboard', number_format($m['ready']), 'identification-card', null, true, 'Hired, no employee profile yet', admin_url('candidates') . '?status=hired') ?>
    <?= kpi('Open jobs', number_format($m['open_jobs']), 'briefcase', null, true, 'Live on the careers page', admin_url('jobs')) ?>
  <?php endif; ?>
</div>

<div class="dash-grid">
  <div class="panel span-7">
    <div class="panel__head"><h2>Applications per week</h2><span class="muted">Last 12 weeks, <?= $m['open_jobs'] ?> open jobs</span></div>
    <div class="panel__body"><?= chart_columns($weekly, 'Applications') ?></div>
  </div>
  <div class="panel span-5">
    <div class="panel__head"><h2>From application to hire</h2><span class="muted">Applied <?= e(strtolower($p['label'])) ?></span></div>
    <div class="panel__body"><?= chart_hbars($funnel, 'Step', 'No applications in this period.') ?></div>
  </div>

  <div class="panel span-6">
    <div class="panel__head"><h2>Headcount by department</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('departments')) ?>">Departments</a></div>
    <div class="panel__body"><?= chart_hbars($byDept, 'Department', 'No employees yet. Convert a hire or add one.') ?></div>
  </div>
  <div class="panel span-6">
    <div class="panel__head"><h2>Employment type</h2></div>
    <div class="panel__body"><?= chart_hbars($byType, 'Type', 'No employees yet.') ?></div>
  </div>

  <?php if ($pay): ?>
  <div class="panel span-7">
    <div class="panel__head"><h2>Net payroll by month</h2><span class="muted">Last 6 months, <?= e($cur) ?></span></div>
    <div class="panel__body"><?= chart_columns($payTrend, 'Net pay', ' ' . $cur, 'Month') ?></div>
  </div>
  <div class="panel span-5">
    <div class="panel__head"><h2>Payroll by department</h2><span class="muted"><?= $latestPeriod ? e(period_label($latestPeriod)) : 'No payroll yet' ?></span></div>
    <div class="panel__body"><?= chart_hbars($payDept, 'Department', 'Run payroll to see the split.') ?></div>
  </div>
  <?php endif; ?>

  <div class="panel span-4">
    <div class="panel__head"><h2>Probation reviews</h2><span class="muted">Next 30 days</span></div>
    <?php if (!$probationDue): ?><div class="empty empty--sm"><?= icon('check-circle') ?><p>No probation reviews due.</p></div><?php else: ?>
    <div class="list"><?php foreach ($probationDue as $e): $late = $e['probation_end'] < today(); ?>
      <div class="list__item"><?= avatar(employee_name($e), 'sm') ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('employees/' . $e['id'])) ?>"><?= e(employee_name($e)) ?></a><span class="list__sub"><?= e((string) $e['designation']) ?></span></div><span class="list__side<?= $late ? ' error' : '' ?>"><?= $late ? 'Overdue ' : '' ?><?= e(fmt_date($e['probation_end'], 'j M')) ?></span></div>
    <?php endforeach; ?></div>
    <?php endif; ?>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Hires to onboard</h2></div>
    <?php if (!$toOnboard): ?><div class="empty empty--sm"><?= icon('check-circle') ?><p>Every hire has an employee profile.</p></div><?php else: ?>
    <div class="list"><?php foreach ($toOnboard as $a): ?>
      <div class="list__item"><?= avatar(candidate_name($a), 'sm') ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e(candidate_name($a)) ?></a><span class="list__sub"><?= e($a['title']) ?>, hired <?= e(fmt_date($a['hired_at'], 'j M')) ?></span></div>
        <?php if (user_can('hr.manage')): ?><a class="btn btn--quiet btn--sm" href="<?= e(admin_url('employees/new') . '?application=' . $a['id']) ?>">Convert</a><?php endif; ?></div>
    <?php endforeach; ?></div>
    <?php endif; ?>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Talent pool</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('talent-pool')) ?>">All</a></div>
    <?php if (!$poolDue): ?><div class="empty empty--sm"><p>Nobody in the pool yet.</p></div><?php else: ?>
    <div class="list"><?php foreach ($poolDue as $a): $late = $a['revisit_on'] && $a['revisit_on'] <= today(); ?>
      <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e(candidate_name($a)) ?></a><span class="list__sub"><?= e($a['title']) ?><?= $a['pool_reason'] ? ' · ' . e($a['pool_reason']) : '' ?></span></div><span class="list__side<?= $late ? ' error' : '' ?>"><?= $a['revisit_on'] ? e(fmt_date($a['revisit_on'], 'j M')) : '' ?></span></div>
    <?php endforeach; ?></div>
    <?php endif; ?>
  </div>

  <?php if ($pay && $recentRuns): ?>
  <div class="panel span-12">
    <div class="panel__head"><h2>Recent payroll runs</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('payroll')) ?>">All payroll</a></div>
    <div class="list"><?php foreach ($recentRuns as $r): ?>
      <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('payroll/' . $r['id'])) ?>"><?= e($r['title']) ?></a></div><span class="list__side"><strong class="num" style="color:var(--ink)"><?= e(fmt_money($r['net'], $r['cur'] ?: $cur)) ?></strong></span><?= badge(PAYROLL_STATUSES[$r['status']] ?? $r['status'], PAYROLL_STATUS_COLORS[$r['status']] ?? 'slate') ?></div>
    <?php endforeach; ?></div>
  </div>
  <?php endif; ?>
</div>
