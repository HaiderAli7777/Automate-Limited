<?php
/** @var string $tab @var array $templates */
partial('admin/settings/_tabs', ['tab' => $tab]);
$groups = ['ats' => 'Recruitment', 'crm' => 'Sales', 'hr' => 'People and payroll'];
?>
<div class="split--even" style="display:grid;gap:18px">
<?php foreach ($groups as $mod => $label): ?>
  <div class="panel">
    <div class="panel__head"><h2><?= e($label) ?></h2></div>
    <div class="list">
      <?php foreach ($templates as $t): if ($t['module'] !== $mod) continue; ?>
        <div class="list__item">
          <div class="list__main"><a class="list__title" href="<?= e(admin_url('settings/templates/' . $t['id'])) ?>"><?= e($t['name']) ?></a><span class="list__sub"><?= e($t['subject']) ?></span></div>
          <a class="btn btn--ghost btn--sm" href="<?= e(admin_url('settings/templates/' . $t['id'])) ?>">Edit</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
