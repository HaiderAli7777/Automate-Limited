<?php /** @var string $q @var array $results */ $total = array_sum(array_map('count', $results)); ?>
<div class="phead"><div><h1>Search</h1><p class="phead__sub"><?= mb_strlen($q) < 2 ? 'Type at least two characters.' : plural($total, 'result') . ' for "' . e($q) . '"' ?></p></div></div>
<?php if (mb_strlen($q) >= 2 && !$total): ?>
  <div class="panel"><div class="empty"><?= icon('magnifying-glass') ?><h3>No matches.</h3><p>Try part of a name, an email address, a phone number or a company.</p></div></div>
<?php endif; ?>
<div class="split--even" style="display:grid;gap:18px">
  <?php if ($results['employees']): ?>
  <div class="panel"><div class="panel__head"><h2>Employees</h2></div><div class="list">
    <?php foreach ($results['employees'] as $emp): ?>
      <div class="list__item"><?= avatar(employee_name($emp)) ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('employees/' . $emp['id'])) ?>"><?= e(employee_name($emp)) ?></a><span class="list__sub"><?= e(implode(' · ', array_filter([$emp['employee_code'], $emp['designation'], $emp['department_name']]))) ?></span></div><?= badge(EMPLOYEE_STATUSES[$emp['status']] ?? $emp['status'], EMPLOYEE_STATUS_COLORS[$emp['status']] ?? 'slate') ?></div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
  <?php if ($results['candidates']): ?>
  <div class="panel"><div class="panel__head"><h2>Candidates</h2></div><div class="list">
    <?php foreach ($results['candidates'] as $c): ?>
      <div class="list__item"><?= avatar(candidate_name($c)) ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('candidates/' . $c['id'])) ?>"><?= e(candidate_name($c)) ?></a><span class="list__sub"><?= e($c['email']) ?><?= $c['current_title'] ? ' · ' . e($c['current_title']) : '' ?></span></div></div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
  <?php if ($results['leads']): ?>
  <div class="panel"><div class="panel__head"><h2>Leads</h2></div><div class="list">
    <?php foreach ($results['leads'] as $l): $st = lead_stages()[(int) $l['stage_id']] ?? null; ?>
      <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><span class="list__sub"><?= e((string) $l['contact_name']) ?><?= $l['contact_company'] ? ' · ' . e($l['contact_company']) : '' ?></span></div><?= stage_badge($st) ?></div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
  <?php if ($results['contacts']): ?>
  <div class="panel"><div class="panel__head"><h2>Contacts</h2></div><div class="list">
    <?php foreach ($results['contacts'] as $c): ?>
      <div class="list__item"><?= avatar($c['name']) ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('contacts/' . $c['id'])) ?>"><?= e($c['name']) ?></a><span class="list__sub"><?= e((string) $c['email']) ?><?= $c['company'] ? ' · ' . e($c['company']) : '' ?></span></div></div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
  <?php if ($results['jobs']): ?>
  <div class="panel"><div class="panel__head"><h2>Jobs</h2></div><div class="list">
    <?php foreach ($results['jobs'] as $j): ?>
      <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('jobs/' . $j['id'])) ?>"><?= e($j['title']) ?></a><span class="list__sub"><?= e((string) $j['department']) ?><?= $j['location'] ? ' · ' . e($j['location']) : '' ?></span></div><?= badge(JOB_STATUSES[$j['status']] ?? $j['status'], JOB_STATUS_COLORS[$j['status']] ?? 'slate') ?></div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
</div>
