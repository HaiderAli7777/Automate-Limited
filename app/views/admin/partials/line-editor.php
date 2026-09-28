<?php
/**
 * Allowances and deductions editor with live totals.
 * @var string $prefix  input name prefix (comp or line)  @var array $lines  rows with type, label, amount
 * @var float $basic  @var string $currency  @var bool|null $readonly
 */
$readonly = !empty($readonly);
$groups = ['allowance' => ['Allowances', ALLOWANCE_PRESETS, '+'], 'deduction' => ['Deductions', DEDUCTION_PRESETS, '-']];
$sum = ['allowance' => 0.0, 'deduction' => 0.0];
foreach ($lines as $l) {
    $sum[$l['type']] = ($sum[$l['type']] ?? 0) + (float) $l['amount'];
}
$fmt = static fn (float $v): string => number_format($v, 2);
$row = static function (string $type, string $label, string $amount) use ($prefix, $readonly): string {
    if ($readonly) {
        return '<div class="line is-readonly"><span class="line__label">' . e($label) . '</span><span class="line__amount num">' . e(number_format((float) $amount, 2)) . '</span></div>';
    }
    return '<div class="line" data-line><input type="hidden" name="' . e($prefix) . '_type[]" value="' . e($type) . '">'
        . '<input class="input input--sm" name="' . e($prefix) . '_label[]" value="' . e($label) . '" aria-label="Name" maxlength="80" required>'
        . '<input class="input input--sm num" name="' . e($prefix) . '_amount[]" value="' . e($amount) . '" aria-label="Amount" inputmode="decimal" data-line-amount required>'
        . '<button type="button" class="btn btn--ghost btn--icon btn--sm" data-remove-line aria-label="Remove line">' . icon('x') . '</button></div>';
};
?>
<div class="lines" data-lines data-basic="<?= e((string) (float) $basic) ?>">
  <?php foreach ($groups as $type => [$title, $presets, $sign]): ?>
    <div class="lines__group lines__group--<?= $type ?>" data-group="<?= $type ?>">
      <div class="lines__head"><h3><?= e($title) ?></h3><span class="num lines__sub" data-sum="<?= $type ?>"><?= $sign ?> <?= e($fmt($sum[$type])) ?></span></div>
      <div class="lines__list" data-line-list>
        <?php $any = false; foreach ($lines as $l): if ($l['type'] !== $type) continue; $any = true; ?>
          <?= $row($type, (string) $l['label'], number_format((float) $l['amount'], 2, '.', '')) ?>
        <?php endforeach; ?>
        <p class="lines__empty small muted"<?= $any ? ' hidden' : '' ?>>None.</p>
      </div>
      <?php if (!$readonly): ?>
        <div class="lines__add">
          <?php foreach ($presets as $pr): ?><button type="button" class="chip chip--btn" data-add-line="<?= $type ?>" data-label="<?= e($pr) ?>"><?= icon('plus', 'ic ic--sm') ?><?= e($pr) ?></button><?php endforeach; ?>
          <button type="button" class="chip chip--btn" data-add-line="<?= $type ?>" data-label=""><?= icon('plus', 'ic ic--sm') ?>Other</button>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <dl class="lines__total">
    <dt>Basic salary</dt><dd class="num"><?= e($currency) ?> <?= e($fmt((float) $basic)) ?></dd>
    <dt>Gross pay</dt><dd class="num" data-sum="gross"><?= e($currency) ?> <?= e($fmt((float) $basic + $sum['allowance'])) ?></dd>
    <dt>Net pay</dt><dd class="num lines__net" data-sum="net" data-currency="<?= e($currency) ?>"><?= e($currency) ?> <?= e($fmt((float) $basic + $sum['allowance'] - $sum['deduction'])) ?></dd>
  </dl>
  <?php if (!$readonly): ?>
  <template data-line-template><?= $row('__type__', '', '') ?></template>
  <?php endif; ?>
</div>
