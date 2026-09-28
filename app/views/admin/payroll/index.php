<?php /** @var array $runs @var array $stats */ $cur = (string) setting('default_currency', 'PKR'); ?>
<div class="phead">
  <div><h1>Payroll</h1><p class="phead__sub">Monthly salary runs. Basic salary comes from each employee's profile; add allowances and deductions on the payslip.</p></div>
  <div class="phead__actions"><a class="btn btn--primary" href="<?= e(admin_url('payroll/new')) ?>"><?= icon('plus') ?>Run payroll</a></div>
</div>

<?php if ($stats['no_salary']): ?>
  <div class="notice" style="margin-bottom:18px"><?= plural($stats['no_salary'], 'employee') ?> <?= $stats['no_salary'] === 1 ? 'has' : 'have' ?> no basic salary on their profile, so their payslip would be zero. <a class="link" href="<?= e(admin_url('employees')) ?>">Check employee profiles</a>.</div>
<?php endif; ?>

<div class="kpis">
  <div class="kpi kpi--hero"><span class="kpi__label"><?= icon('currency-circle-dollar') ?>Net pay, <?= e(date('F Y')) ?></span><span class="kpi__value"><?= e(fmt_money($stats['month_net'], $cur)) ?></span><span class="kpi__foot"><?= plural($stats['month_slips'], 'payslip') ?> of <?= $stats['headcount'] ?> current staff</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('chart-line-up') ?>Payroll cost this year</span><span class="kpi__value"><?= e(compact_money($stats['ytd'], $cur)) ?></span><span class="kpi__foot">Gross, since January</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('note-pencil') ?>Payslips in draft</span><span class="kpi__value"><?= number_format($stats['drafts']) ?></span><span class="kpi__foot">Waiting for review and confirmation</span></div>
</div>

<div class="panel">
  <div class="panel__head"><h2>Payroll runs</h2></div>
  <?php if (!$runs): ?>
    <div class="empty"><?= icon('currency-circle-dollar') ?><h3>No payroll yet.</h3><p>Pick a month and pay everyone, one department or a single employee. Each payslip starts from the basic salary on their profile.</p><a class="btn btn--primary" href="<?= e(admin_url('payroll/new')) ?>">Run payroll</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Run</th><th>For</th><th class="num">People</th><th class="num">Gross</th><th class="num">Deductions</th><th class="num">Net pay</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($runs as $r): ?>
      <tr>
        <td><a class="t-strong" href="<?= e(admin_url('payroll/' . $r['id'])) ?>"><?= e(period_label($r['period'])) ?></a><span class="t-sub"><?= $r['pay_date'] ? 'Pay date ' . e(fmt_date($r['pay_date'])) : 'Created ' . e(fmt_date($r['created_at'])) ?></span></td>
        <td><?= e(match ($r['scope']) { 'department' => (string) $r['department_name'], 'employee' => (string) $r['employee_name'], default => 'All employees' }) ?></td>
        <td class="num"><?= (int) $r['slips'] ?></td>
        <td class="num nowrap"><?= e(fmt_money($r['gross'], $r['currency'] ?: $cur)) ?></td>
        <td class="num nowrap muted"><?= e(fmt_money($r['deductions'], $r['currency'] ?: $cur)) ?></td>
        <td class="num nowrap t-strong"><?= e(fmt_money($r['net'], $r['currency'] ?: $cur)) ?></td>
        <td><?= badge(PAYROLL_STATUSES[$r['status']] ?? $r['status'], PAYROLL_STATUS_COLORS[$r['status']] ?? 'slate') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
