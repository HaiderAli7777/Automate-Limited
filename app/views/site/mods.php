<?php
/**
 * Odoo module tiles. Each one opens the enquiry page with the module filled in.
 * @var array $items  from site_modules() @var bool|null $full  show feature points (the modules page)
 */
$full = !empty($full);
?>
<ul class="mods<?= $full ? ' mods--full' : '' ?>">
  <?php foreach ($items as $m): $size = $full ? '' : (string) ($m['home'] ?? ''); ?>
    <li class="<?= e($size) ?>">
      <a class="mod" href="<?= e(enquiry_url('Odoo ERP', $m['name'])) ?>" aria-label="<?= e($m['name'] . ': ' . $m['blurb'] . '. Enquire about ' . $m['name']) ?>">
        <svg class="ic" aria-hidden="true"><use href="#i-<?= e($m['icon']) ?>"/></svg>
        <h3><?= e($m['name']) ?></h3>
        <p><?= e($m['blurb']) ?></p>
        <?php if ($full && !empty($m['points'])): ?>
          <ul class="mod__points"><?php foreach ($m['points'] as $pt): ?><li><?= e($pt) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <span class="mod__go" aria-hidden="true">Enquire<svg class="ic"><use href="#i-arrow-up-right"/></svg></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
