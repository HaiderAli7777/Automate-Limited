<?php /** @var array $rows @var array $p @var array $totals */ $stages = lead_stages(); $status = input('status', 'open'); ?>
<div class="phead">
  <div><h1>Leads</h1><p class="phead__sub"><?= plural((int) $totals['n'], 'lead') ?><?= (float) $totals['total'] > 0 ? ', worth ' . e(fmt_money($totals['total'])) . ' in total' : '' ?>. Website enquiries arrive here automatically.</p></div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('leads/board')) ?>"><?= icon('kanban') ?>Pipeline view</a>
    <a class="btn btn--quiet" href="<?= e(admin_url('leads/export') . qs()) ?>"><?= icon('download-simple') ?>Export CSV</a>
    <a class="btn btn--primary" href="<?= e(admin_url('leads/new')) ?>"><?= icon('plus') ?>New lead</a>
  </div>
</div>
<nav class="tabs" aria-label="Lead status">
  <?php foreach (['open' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'all' => 'All'] as $k => $label): ?>
    <a href="<?= e(qs(['status' => $k, 'page' => null, 'stage' => null])) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
<form class="filters" method="get" action="<?= e(admin_url('leads')) ?>" data-autosubmit>
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input class="input" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Title, name, email, company" aria-label="Search">
  <select class="select" name="stage" aria-label="Stage"><option value="">All stages</option><?php foreach ($stages as $s): ?><option value="<?= (int) $s['id'] ?>"<?= selected(input('stage'), $s['id']) ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="owner" aria-label="Owner"><option value="">Anyone</option><option value="me"<?= selected(input('owner'), 'me') ?>>Mine</option><option value="none"<?= selected(input('owner'), 'none') ?>>Unassigned</option><?php foreach (active_users() as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected(input('owner'), $u['id']) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="service" aria-label="Service"><option value="">Any service</option><?php foreach (array_merge(lead_services(), ['Not sure yet']) as $s): ?><option<?= selected(input('service'), $s) ?>><?= e($s) ?></option><?php endforeach; ?></select>
  <select class="select" name="follow" aria-label="Follow-up"><?= options(['' => 'Any follow-up', 'overdue' => 'Follow-up overdue', 'today' => 'Follow-up today', 'none' => 'No follow-up set'], input('follow')) ?></select>
  <select class="select" name="sort" aria-label="Sort"><?= options(['created' => 'Newest first', 'updated' => 'Recently active', 'value' => 'Highest value', 'follow' => 'Next follow-up'], input('sort', 'created')) ?></select>
  <?php if (input('q') !== '' || input('stage') !== '' || input('owner') !== '' || input('service') !== '' || input('follow') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('leads') . '?status=' . $status) ?>">Clear</a><?php endif; ?>
</form>
<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('funnel') ?><h3>No leads here.</h3><p>Enquiries from the website contact form land here with the stage "<?= e(reset($stages)['name'] ?? 'New') ?>". You can add leads from calls or referrals too.</p><a class="btn btn--primary" href="<?= e(admin_url('leads/new')) ?>">Add a lead</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Lead</th><th>Stage</th><th class="num">Value</th><th>Owner</th><th>Next follow-up</th><th>Source</th><th>Created</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $l): $cls = task_due_class($l['next_follow_up'], $l['status'] !== 'open' ? 'x' : null); ?>
      <tr>
        <td><a class="t-strong" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><?= $l['priority'] === 'high' ? ' ' . badge('High', 'orange', 'badge--plain') : '' ?><span class="t-sub"><?= e(implode(' · ', array_filter([$l['contact_name'], $l['contact_company'], $l['service']]))) ?></span></td>
        <td><?= stage_badge($stages[(int) $l['stage_id']] ?? null) ?></td>
        <td class="num nowrap"><?= $l['value'] !== null ? e(fmt_money($l['value'], $l['currency'])) : '<span class="muted">-</span>' ?></td>
        <td><?= $l['owner_id'] ? e(user_name((int) $l['owner_id'])) : '<span class="muted">Unassigned</span>' ?></td>
        <td class="nowrap <?= $cls === 'is-overdue' ? 'error' : '' ?>"><?= $l['next_follow_up'] ? e(fmt_day($l['next_follow_up'])) : '<span class="muted">-</span>' ?></td>
        <td class="muted"><?= e(LEAD_SOURCES[$l['source']] ?? $l['source']) ?></td>
        <td class="muted nowrap"><?= e(fmt_date($l['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
