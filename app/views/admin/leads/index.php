<?php /** @var array $rows @var array $p @var array $totals */ $stages = lead_stages(); $status = input('status', 'open'); $bulk = user_can('crm.manage'); ?>
<div class="phead">
  <div><h1>Leads</h1><p class="phead__sub"><?= plural((int) $totals['n'], 'lead') ?><?= (float) $totals['total'] > 0 ? ', worth ' . e(fmt_money($totals['total'])) . ' in total' : '' ?>. Website enquiries arrive here automatically.</p></div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('leads/board')) ?>"><?= icon('kanban') ?>Pipeline view</a>
    <?php if (user_can('data.export')): ?><a class="btn btn--quiet" href="<?= e(admin_url('leads/export') . qs()) ?>"><?= icon('download-simple') ?>Export CSV</a><?php endif; ?>
    <?php if ($bulk): ?><a class="btn btn--primary" href="<?= e(admin_url('leads/new')) ?>"><?= icon('plus') ?>New lead</a><?php endif; ?>
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
  <?php if (user_can('crm.all')): ?><select class="select" name="owner" aria-label="Owner"><option value="">Anyone</option><option value="me"<?= selected(input('owner'), 'me') ?>>Mine</option><option value="none"<?= selected(input('owner'), 'none') ?>>Unassigned</option><?php foreach (active_users() as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected(input('owner'), $u['id']) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select><?php endif; ?>
  <select class="select" name="service" aria-label="Service"><option value="">Any service</option><?php foreach (array_merge(lead_services(), ['Not sure yet']) as $s): ?><option<?= selected(input('service'), $s) ?>><?= e($s) ?></option><?php endforeach; ?></select>
  <select class="select" name="follow" aria-label="Follow-up"><?= options(['' => 'Any follow-up', 'overdue' => 'Follow-up overdue', 'today' => 'Follow-up today', 'none' => 'No follow-up set'], input('follow')) ?></select>
  <select class="select" name="sort" aria-label="Sort"><?= options(['created' => 'Newest first', 'updated' => 'Recently active', 'value' => 'Highest value', 'follow' => 'Next follow-up'], input('sort', 'created')) ?></select>
  <?php if (input('q') !== '' || input('stage') !== '' || input('owner') !== '' || input('service') !== '' || input('follow') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('leads') . '?status=' . $status) ?>">Clear</a><?php endif; ?>
</form>
<<?= $bulk ? 'form method="post" action="' . e(admin_url('leads/bulk')) . '" data-bulk' : 'div' ?> class="panel">
  <?php if ($bulk): ?><?= csrf_field() ?>
  <div class="bulkbar" data-bulk-bar hidden>
    <span class="bulkbar__count" data-bulk-count>0 selected</span>
    <select class="select select--sm" name="action" data-bulk-action aria-label="Action">
      <option value="stage">Move to stage</option>
      <?php if (user_can('crm.all')): ?><option value="owner">Assign to</option><?php endif; ?>
      <?php if (user_can('data.delete')): ?><option value="delete">Delete</option><?php endif; ?>
    </select>
    <span class="row" data-bulk-for="stage">
      <select class="select select--sm" name="stage_id" data-bulk-stage aria-label="Stage"><?php foreach ($stages as $s): ?><option value="<?= (int) $s['id'] ?>" data-kind="<?= e($s['kind']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
      <select class="select select--sm" name="reason" aria-label="Reason lost" data-bulk-rejected hidden><?php foreach (LOST_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select>
    </span>
    <?php if (user_can('crm.all')): ?><span data-bulk-for="owner" hidden><select class="select select--sm" name="owner_id" aria-label="Owner"><?= user_options(null, 'Nobody (unassign)', 'crm.view') ?></select></span><?php endif; ?>
    <button class="btn btn--primary btn--sm" type="submit" data-bulk-go>Apply</button>
    <button class="btn btn--ghost btn--sm" type="button" data-bulk-clear>Clear</button>
  </div>
  <?php endif; ?>
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('funnel') ?><h3>No leads here.</h3><p>Enquiries from the website contact form land here with the stage "<?= e(reset($stages)['name'] ?? 'New') ?>". You can add leads from calls or referrals too.</p><?php if ($bulk): ?><a class="btn btn--primary" href="<?= e(admin_url('leads/new')) ?>">Add a lead</a><?php endif; ?></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><?php if ($bulk): ?><th class="tbl__check"><input type="checkbox" data-bulk-all aria-label="Select all on this page"></th><?php endif; ?><th>Lead</th><th>Stage</th><th class="num">Value</th><th>Owner</th><th>Next follow-up</th><th>Source</th><th>Created</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $l): $cls = task_due_class($l['next_follow_up'], $l['status'] !== 'open' ? 'x' : null); ?>
      <tr>
        <?php if ($bulk): ?><td class="tbl__check"><input type="checkbox" name="ids[]" value="<?= (int) $l['id'] ?>" data-bulk-item aria-label="Select <?= e($l['title']) ?>"></td><?php endif; ?>
        <td><a class="t-strong" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><?= $l['priority'] === 'high' ? ' ' . badge('High', 'orange', 'badge--plain') : '' ?><span class="t-sub"><?= e(implode(' · ', array_filter([$l['contact_name'], $l['contact_company'], $l['service'] . (!empty($l['topic']) ? ': ' . $l['topic'] : '')]))) ?></span></td>
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
</<?= $bulk ? 'form' : 'div' ?>>
