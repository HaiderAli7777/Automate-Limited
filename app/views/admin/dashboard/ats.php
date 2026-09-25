<?php
/** @var array $p @var array $m @var array $funnel @var array $reached @var array $sourceRows @var array $reasonRows @var array $weekly
 *  @var array $jobs @var array $upcoming @var array $stale @var int $awaitingScore */
$stages = ats_stages();
?>
<div class="phead">
  <div><h1>ATS dashboard</h1><p class="phead__sub">Hiring at a glance: volume, speed and where candidates stall.</p></div>
  <div class="phead__actions"><a class="btn btn--quiet" href="<?= e(admin_url('pipeline')) ?>"><?= icon('kanban') ?>Pipeline</a><a class="btn btn--primary" href="<?= e(admin_url('jobs/new')) ?>"><?= icon('plus') ?>New job</a></div>
</div>
<?= period_filter($p, 'dashboard/ats') ?>

<div class="kpis">
  <?= kpi('Applications', number_format($m['apps']), 'user-plus', pct_change($m['apps'], $m['apps_prev']), true, strtolower($p['label']), admin_url('candidates'), 'kpi--hero') ?>
  <?= kpi('Hires', number_format($m['hires']), 'handshake', pct_change($m['hires'], $m['hires_prev']), true, strtolower($p['label'])) ?>
  <?= kpi('Interviews held', number_format($m['interviews']), 'calendar-dots', pct_change($m['interviews'], $m['interviews_prev']), true, $m['upcoming'] . ' in the next 7 days', admin_url('interviews')) ?>
  <?= kpi('In the pipeline now', number_format($m['active']), 'kanban', null, true, 'Active candidates', admin_url('pipeline')) ?>
  <?= kpi('Open jobs', number_format($m['open_jobs']), 'briefcase', null, true, plural($m['openings'], 'opening'), admin_url('jobs')) ?>
  <?= kpi('Average time to hire', $m['time_to_hire'] !== null ? number_format((float) $m['time_to_hire'], 0) . ' <small>days</small>' : '-', 'clock', null, true, 'Applied to hired') ?>
  <?= kpi('Offer acceptance', $m['offer_rate'] !== null ? number_format($m['offer_rate'], 0) . '<small>%</small>' : '-', 'check-circle', null, true, $m['offer_rate'] !== null ? 'Of offers decided' : 'No offers decided yet') ?>
</div>

<div class="dash-grid">
  <div class="panel span-7">
    <div class="panel__head"><h2>Applications per week</h2><span class="muted">Last 12 weeks</span></div>
    <div class="panel__body"><?= chart_columns($weekly, 'Applications') ?></div>
  </div>
  <div class="panel span-5">
    <div class="panel__head"><h2>Pipeline now</h2><span class="muted">Active candidates by stage</span></div>
    <div class="panel__body"><?= chart_hbars($funnel, 'Stage', 'No active candidates.') ?></div>
  </div>

  <div class="panel span-4">
    <div class="panel__head"><h2>Where applicants come from</h2></div>
    <div class="panel__body"><?= chart_hbars($sourceRows, 'Source') ?></div>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Why candidates didn't progress</h2></div>
    <div class="panel__body"><?= chart_hbars($reasonRows, 'Reason', 'Nobody was rejected in this period.') ?></div>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Needs attention</h2></div>
    <?php if ($awaitingScore): ?><div class="panel__body" style="padding-bottom:0"><p class="notice"><?= plural($awaitingScore, 'scorecard') ?> still missing after interviews. <a class="link" href="<?= e(admin_url('interviews') . '?view=past') ?>">See past interviews</a></p></div><?php endif; ?>
    <?php if (!$stale): ?>
      <div class="empty empty--sm"><?= icon('check-circle') ?><p>Nobody has been stuck in a stage for more than a week.</p></div>
    <?php else: ?>
      <div class="list"><?php foreach ($stale as $s): ?>
        <div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $s['id'])) ?>"><?= e(candidate_name($s)) ?></a><span class="list__sub"><?= e($s['job_title']) ?> · <?= e($stages[(int) $s['stage_id']]['name'] ?? '') ?></span></div><span class="list__side error"><?= days_since($s['stage_changed_at']) ?> days</span></div>
      <?php endforeach; ?></div>
    <?php endif; ?>
  </div>

  <div class="panel span-8">
    <div class="panel__head"><h2>Open jobs</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('jobs')) ?>">All jobs</a></div>
    <?php if (!$jobs): ?><div class="empty empty--sm"><p>No open jobs. <a class="link" href="<?= e(admin_url('jobs/new')) ?>">Post one</a>.</p></div><?php else: ?>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>Job</th><th class="num">Applied (period)</th><th class="num">To review</th><th class="num">In process</th><th class="num">Interviews booked</th><th class="num">Hired</th><th class="num">Days open</th></tr></thead>
      <tbody><?php foreach ($jobs as $j): ?>
        <tr>
          <td><a class="t-strong" href="<?= e(admin_url('jobs/' . $j['id'])) ?>"><?= e($j['title']) ?></a></td>
          <td class="num"><?= (int) $j['period_apps'] ?></td>
          <td class="num"><?= (int) $j['to_review'] ? '<strong>' . (int) $j['to_review'] . '</strong>' : '0' ?></td>
          <td class="num"><?= (int) $j['active'] ?></td>
          <td class="num"><?= (int) $j['interviews'] ?></td>
          <td class="num"><?= (int) $j['hired'] ?><span class="muted">/<?= (int) $j['openings'] ?></span></td>
          <td class="num muted"><?= days_since($j['published_at'] ?: $j['created_at']) ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
  </div>
  <div class="panel span-4">
    <div class="panel__head"><h2>Upcoming interviews</h2><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('interviews')) ?>">Calendar</a></div>
    <?php if (!$upcoming): ?><div class="empty empty--sm"><p>Nothing booked.</p></div><?php else: ?>
      <div class="list"><?php foreach ($upcoming as $iv): ?>
        <div class="list__item"><div class="date-tile"><span><?= e(fmt_date($iv['scheduled_at'], 'D')) ?></span><b><?= e(fmt_date($iv['scheduled_at'], 'j')) ?></b></div><div class="list__main"><a class="list__title" href="<?= e(admin_url('interviews/' . $iv['id'])) ?>"><?= e(candidate_name($iv)) ?></a><span class="list__sub"><?= e(fmt_time($iv['scheduled_at'])) ?> · <?= e($iv['job_title']) ?></span></div></div>
      <?php endforeach; ?></div>
    <?php endif; ?>
  </div>
</div>
