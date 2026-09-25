<?php /** @var array $apps @var array $jobs @var int $jobId @var string $q */ ?>
<div class="phead">
  <div><h1>Hiring pipeline</h1><p class="phead__sub"><?= $jobId ? 'One job.' : 'Every open job.' ?> Finished candidates stay visible for 30 days.</p></div>
  <form class="phead__actions" method="get" action="<?= e(admin_url('pipeline')) ?>" data-autosubmit>
    <input class="input input--sm" type="search" name="q" value="<?= e($q) ?>" placeholder="Find a candidate" aria-label="Find a candidate" style="width:200px">
    <select class="select select--sm" name="job" aria-label="Job" style="width:auto"><option value="">All open jobs</option><?php foreach ($jobs as $j): ?><option value="<?= (int) $j['id'] ?>"<?= selected($jobId, $j['id']) ?>><?= e($j['title']) ?></option><?php endforeach; ?></select>
    <a class="btn btn--primary btn--sm" href="<?= e(admin_url('candidates/new') . ($jobId ? '?job=' . $jobId : '')) ?>"><?= icon('user-plus') ?>Add candidate</a>
  </form>
</div>
<?php if (!$apps && !$jobs): ?>
  <div class="panel"><div class="empty"><?= icon('kanban') ?><h3>No open jobs yet.</h3><p>Publish a job and applications will flow into this board.</p><a class="btn btn--primary" href="<?= e(admin_url('jobs/new')) ?>">Create a job</a></div></div>
<?php else: ?>
  <?php partial('admin/partials/ats-board', ['apps' => $apps, 'showJob' => !$jobId]); ?>
<?php endif; ?>
