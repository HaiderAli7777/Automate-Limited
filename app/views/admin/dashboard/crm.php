<?php
/** @var array $p @var array $m @var string $cur @var array $pipeRows @var array $countRows @var array $sourceRows @var array $serviceRows
 *  @var array $lostRows @var array $owners @var array $followUps @var array $topDeals @var array $weekly */
$stages = lead_stages();
?>
<div class="phead">
  <div><h1>CRM dashboard</h1><p class="phead__sub">Enquiries, pipeline and what's been won, in <?= e($cur) ?>.</p></div>
  <div class="phead__actions"><a class="btn btn--quiet" href="<?= e(admin_url('leads/board')) ?>"><?= icon('kanban') ?>Sales pipeline</a><a class="btn btn--primary" href="<?= e(admin_url('leads/new')) ?>"><?= icon('plus') ?>New lead</a></div>
</div>
<?= period_filter($p, 'dashboard/crm') ?>

<div class="kpis">
  <?= kpi('Won', e(compact_money($m['won_v'], $cur)), 'handshake', pct_change($m['won_v'], $m['won_v_prev']), true, plural($m['won_n'], 'deal') . ', ' . strtolower($p['label']), admin_url('leads') . '?status=won', 'kpi--hero') ?>
  <?= kpi('New leads', number_format($m['leads']), 'funnel', pct_change($m['leads'], $m['leads_prev']), true, strtolower($p['label']), admin_url('leads') . '?status=all') ?>
  <?php $pts = $m['win_rate'] !== null && $m['win_rate_prev'] !== null ? $m['win_rate'] - $m['win_rate_prev'] : null; ?>
  <?= kpi('Win rate', $m['win_rate'] !== null ? number_format($m['win_rate'], 0) . '<small>%</small>' : '-', 'target', null, true, $pts !== null ? sprintf('%+d points vs previous period', (int) round($pts)) : 'Of deals closed in the period') ?>
  <?= kpi('Open pipeline', e(compact_money($m['open_v'], $cur)), 'currency-circle-dollar', null, true, plural($m['open_n'], 'open deal'), admin_url('leads/board')) ?>
  <?= kpi('Weighted forecast', e(compact_money($m['weighted'], $cur)), 'chart-line-up', null, true, 'Value times stage probability') ?>
  <?= kpi('Average deal won', $m['avg_deal'] !== null ? e(compact_money($m['avg_deal'], $cur)) : '-', 'trend-up', null, true, $m['cycle'] !== null ? 'Closed in ' . number_format((float) $m['cycle'], 0) . ' days on average' : '') ?>
  <?= kpi('Follow-ups overdue', number_format($m['overdue']), 'bell', null, false, $m['no_follow'] . ' open leads have none set', admin_url('leads') . '?follow=overdue') ?>
</div>

<div class="dash-grid">
  <div class="panel span-7">
    <div class="panel__head"><h2>New leads per week</h2><span class="muted">Last 12 weeks</span></div>
    <div class="panel__body"><?= chart_columns($weekly, 'New leads') ?></div>
  </div>
  <div class="panel span-5">
    <div class="panel__head"><h2>Pipeline value by stage</h2><span class="muted">Open deals now</span></div>
    <div class="panel__body"><?= chart_hbars($pipeRows, 'Stage', 'No open deals with a value yet.') ?></div>
  </div>

  <div class="panel span-4">
    <div class="panel__head"><h2>Lead sources</h2></div>
    <div class="panel__body"><?= chart_hbars($sourceRows, 'Source') ?></div>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>What people ask for</h2></div>
    <div class="panel__body"><?= chart_hbars($serviceRows, 'Service') ?></div>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Why deals were lost</h2></div>
    <div class="panel__body"><?= chart_hbars($lostRows, 'Reason', 'No deals lost in this period.') ?></div>
  </div>

  <div class="panel span-6">
    <div class="panel__head"><h2>Follow-ups due</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('leads') . '?sort=follow') ?>">All</a></div>
    <?php if (!$followUps): ?><div class="empty empty--sm"><?= icon('check-circle') ?><p>Nothing overdue or due today.</p></div><?php else: ?>
      <div class="list"><?php foreach ($followUps as $l): $late = (ts($l['next_follow_up']) ?? 0) < time(); ?>
        <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><span class="list__sub"><?= e(implode(' · ', array_filter([$l['contact_name'], $l['owner_id'] ? user_name((int) $l['owner_id']) : 'Unassigned']))) ?></span></div><span class="list__side <?= $late ? 'error' : '' ?>"><?= $late ? 'Overdue ' : '' ?><?= e(fmt_day($l['next_follow_up'])) ?></span></div>
      <?php endforeach; ?></div>
    <?php endif; ?>
  </div>
  <div class="panel span-6">
    <div class="panel__head"><h2>Biggest open deals</h2></div>
    <?php if (!$topDeals): ?><div class="empty empty--sm"><p>Add values to open leads to see them here.</p></div><?php else: ?>
      <div class="list"><?php foreach ($topDeals as $l): ?>
        <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><span class="list__sub"><?= e(implode(' · ', array_filter([$l['contact_company'] ?: $l['contact_name'], $stages[(int) $l['stage_id']]['name'] ?? '']))) ?></span></div><span class="list__side"><strong class="num" style="color:var(--ink)"><?= e(fmt_money($l['value'], $l['currency'])) ?></strong></span></div>
      <?php endforeach; ?></div>
    <?php endif; ?>
  </div>

  <div class="panel span-12">
    <div class="panel__head"><h2>By owner</h2><span class="muted">Won in the period, open now</span></div>
    <?php if (!$owners): ?><div class="empty empty--sm"><p>Assign owners to leads to compare workloads here.</p></div><?php else: ?>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>Owner</th><th class="num">Open deals</th><th class="num">Open value</th><th class="num">Won</th><th class="num">Won value</th></tr></thead>
      <tbody><?php foreach ($owners as $o): ?>
        <tr><td><div class="person"><?= avatar($o['name'], 'sm') ?><a class="t-strong" href="<?= e(admin_url('leads') . '?owner=' . $o['id']) ?>"><?= e($o['name']) ?></a></div></td><td class="num"><?= (int) $o['open_n'] ?></td><td class="num"><?= e(compact_money($o['open_v'], $cur)) ?></td><td class="num"><?= (int) $o['won_n'] ?></td><td class="num"><?= e(compact_money($o['won_v'], $cur)) ?></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
  </div>
</div>
