<?php
/** @var array $tasks @var array $interviews @var array $stats @var array $recentLeads @var array $recentApps */
$me = auth_user();
$hour = (int) date('G');
$greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$atsStages = user_can('ats') ? ats_stages() : [];
$leadStages = user_can('crm') ? lead_stages() : [];
?>
<div class="phead">
  <div><h1><?= e($greet) ?>, <?= e(explode(' ', (string) $me['name'])[0]) ?>.</h1><p class="phead__sub"><?= e(date('l j F Y')) ?>. Here's what needs you today.</p></div>
  <div class="phead__actions">
    <?php if (user_can('ats')): ?><a class="btn btn--quiet" href="<?= e(admin_url('dashboard/ats')) ?>"><?= icon('chart-bar') ?>ATS dashboard</a><?php endif; ?>
    <?php if (user_can('crm')): ?><a class="btn btn--quiet" href="<?= e(admin_url('dashboard/crm')) ?>"><?= icon('chart-line-up') ?>CRM dashboard</a><?php endif; ?>
    <?php if (user_can('hr.view')): ?><a class="btn btn--quiet" href="<?= e(admin_url('dashboard/hr')) ?>"><?= icon('chart-pie-slice') ?>HR dashboard</a><?php endif; ?>
  </div>
</div>

<?php if (user_can('settings') && setting('onboarding_dismissed', '0') !== '1'):
  $steps = onboarding_steps(); $done = count(array_filter($steps, static fn ($st) => $st['done']));
  if ($done < count($steps)): ?>
<section class="panel onboard" aria-labelledby="onboard-title">
  <div class="panel__head">
    <div><h2 id="onboard-title">Get set up</h2><p class="muted small"><?= $done ?> of <?= count($steps) ?> done. These make the team area work well from day one.</p></div>
    <form method="post" action="<?= e(admin_url('onboarding/dismiss')) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Hide</button></form>
  </div>
  <div class="meter onboard__meter" role="progressbar" aria-valuemin="0" aria-valuemax="<?= count($steps) ?>" aria-valuenow="<?= $done ?>" aria-label="Setup progress"><i style="width:<?= round($done / count($steps) * 100) ?>%"></i></div>
  <ol class="onboard__steps">
    <?php foreach ($steps as $i => $st): ?>
      <li class="<?= $st['done'] ? 'is-done' : '' ?>">
        <span class="onboard__n"><?= $st['done'] ? icon('check') : $i + 1 ?></span>
        <div class="onboard__text"><b><?= e($st['title']) ?></b><span><?= e($st['text']) ?></span></div>
        <?php if (!$st['done']): ?><a class="btn btn--quiet btn--sm" href="<?= e($st['href']) ?>"<?= str_starts_with($st['href'], admin_url()) ? '' : ' target="_blank" rel="noopener"' ?>><?= e($st['cta']) ?></a><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
<?php endif; endif; ?>

<?php if ($stats): ?>
<div class="kpis">
  <?php if (user_can('ats')): ?>
    <?= kpi('New applications this week', number_format($stats['apps']), 'user-plus', pct_change($stats['apps'], $stats['apps_prev']), true, 'vs last week', admin_url('candidates')) ?>
    <?= kpi('Waiting for first review', number_format($stats['to_review']), 'hourglass', null, true, 'In the first pipeline stage', admin_url('pipeline')) ?>
  <?php endif; ?>
  <?php if (user_can('crm')): ?>
    <?= kpi('New enquiries this week', number_format($stats['leads']), 'funnel', pct_change($stats['leads'], $stats['leads_prev']), true, 'vs last week', admin_url('leads') . '?status=all') ?>
    <?= kpi('Follow-ups overdue', number_format($stats['follow_overdue']), 'bell', null, true, $stats['uncontacted'] ? $stats['uncontacted'] . ' new leads not contacted' : 'All new leads contacted', admin_url('leads') . '?follow=overdue') ?>
  <?php endif; ?>
  <?php if (user_can('ats') && !user_can('crm')): ?>
    <?= kpi('Interviews in the next 7 days', number_format($stats['interviews_week']), 'calendar-dots', null, true, '', admin_url('interviews')) ?>
    <?= kpi('Open jobs', number_format($stats['open_jobs']), 'briefcase', null, true, '', admin_url('jobs')) ?>
  <?php elseif (user_can('crm') && !user_can('ats')): ?>
    <?= kpi('Open pipeline', e(compact_money($stats['pipeline'])), 'currency-circle-dollar', null, true, '', admin_url('leads/board')) ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="split">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Your follow-ups</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('tasks')) ?>">All tasks</a></div>
      <?php if (!$tasks): ?>
        <div class="empty empty--sm"><?= icon('check-circle') ?><p>Nothing due in the next two days.</p></div>
      <?php else: foreach ($tasks as $t) { partial('admin/partials/task-item', ['t' => $t, 'showAbout' => true]); } endif; ?>
    </div>

    <?php if ($recentLeads): ?>
    <div class="panel">
      <div class="panel__head"><h2>Latest enquiries</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('leads') . '?status=all') ?>">All leads</a></div>
      <div class="list"><?php foreach ($recentLeads as $l): ?>
        <div class="list__item"><?= avatar((string) ($l['contact_name'] ?: $l['title'])) ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><span class="list__sub"><?= e(implode(' · ', array_filter([$l['contact_name'], LEAD_SOURCES[$l['source']] ?? '', time_ago($l['created_at'])]))) ?></span></div><?= stage_badge($leadStages[(int) $l['stage_id']] ?? null) ?></div>
      <?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <?php if ($recentApps): ?>
    <div class="panel">
      <div class="panel__head"><h2>Latest applications</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('candidates')) ?>">All candidates</a></div>
      <div class="list"><?php foreach ($recentApps as $a): ?>
        <div class="list__item"><?= avatar(candidate_name($a)) ?><div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e(candidate_name($a)) ?></a><span class="list__sub"><?= e($a['job_title']) ?> · <?= e(time_ago($a['applied_at'])) ?></span></div><?= stage_badge($atsStages[(int) $a['stage_id']] ?? null) ?></div>
      <?php endforeach; ?></div>
    </div>
    <?php endif; ?>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Your interviews</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('interviews') . (user_can('ats') ? '?who=mine' : '')) ?>">Calendar</a></div>
      <?php if (!$interviews): ?>
        <div class="empty empty--sm"><p>You're not on any upcoming interview panels.</p></div>
      <?php else: ?>
        <div class="list"><?php foreach ($interviews as $iv): ?>
          <div class="list__item">
            <div class="date-tile"><span><?= e(fmt_date($iv['scheduled_at'], 'D')) ?></span><b><?= e(fmt_date($iv['scheduled_at'], 'j')) ?></b></div>
            <div class="list__main"><a class="list__title" href="<?= e(admin_url('interviews/' . $iv['id'])) ?>"><?= e(candidate_name($iv)) ?></a><span class="list__sub"><?= e(fmt_time($iv['scheduled_at'])) ?> · <?= e($iv['title']) ?> · <?= e($iv['job_title']) ?></span></div>
          </div>
        <?php endforeach; ?></div>
      <?php endif; ?>
    </div>
    <?php if (user_can('ats') && !empty($stats)): ?>
    <div class="panel"><div class="panel__body">
      <div class="stat-row">
        <div><b><?= number_format($stats['open_jobs']) ?></b><span>Open jobs</span></div>
        <div><b><?= number_format($stats['interviews_week']) ?></b><span>Interviews in 7 days</span></div>
        <div><b><?= number_format($stats['to_review']) ?></b><span>To review</span></div>
      </div>
    </div></div>
    <?php endif; ?>
    <?php if (user_can('crm') && !empty($stats) && user_can('ats')): ?>
    <div class="panel"><div class="panel__body">
      <div class="stat-row">
        <div><b><?= e(compact_number($stats['pipeline'])) ?></b><span>Open pipeline (<?= e((string) setting('default_currency', 'PKR')) ?>)</span></div>
        <div><b><?= number_format($stats['leads']) ?></b><span>Enquiries this week</span></div>
        <div><b><?= number_format($stats['uncontacted']) ?></b><span>Not yet contacted</span></div>
      </div>
    </div></div>
    <?php endif; ?>
  </div>
</div>
