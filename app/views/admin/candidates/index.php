<?php /** @var array $rows @var array $p @var array $jobs */ $stages = ats_stages(); $bulk = user_can('ats.manage'); ?>
<div class="phead">
  <div><h1>Candidates</h1><p class="phead__sub"><?= plural($p['total'], 'result') ?>. One row per application. Good people you can't hire yet sit in the talent pool.</p></div>
  <div class="phead__actions">
    <?php if (user_can('data.export')): ?><a class="btn btn--quiet" href="<?= e(admin_url('candidates/export') . qs()) ?>"><?= icon('download-simple') ?>Export CSV</a><?php endif; ?>
    <?php if ($bulk): ?><a class="btn btn--primary" href="<?= e(admin_url('candidates/new')) ?>"><?= icon('user-plus') ?>Add candidate</a><?php endif; ?>
  </div>
</div>
<form class="filters" method="get" action="<?= e(admin_url('candidates')) ?>" data-autosubmit>
  <input class="input" type="search" name="q" value="<?= e(input('q')) ?>" placeholder="Name, email, phone, company, tag" aria-label="Search">
  <select class="select" name="job" aria-label="Job"><option value="">All jobs</option><?php foreach ($jobs as $j): ?><option value="<?= (int) $j['id'] ?>"<?= selected(input('job'), $j['id']) ?>><?= e($j['title']) ?><?= $j['status'] !== 'open' ? ' (' . e(JOB_STATUSES[$j['status']]) . ')' : '' ?></option><?php endforeach; ?></select>
  <select class="select" name="stage" aria-label="Stage"><option value="">All stages</option><?php foreach ($stages as $s): ?><option value="<?= (int) $s['id'] ?>"<?= selected(input('stage'), $s['id']) ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="status" aria-label="Status"><?= options(['' => 'Any status', 'active' => 'In process', 'pool' => 'Talent pool', 'hired' => 'Hired', 'rejected' => 'Rejected', 'none' => 'Not applied to a job'], input('status')) ?></select>
  <select class="select" name="source" aria-label="Source"><?= options(['' => 'Any source'] + CANDIDATE_SOURCES, input('source')) ?></select>
  <button class="btn btn--quiet btn--sm" type="submit">Filter</button>
  <?php if (qs(['page' => null]) !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('candidates')) ?>">Clear</a><?php endif; ?>
</form>
<<?= $bulk ? 'form method="post" action="' . e(admin_url('applications/bulk')) . '" data-bulk' : 'div' ?> class="panel">
  <?php if ($bulk): ?><?= csrf_field() ?>
  <div class="bulkbar" data-bulk-bar hidden>
    <span class="bulkbar__count" data-bulk-count>0 selected</span>
    <label class="sr-only" for="bulk-stage">Move to stage</label>
    <select class="select select--sm" id="bulk-stage" name="stage_id" data-bulk-stage required><option value="">Move to stage...</option><?php foreach ($stages as $s): ?><option value="<?= (int) $s['id'] ?>" data-kind="<?= e($s['kind']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    <span class="row" data-bulk-pool hidden>
      <select class="select select--sm" name="reason" aria-label="Why not now"><?php foreach (POOL_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select>
      <input class="input input--sm" type="date" name="revisit_on" aria-label="Revisit on" title="Revisit on (optional)" style="width:auto">
    </span>
    <span class="row" data-bulk-rejected hidden>
      <select class="select select--sm" name="reason" aria-label="Reason"><?php foreach (REJECTION_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select>
      <label class="checkbox small"><input type="checkbox" name="notify" value="1" checked><span>Email them</span></label>
    </span>
    <button class="btn btn--primary btn--sm" type="submit" data-busy="Moving...">Apply</button>
    <button class="btn btn--ghost btn--sm" type="button" data-bulk-clear>Clear</button>
  </div>
  <?php endif; ?>
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('users-three') ?><h3>No candidates match.</h3><p>Applications from the careers page land here automatically. You can also add people you've sourced yourself.</p></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><?php if ($bulk): ?><th class="tbl__check"><input type="checkbox" data-bulk-all aria-label="Select all on this page"></th><?php endif; ?><th>Candidate</th><th>Job</th><th>Stage</th><th>Score</th><th>Applied</th><th>Source</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $name = candidate_name($r); $href = $r['app_id'] ? admin_url('applications/' . $r['app_id']) : admin_url('candidates/' . $r['id']); ?>
      <tr>
        <?php if ($bulk): ?><td class="tbl__check"><?php if ($r['app_id']): ?><input type="checkbox" name="ids[]" value="<?= (int) $r['app_id'] ?>" data-bulk-item aria-label="Select <?= e($name) ?>"><?php endif; ?></td><?php endif; ?>
        <td><div class="person"><?= avatar($name) ?><span><a class="t-strong" href="<?= e($href) ?>"><?= e($name) ?></a><span class="t-sub"><?= e(implode(' · ', array_filter([$r['current_title'], $r['location']])) ?: $r['email']) ?></span></span></div></td>
        <td><?= $r['job_title'] ? e($r['job_title']) : '<span class="muted">No job yet</span>' ?></td>
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
</<?= $bulk ? 'form' : 'div' ?>>
