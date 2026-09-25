<?php /** @var array $rows @var array $p @var array $jobs */ $stages = ats_stages(); ?>
<div class="phead">
  <div><h1>Candidates</h1><p class="phead__sub"><?= plural($p['total'], 'result') ?>. One row per application; people without one are in the talent pool.</p></div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('candidates/export') . qs()) ?>"><?= icon('download-simple') ?>Export CSV</a>
    <a class="btn btn--primary" href="<?= e(admin_url('candidates/new')) ?>"><?= icon('user-plus') ?>Add candidate</a>
  </div>
</div>
<form class="filters" method="get" action="<?= e(admin_url('candidates')) ?>" data-autosubmit>
  <input class="input" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Name, email, phone, company, tag" aria-label="Search">
  <select class="select" name="job" aria-label="Job"><option value="">All jobs</option><?php foreach ($jobs as $j): ?><option value="<?= (int) $j['id'] ?>"<?= selected(input('job'), $j['id']) ?>><?= e($j['title']) ?><?= $j['status'] !== 'open' ? ' (' . e(JOB_STATUSES[$j['status']]) . ')' : '' ?></option><?php endforeach; ?></select>
  <select class="select" name="stage" aria-label="Stage"><option value="">All stages</option><?php foreach ($stages as $s): ?><option value="<?= (int) $s['id'] ?>"<?= selected(input('stage'), $s['id']) ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="status" aria-label="Status"><?= options(['' => 'Any status', 'active' => 'In process', 'hired' => 'Hired', 'rejected' => 'Rejected', 'pool' => 'Talent pool'], input('status')) ?></select>
  <select class="select" name="source" aria-label="Source"><?= options(['' => 'Any source'] + CANDIDATE_SOURCES, input('source')) ?></select>
  <button class="btn btn--quiet btn--sm" type="submit">Filter</button>
  <?php if (qs(['page' => null]) !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('candidates')) ?>">Clear</a><?php endif; ?>
</form>
<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('users-three') ?><h3>No candidates match.</h3><p>Applications from the careers page land here automatically. You can also add people you've sourced yourself.</p></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Candidate</th><th>Job</th><th>Stage</th><th>Score</th><th>Applied</th><th>Source</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $name = candidate_name($r); $href = $r['app_id'] ? admin_url('applications/' . $r['app_id']) : admin_url('candidates/' . $r['id']); ?>
      <tr>
        <td><div class="person"><?= avatar($name) ?><span><a class="t-strong" href="<?= e($href) ?>"><?= e($name) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$r['current_title'], $r['location']])) ?: $r['email']) ?></span></span></div></td>
        <td><?= $r['job_title'] ? e($r['job_title']) : '<span class="muted">Talent pool</span>' ?></td>
        <td><?= $r['app_id'] ? stage_badge($stages[(int) $r['stage_id']] ?? null) : '' ?></td>
        <td><?= $r['avg_rating'] ? stars((float) $r['avg_rating']) : '<span class="muted">-</span>' ?></td>
        <td class="nowrap muted"><?= e(fmt_date($r['applied_at'] ?? $r['created_at'])) ?></td>
        <td class="muted"><?= e(CANDIDATE_SOURCES[$r['app_source'] ?? $r['source'] ?? ''] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
