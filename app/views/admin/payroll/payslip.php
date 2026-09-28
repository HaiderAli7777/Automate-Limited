<?php /** @var array $slip @var array $lines @var int|null $prev @var int|null $next @var int $position @var int $count */
$editable = $slip['status'] === 'draft' && $slip['run_status'] !== 'paid';
$status = $slip['run_status'] === 'paid' ? 'paid' : $slip['status'];
?>
<a class="crumb" href="<?= e(admin_url('payroll/' . $slip['run_id'])) ?>"><?= icon('arrow-left') ?><?= e(period_label($slip['period'])) ?> payroll</a>
<div class="profile">
  <?= avatar($slip['emp_name'], 'lg') ?>
  <div>
    <div class="row"><h1><?= e($slip['emp_name']) ?></h1><?= badge(PAYROLL_STATUSES[$status] ?? $status, PAYROLL_STATUS_COLORS[$status] ?? 'slate') ?></div>
    <div class="profile__meta">
      <span><?= icon('identification-card') ?><a class="link" href="<?= e(admin_url('employees/' . $slip['employee_id'])) ?>"><?= e($slip['emp_code']) ?></a></span>
      <?php if ($slip['designation']): ?><span><?= icon('briefcase') ?><?= e($slip['designation']) ?><?= $slip['department'] ? ', ' . e($slip['department']) : '' ?></span><?php endif; ?>
      <span><?= icon('calendar-dots') ?><?= e(period_label($slip['period'])) ?></span>
      <?php if ($slip['bank_iban']): ?><span><?= icon('buildings') ?><?= e(trim(($slip['bank_name'] ?? '') . ' ' . $slip['bank_iban'])) ?></span><?php endif; ?>
    </div>
  </div>
  <div class="profile__actions">
    <span class="muted small"><?= $position ?> of <?= $count ?></span>
    <?php if ($prev): ?><a class="btn btn--quiet btn--icon" href="<?= e(admin_url('payslips/' . $prev)) ?>" aria-label="Previous payslip"><?= icon('arrow-left') ?></a><?php endif; ?>
    <?php if ($next): ?><a class="btn btn--quiet btn--icon" href="<?= e(admin_url('payslips/' . $next)) ?>" aria-label="Next payslip"><?= icon('arrow-right') ?></a><?php endif; ?>
    <a class="btn btn--quiet" href="<?= e(admin_url('payslips/' . $slip['id'] . '/print')) ?>" target="_blank" rel="noopener"><?= icon('printer') ?>Print or save PDF</a>
  </div>
</div>

<div class="split">
  <form method="post" action="<?= e(admin_url('payslips/' . $slip['id'])) ?>" class="panel">
    <?= csrf_field() ?>
    <div class="panel__head"><h2>Earnings and deductions</h2><?= $editable ? '<span class="muted small">Draft. Changes are saved when you press Save</span>' : '<span class="muted small">Confirmed. Reopen to change</span>' ?></div>
    <div class="panel__body">
      <?php partial('admin/partials/line-editor', ['prefix' => 'line', 'lines' => $lines, 'basic' => (float) $slip['basic'], 'currency' => (string) $slip['currency'], 'readonly' => !$editable]); ?>
      <?php if ($editable): ?>
        <?= ft('notes', 'Note on this payslip', (string) $slip['notes'], ['optional' => true, 'attrs' => ['rows' => 2], 'placeholder' => 'For example: two days of unpaid leave, bonus for the Q3 launch']) ?>
        <div class="form-actions">
          <button class="btn btn--primary" type="submit" name="then" value="confirm"><?= icon('check') ?>Save and confirm</button>
          <button class="btn btn--quiet" type="submit" name="then" value="save">Save draft</button>
        </div>
      <?php elseif ($slip['notes']): ?>
        <p class="tl__body"><?= e($slip['notes']) ?></p>
      <?php endif; ?>
    </div>
  </form>

  <aside class="stack">
    <div class="panel paycard">
      <div class="panel__body">
        <p class="paycard__label">Net pay</p>
        <p class="paycard__value"><?= e(fmt_money($slip['net'], $slip['currency'])) ?></p>
        <p class="paycard__words"><?= e(amount_in_words((float) $slip['net'])) ?> only</p>
        <dl class="paycard__rows">
          <dt>Basic</dt><dd><?= e(number_format((float) $slip['basic'], 2)) ?></dd>
          <dt>Allowances</dt><dd>+ <?= e(number_format((float) $slip['allowances'], 2)) ?></dd>
          <dt>Gross</dt><dd><?= e(number_format((float) $slip['gross'], 2)) ?></dd>
          <dt>Deductions</dt><dd>- <?= e(number_format((float) $slip['deductions'], 2)) ?></dd>
        </dl>
      </div>
    </div>
    <div class="panel">
      <div class="panel__body stack-sm">
        <?php if ($editable): ?>
          <form method="post" action="<?= e(admin_url('payslips/' . $slip['id'] . '/refresh')) ?>" data-confirm="Reload the basic salary and monthly lines from <?= e($slip['first_name']) ?>'s profile? Changes made on this payslip will be replaced."><?= csrf_field() ?><button class="btn btn--quiet btn--sm btn--block" type="submit"><?= icon('arrows-clockwise') ?>Reload from profile</button></form>
          <p class="help">Changed the basic salary on the profile after creating this run? Reload to pick it up.</p>
        <?php elseif ($slip['run_status'] !== 'paid'): ?>
          <form method="post" action="<?= e(admin_url('payslips/' . $slip['id'] . '/reopen')) ?>"><?= csrf_field() ?><button class="btn btn--quiet btn--sm btn--block" type="submit"><?= icon('pencil-simple') ?>Reopen for changes</button></form>
        <?php else: ?>
          <p class="small muted">Paid on <?= e(fmt_date($slip['pay_date'])) ?>. This payslip is locked.</p>
        <?php endif; ?>
        <?php if ($slip['confirmed_at']): ?><p class="small muted">Confirmed <?= e(fmt_datetime($slip['confirmed_at'])) ?><?= $slip['emailed_at'] ? ', emailed ' . e(fmt_datetime($slip['emailed_at'])) : '' ?>.</p><?php endif; ?>
      </div>
    </div>
  </aside>
</div>
