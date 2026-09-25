<?php
/** @var array $rows @var string $view @var bool $mine @var string|null $month @var array $pending */
$tabs = ['agenda' => 'Upcoming', 'month' => 'Calendar', 'past' => 'Past'];
?>
<div class="phead">
  <div><h1><?= user_can('ats') ? 'Interviews' : 'My interviews' ?></h1><p class="phead__sub">Schedule interviews from a candidate's application. Invitations include a calendar file.</p></div>
  <div class="phead__actions">
    <?php if (user_can('ats')): ?>
    <nav class="seg" aria-label="Whose interviews">
      <a href="<?= e(qs(['who' => null])) ?>" class="<?= !$mine ? 'is-active' : '' ?>">Everyone</a>
      <a href="<?= e(qs(['who' => 'mine'])) ?>" class="<?= $mine ? 'is-active' : '' ?>">Mine</a>
    </nav>
    <?php endif; ?>
  </div>
</div>

<?php if ($pending): ?>
  <div class="notice" style="margin-bottom:18px">
    <strong><?= plural(count($pending), 'scorecard') ?> waiting for you.</strong>
    <?php foreach ($pending as $i => $p): ?><?= $i ? ', ' : ' ' ?><a class="link" href="<?= e(admin_url('interviews/' . $p['id'])) ?>#feedback"><?= e(candidate_name($p)) ?> (<?= e(fmt_date($p['scheduled_at'], 'j M')) ?>)</a><?php endforeach; ?>
  </div>
<?php endif; ?>

<nav class="tabs" aria-label="Interview views">
  <?php foreach ($tabs as $k => $label): ?><a href="<?= e(qs(['view' => $k, 'month' => null])) ?>" class="<?= $view === $k ? 'is-active' : '' ?>"><?= e($label) ?></a><?php endforeach; ?>
</nav>

<?php if ($view === 'month'):
    $start = strtotime($month . '-01');
    $gridStart = strtotime('-' . ((int) date('N', $start) - 1) . ' days', $start);
    $byDay = [];
    foreach ($rows as $r) { $byDay[substr($r['scheduled_at'], 0, 10)][] = $r; }
?>
  <div class="row row--between" style="margin-bottom:12px">
    <h2 style="font-size:1.25rem"><?= e(date('F Y', $start)) ?></h2>
    <div class="row">
      <a class="btn btn--quiet btn--sm" href="<?= e(qs(['month' => date('Y-m', strtotime('-1 month', $start))])) ?>"><?= icon('arrow-left') ?>Previous</a>
      <a class="btn btn--quiet btn--sm" href="<?= e(qs(['month' => date('Y-m')])) ?>">Today</a>
      <a class="btn btn--quiet btn--sm" href="<?= e(qs(['month' => date('Y-m', strtotime('+1 month', $start))])) ?>">Next<?= icon('arrow-right') ?></a>
    </div>
  </div>
  <div class="cal" role="grid" aria-label="Interviews in <?= e(date('F Y', $start)) ?>">
    <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d): ?><div class="cal__dow" role="columnheader"><?= $d ?></div><?php endforeach; ?>
    <?php for ($i = 0; $i < 42; $i++): $day = strtotime('+' . $i . ' days', $gridStart); $key = date('Y-m-d', $day); ?>
      <div class="cal__day<?= date('m', $day) !== date('m', $start) ? ' is-out' : '' ?><?= $key === today() ? ' is-today' : '' ?>" role="gridcell">
        <span class="cal__n"><?= date('j', $day) ?></span>
        <?php foreach ($byDay[$key] ?? [] as $r): ?>
          <a class="cal__ev<?= $r['status'] === 'cancelled' ? ' is-cancelled' : '' ?>" style="--dot:<?= stage_dot(INTERVIEW_STATUS_COLORS[$r['status']] ?? 'blue') ?>" href="<?= e(admin_url('interviews/' . $r['id'])) ?>" title="<?= e(fmt_time($r['scheduled_at']) . ' ' . candidate_name($r) . ', ' . $r['job_title']) ?>"><?= e(fmt_time($r['scheduled_at'])) ?> <?= e(candidate_name($r)) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
<?php else: ?>
  <?php if (!$rows): ?>
    <div class="panel"><div class="empty"><?= icon('calendar-dots') ?><h3><?= $view === 'past' ? 'No past interviews.' : 'Nothing scheduled.' ?></h3><p>Open a candidate from the pipeline and choose Schedule interview.</p><?php if (user_can('ats')): ?><a class="btn btn--quiet" href="<?= e(admin_url('pipeline')) ?>">Go to the pipeline</a><?php endif; ?></div></div>
  <?php else:
      $groups = [];
      foreach ($rows as $r) { $groups[substr($r['scheduled_at'], 0, 10)][] = $r; }
  ?>
    <div class="stack">
    <?php foreach ($groups as $day => $items): ?>
      <div class="panel">
        <div class="panel__head"><h2><?= e(fmt_day($day . ' 12:00:00')) ?></h2><span class="muted"><?= e(date('l j F', strtotime($day))) ?></span></div>
        <div class="list">
          <?php foreach ($items as $r): ?>
            <div class="list__item">
              <div class="date-tile"><b style="font-size:.9375rem"><?= e(fmt_time($r['scheduled_at'])) ?></b><span><?= (int) $r['duration_minutes'] ?>m</span></div>
              <div class="list__main">
                <a class="list__title" href="<?= e(admin_url('interviews/' . $r['id'])) ?>"><?= e(candidate_name($r)) ?>, <?= e($r['title']) ?></a>
                <span class="list__sub"><?= e($r['job_title']) ?> · <?= e((string) ($r['panel_names'] ?: 'No panel')) ?></span>
              </div>
              <div class="list__side stack-sm" style="justify-items:end">
                <?= badge(INTERVIEW_STATUSES[$r['status']] ?? $r['status'], INTERVIEW_STATUS_COLORS[$r['status']] ?? 'slate') ?>
                <span class="small"><?= (int) $r['feedback_count'] ?>/<?= (int) $r['panel_count'] ?> scorecards</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
