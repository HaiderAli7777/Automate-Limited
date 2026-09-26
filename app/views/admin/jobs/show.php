<?php
/** @var array $job @var array $apps @var string $tab @var array $questions */
$stages = ats_stages();
$active = count(array_filter($apps, static fn ($a) => $a['status'] === 'active'));
$hired = count(array_filter($apps, static fn ($a) => $a['status'] === 'hired'));
$publicUrl = abs_url('careers/' . $job['slug']);
?>
<a class="crumb" href="<?= e(admin_url('jobs')) ?>"><?= icon('arrow-left') ?>Jobs</a>
<div class="phead">
  <div>
    <div class="row"><h1><?= e($job['title']) ?></h1><?= badge(JOB_STATUSES[$job['status']] ?? $job['status'], JOB_STATUS_COLORS[$job['status']] ?? 'slate') ?></div>
    <p class="phead__sub"><?= e(implode(' · ', array_filter([$job['department'], $job['location'], EMPLOYMENT_TYPES[$job['employment_type']] ?? '', WORKPLACES[$job['workplace']] ?? '']))) ?>. <?= plural(count($apps), 'applicant') ?>, <?= $active ?> in process, <?= $hired ?> of <?= (int) $job['openings'] ?> hired.</p>
  </div>
  <div class="phead__actions">
    <?php if ($job['status'] === 'open'): ?>
      <a class="btn btn--quiet" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>View posting</a>
    <?php endif; ?>
    <?php if (user_can('ats.manage')): ?>
    <a class="btn btn--quiet" href="<?= e(admin_url('candidates/new') . '?job=' . $job['id']) ?>"><?= icon('user-plus') ?>Add candidate</a>
    <a class="btn btn--primary" href="<?= e(admin_url('jobs/' . $job['id'] . '/edit')) ?>"><?= icon('pencil-simple') ?>Edit</a>
    <details class="dropdown">
      <summary class="btn btn--quiet btn--icon" aria-label="More actions"><?= icon('dots-three') ?></summary>
      <div class="dropdown__menu">
        <?php foreach (['open' => 'Publish / reopen', 'paused' => 'Pause', 'closed' => 'Close', 'draft' => 'Move to draft'] as $st => $label): if ($st === $job['status']) continue; ?>
          <form method="post" action="<?= e(admin_url('jobs/' . $job['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $st ?>"><button type="submit"><?= icon(['open' => 'rocket-launch', 'paused' => 'hourglass', 'closed' => 'lock', 'draft' => 'note-pencil'][$st]) ?><?= e($label) ?></button></form>
        <?php endforeach; ?>
        <button type="button" data-copy="<?= e($publicUrl) ?>"><?= icon('copy') ?>Copy public link</button>
        <form method="post" action="<?= e(admin_url('jobs/' . $job['id'] . '/duplicate')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('copy') ?>Duplicate</button></form>
        <?php if (user_can('data.delete')): ?>
        <div class="dropdown__sep"></div>
        <form method="post" action="<?= e(admin_url('jobs/' . $job['id'] . '/delete')) ?>" data-confirm="Delete this job? This can't be undone."><?= csrf_field() ?><button type="submit" class="is-danger"><?= icon('trash') ?>Delete</button></form>
        <?php endif; ?>
      </div>
    </details>
    <?php endif; ?>
  </div>
</div>

<nav class="tabs" aria-label="Job views">
  <a href="<?= e(qs(['tab' => 'pipeline'])) ?>" class="<?= $tab === 'pipeline' ? 'is-active' : '' ?>"><?= icon('kanban') ?>Pipeline</a>
  <a href="<?= e(qs(['tab' => 'list'])) ?>" class="<?= $tab === 'list' ? 'is-active' : '' ?>"><?= icon('list') ?>Applicants <span class="count"><?= count($apps) ?></span></a>
  <a href="<?= e(qs(['tab' => 'details'])) ?>" class="<?= $tab === 'details' ? 'is-active' : '' ?>"><?= icon('file-text') ?>Posting</a>
</nav>

<?php if ($tab === 'pipeline'): ?>
  <?php if (!$apps): ?>
    <div class="panel"><div class="empty"><?= icon('users-three') ?><h3>No applicants yet.</h3><p><?= $job['status'] === 'open' ? 'The posting is live. Share the link, or add someone you sourced yourself.' : 'Publish the job to start receiving applications, or add someone you sourced yourself.' ?></p></div></div>
  <?php else: ?>
    <?php partial('admin/partials/ats-board', ['apps' => $apps]); ?>
  <?php endif; ?>
<?php elseif ($tab === 'list'): ?>
  <div class="panel">
    <?php if (!$apps): ?><div class="empty"><p>No applicants yet.</p></div><?php else: ?>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>Candidate</th><th>Stage</th><th>Score</th><th>Applied</th><th>In stage</th><th>Source</th></tr></thead>
      <tbody>
      <?php foreach ($apps as $a): $name = candidate_name($a); ?>
        <tr>
          <td><div class="person"><?= avatar($name) ?><span><a class="t-strong" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e($name) ?></a><span class="t-sub"><?= e($a['email']) ?></span></span></div></td>
          <td><?= stage_badge($stages[(int) $a['stage_id']] ?? null) ?></td>
          <td><?= $a['avg_rating'] ? stars((float) $a['avg_rating']) : '<span class="muted">-</span>' ?></td>
          <td class="nowrap"><?= e(fmt_date($a['applied_at'])) ?></td>
          <td class="nowrap muted"><?= days_since($a['stage_changed_at']) ?> days</td>
          <td class="muted"><?= e(CANDIDATE_SOURCES[$a['source']] ?? (string) $a['source']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="split">
    <div class="panel"><div class="panel__body">
      <?php if ($job['summary']): ?><p style="font-size:1.0625rem;margin-bottom:16px"><?= e($job['summary']) ?></p><?php endif; ?>
      <div class="prose-admin">
        <?php if ($job['description']): ?><h3 style="margin:10px 0 8px">About the role</h3><?= markdown($job['description']) ?><?php endif; ?>
        <?php if ($job['requirements']): ?><h3 style="margin:18px 0 8px">What they will need</h3><?= markdown($job['requirements']) ?><?php endif; ?>
        <?php if ($job['benefits']): ?><h3 style="margin:18px 0 8px">What you offer</h3><?= markdown($job['benefits']) ?><?php endif; ?>
      </div>
    </div></div>
    <div class="stack">
      <div class="panel"><div class="panel__head"><h2>Details</h2></div><div class="panel__body">
        <dl class="dl">
          <dt>Public link</dt><dd><a class="link" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">/careers/<?= e($job['slug']) ?></a></dd>
          <dt>Listed</dt><dd><?= (int) $job['listed'] ? 'Yes' : 'No, link only' ?></dd>
          <dt>Hiring manager</dt><dd><?= e((string) ($job['manager_name'] ?: 'None')) ?></dd>
          <dt>Level</dt><dd><?= e(EXPERIENCE_LEVELS[(string) $job['experience_level']] ?? 'Any') ?></dd>
          <dt>Salary</dt><dd><?= job_salary(array_merge($job, ['salary_visible' => 1])) !== '' ? e(job_salary(array_merge($job, ['salary_visible' => 1]))) . ((int) $job['salary_visible'] ? '' : ' (hidden)') : 'Not set' ?></dd>
          <dt>Published</dt><dd><?= $job['published_at'] ? e(fmt_date($job['published_at'])) : 'Not yet' ?></dd>
          <dt>Closes</dt><dd><?= $job['closes_at'] ? e(fmt_date($job['closes_at'])) : 'No closing date' ?></dd>
        </dl>
      </div></div>
      <div class="panel"><div class="panel__head"><h2>Screening questions</h2></div>
        <?php if ($questions): ?><div class="list"><?php foreach ($questions as $q): ?><div class="list__item"><div class="list__main"><span class="list__title" style="white-space:normal"><?= e($q['question']) ?></span><span class="list__sub"><?= e(QUESTION_TYPES[$q['qtype']] ?? $q['qtype']) ?><?= (int) $q['required'] ? ', required' : '' ?></span></div></div><?php endforeach; ?></div>
        <?php else: ?><div class="empty empty--sm"><p>None. Add them from Edit.</p></div><?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
