<?php /** @var array $run @var array $slips @var array $totals @var array $timeline */
$cur = (string) ($totals['currency'] ?: setting('default_currency', 'PKR'));
$drafts = count(array_filter($slips, static fn ($s) => $s['status'] === 'draft'));
$locked = $run['status'] === 'paid';
?>
<a class="crumb" href="<?= e(admin_url('payroll')) ?>"><?= icon('arrow-left') ?>Payroll</a>
<div class="phead">
  <div>
    <div class="row"><h1><?= e(period_label($run['period'])) ?></h1><?= badge(PAYROLL_STATUSES[$run['status']] ?? $run['status'], PAYROLL_STATUS_COLORS[$run['status']] ?? 'slate') ?></div>
    <p class="phead__sub"><?= e(match ($run['scope']) { 'department' => 'Department: ' . $run['department_name'], 'employee' => 'One employee', default => 'All employees' }) ?><?= $run['pay_date'] ? ' · Pay date ' . e(fmt_date($run['pay_date'])) : '' ?> · Created by <?= e((string) ($run['created_name'] ?: 'System')) ?><?= $run['confirmed_at'] ? ' · Confirmed ' . e(fmt_date($run['confirmed_at'])) : '' ?></p>
  </div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('payroll/' . $run['id'] . '/export')) ?>"><?= icon('download-simple') ?>Bank sheet (CSV)</a>
    <?php if ($slips): ?><a class="btn btn--quiet" href="<?= e(admin_url('payroll/' . $run['id'] . '/print')) ?>" target="_blank" rel="noopener"><?= icon('printer') ?>Print all</a><?php endif; ?>
    <?php if ($drafts && !$locked): ?>
      <form method="post" action="<?= e(admin_url('payroll/' . $run['id'] . '/confirm')) ?>" data-confirm="Confirm all <?= $drafts ?> draft payslips? You can still reopen one until the run is marked as paid."><?= csrf_field() ?><button class="btn btn--primary" type="submit"><?= icon('checks') ?>Confirm all (<?= $drafts ?>)</button></form>
    <?php elseif ($run['status'] === 'confirmed'): ?>
      <form method="post" action="<?= e(admin_url('payroll/' . $run['id'] . '/paid')) ?>" data-confirm="Mark this payroll as paid? Payslips will be locked."><?= csrf_field() ?><button class="btn btn--primary" type="submit"><?= icon('check-circle') ?>Mark as paid</button></form>
    <?php endif; ?>
    <details class="dropdown">
      <summary class="btn btn--quiet btn--icon" aria-label="More actions"><?= icon('dots-three') ?></summary>
      <div class="dropdown__menu">
        <?php if ($run['status'] !== 'draft'): ?>
        <form method="post" action="<?= e(admin_url('payroll/' . $run['id'] . '/email')) ?>" data-confirm="Email each employee their confirmed payslip?"><?= csrf_field() ?><button type="submit"><?= icon('envelope-simple') ?>Email payslips</button></form>
        <?php endif; ?>
        <?php if ($run['status'] === 'draft'): ?>
        <form method="post" action="<?= e(admin_url('payroll/' . $run['id'] . '/delete')) ?>" data-confirm="Delete this draft payroll and its payslips?"><?= csrf_field() ?><button type="submit" class="is-danger"><?= icon('trash') ?>Delete draft</button></form>
        <?php endif; ?>
      </div>
    </details>
  </div>
</div>

<ol class="runsteps" aria-label="Payroll progress">
  <li class="is-done"><span><?= icon('check') ?></span>Payslips created</li>
  <li class="<?= $drafts === 0 ? 'is-done' : 'is-current' ?>"><span><?= $drafts === 0 ? icon('check') : '2' ?></span>Review and confirm<?= $drafts ? ' <em>' . $drafts . ' left</em>' : '' ?></li>
  <li class="<?= $locked ? 'is-done' : ($run['status'] === 'confirmed' ? 'is-current' : '') ?>"><span><?= $locked ? icon('check') : '3' ?></span>Pay and lock</li>
</ol>

<div class="kpis">
  <div class="kpi"><span class="kpi__label"><?= icon('users-three') ?>Employees</span><span class="kpi__value"><?= (int) $totals['n'] ?></span><span class="kpi__foot"><?= (int) $totals['confirmed'] ?> confirmed</span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('trend-up') ?>Gross pay</span><span class="kpi__value kpi__value--sm"><?= e(fmt_money($totals['gross'], $cur)) ?></span><span class="kpi__foot">Basic <?= e(compact_money($totals['basic'], $cur)) ?> + allowances <?= e(compact_money($totals['allowances'], $cur)) ?></span></div>
  <div class="kpi"><span class="kpi__label"><?= icon('trend-down') ?>Deductions</span><span class="kpi__value kpi__value--sm"><?= e(fmt_money($totals['deductions'], $cur)) ?></span><span class="kpi__foot">Tax, advances, leave and more</span></div>
  <div class="kpi kpi--accent"><span class="kpi__label"><?= icon('currency-circle-dollar') ?>Net pay to transfer</span><span class="kpi__value kpi__value--sm"><?= e(fmt_money($totals['net'], $cur)) ?></span><span class="kpi__foot">The total of the bank sheet</span></div>
</div>

<div class="panel">
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Employee</th><th>Department</th><th class="num">Basic</th><th class="num">Allowances</th><th class="num">Deductions</th><th class="num">Net pay</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
    <tbody>
    <?php foreach ($slips as $s): ?>
      <tr>
        <td><div class="person"><?= avatar($s['emp_name'], 'sm') ?><span><a class="t-strong" href="<?= e(admin_url('payslips/' . $s['id'])) ?>"><?= e($s['emp_name']) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$s['emp_code'], $s['designation']]))) ?></span></span></div></td>
        <td class="muted"><?= e((string) $s['department']) ?: '-' ?></td>
        <td class="num"><?= e(number_format((float) $s['basic'], 2)) ?></td>
        <td class="num"><?= (float) $s['allowances'] > 0 ? '+ ' . e(number_format((float) $s['allowances'], 2)) : '<span class="muted">-</span>' ?></td>
        <td class="num"><?= (float) $s['deductions'] > 0 ? '- ' . e(number_format((float) $s['deductions'], 2)) : '<span class="muted">-</span>' ?></td>
        <td class="num t-strong nowrap"><?= e(fmt_money($s['net'], $s['currency'])) ?></td>
        <td><?= badge($locked ? 'Paid' : (PAYROLL_STATUSES[$s['status']] ?? $s['status']), $locked ? 'green' : (PAYROLL_STATUS_COLORS[$s['status']] ?? 'slate')) ?><?= $s['emailed_at'] ? '<span class="t-sub">Emailed</span>' : '' ?></td>
        <td class="t-right nowrap">
          <a class="btn btn--ghost btn--sm" href="<?= e(admin_url('payslips/' . $s['id'])) ?>"><?= $s['status'] === 'draft' && !$locked ? 'Review' : 'Open' ?></a>
          <a class="btn btn--ghost btn--icon btn--sm" href="<?= e(admin_url('payslips/' . $s['id'] . '/print')) ?>" target="_blank" rel="noopener" aria-label="Print payslip for <?= e($s['emp_name']) ?>"><?= icon('printer') ?></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="2">Total</th><th class="num"><?= e(number_format((float) $totals['basic'], 2)) ?></th><th class="num">+ <?= e(number_format((float) $totals['allowances'], 2)) ?></th><th class="num">- <?= e(number_format((float) $totals['deductions'], 2)) ?></th><th class="num"><?= e(fmt_money($totals['net'], $cur)) ?></th><th colspan="2"></th></tr></tfoot>
  </table></div>
</div>

<?php if ($run['notes'] || $timeline): ?>
<div class="panel">
  <div class="panel__head"><h2>History</h2></div>
  <div class="panel__body">
    <?php if ($run['notes']): ?><p class="notice notice--info" style="margin-bottom:16px"><?= e($run['notes']) ?></p><?php endif; ?>
    <?php partial('admin/partials/timeline', ['items' => $timeline]); ?>
  </div>
</div>
<?php endif; ?>
