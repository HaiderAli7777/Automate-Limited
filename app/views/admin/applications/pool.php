<?php /** @var array $rows @var array $jobs @var int $due */
$stages = ats_stages();
$active = array_filter($stages, static fn ($s) => $s['kind'] === 'active');
$edit = user_can('ats.manage');
?>
<div class="phead">
  <div><h1>Talent pool</h1><p class="phead__sub">Good candidates you couldn't hire yet, kept on file against the role they applied for.</p></div>
  <div class="phead__actions"><a class="btn btn--quiet" href="<?= e(admin_url('pipeline')) ?>"><?= icon('kanban') ?>Hiring pipeline</a></div>
</div>

<div class="kpis kpis--3">
  <div class="kpi"><span class="kpi__label"><?= icon('user-list') ?>In the pool</span><span class="kpi__value"><?= number_format((int) db()->value("SELECT COUNT(*) FROM applications WHERE status = 'pool'")) ?></span><span class="kpi__foot">Across <?= plural(count($jobs), 'job') ?></span></div>
  <a class="kpi" href="<?= e(admin_url('talent-pool') . '?due=1') ?>"><span class="kpi__label"><?= icon('bell') ?>Due for a revisit</span><span class="kpi__value"><?= number_format($due) ?></span><span class="kpi__foot">Revisit date is today or earlier</span></a>
  <div class="kpi"><span class="kpi__label"><?= icon('currency-circle-dollar') ?>Main reason</span><span class="kpi__value kpi__value--sm"><?= e((string) (db()->value("SELECT pool_reason FROM applications WHERE status = 'pool' AND pool_reason IS NOT NULL GROUP BY pool_reason ORDER BY COUNT(*) DESC LIMIT 1") ?: 'None yet')) ?></span><span class="kpi__foot">Most common reason for holding</span></div>
</div>

<form class="filters" method="get" action="<?= e(admin_url('talent-pool')) ?>" data-autosubmit>
  <input class="input" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Name, email, current role" aria-label="Search">
  <select class="select" name="job" aria-label="Job"><option value="">All jobs</option><?php foreach ($jobs as $j): ?><option value="<?= (int) $j['id'] ?>"<?= selected(input('job'), $j['id']) ?>><?= e($j['title']) ?> (<?= (int) $j['n'] ?>)</option><?php endforeach; ?></select>
  <select class="select" name="reason" aria-label="Reason"><option value="">Any reason</option><?php foreach (POOL_REASONS as $r): ?><option<?= selected(input('reason'), $r) ?>><?= e($r) ?></option><?php endforeach; ?></select>
  <label class="checkbox small"><input type="checkbox" name="due" value="1"<?= checked(input('due') === '1') ?>><span>Due for a revisit</span></label>
  <?php if (input('q') !== '' || input('job') !== '' || input('reason') !== '' || input('due') !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('talent-pool')) ?>">Clear</a><?php endif; ?>
</form>

<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('user-list') ?><h3>The pool is empty.</h3><p>When a strong candidate can't be hired right now (salary, seniority or timing), move them to <b>Talent pool</b> on the pipeline instead of rejecting them. They'll wait here with the reason and a revisit date.</p></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th style="min-width:220px">Candidate</th><th style="min-width:170px">Kept for</th><th style="min-width:180px">Why not now</th><th>Expects</th><th>Score</th><th>Revisit</th><?php if ($edit): ?><th class="t-right">Bring back</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $name = candidate_name($r); $late = $r['revisit_on'] && $r['revisit_on'] <= today(); ?>
      <tr>
        <td><div class="person"><?= avatar($name) ?><span><a class="t-strong" href="<?= e(admin_url('applications/' . $r['id'])) ?>"><?= e($name) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$r['current_title'], $r['experience_years'] !== null ? (float) $r['experience_years'] . ' yrs' : null])) ?: $r['email']) ?></span></span></div></td>
        <td><?= e($r['job_title']) ?><?= $r['job_status'] !== 'open' ? ' ' . badge(JOB_STATUSES[$r['job_status']] ?? $r['job_status'], 'slate', 'badge--plain') : '' ?><span class="t-sub">Since <?= e(fmt_date($r['pooled_at'])) ?></span></td>
        <td><?= $r['pool_reason'] ? e($r['pool_reason']) : '<span class="muted">-</span>' ?><?php if ($r['pool_note']): ?><span class="t-sub clamp-1" title="<?= e($r['pool_note']) ?>"><?= e($r['pool_note']) ?></span><?php endif; ?></td>
        <td class="nowrap"><?= $r['expected_salary'] ? e($r['expected_salary']) : '<span class="muted">-</span>' ?></td>
        <td><?= $r['avg_rating'] ? stars((float) $r['avg_rating']) : '<span class="muted">-</span>' ?></td>
        <td class="nowrap<?= $late ? ' error' : '' ?>"><?= $r['revisit_on'] ? e(fmt_date($r['revisit_on'])) : '<span class="muted">No date</span>' ?></td>
        <?php if ($edit): ?>
        <td class="t-right">
          <form method="post" action="<?= e(admin_url('applications/' . $r['id'] . '/stage')) ?>" class="row" style="justify-content:flex-end;flex-wrap:nowrap">
            <?= csrf_field() ?>
            <select class="select select--sm" name="stage_id" aria-label="Move <?= e($name) ?> to" style="width:auto"><?php foreach ($active as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
            <button class="btn btn--quiet btn--sm" type="submit">Move</button>
          </form>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
