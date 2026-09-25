<?php
/** Follow-up tasks for one record. @var string $entityType @var int $entityId @var array $tasks */
$open = array_filter($tasks, static fn ($t) => $t['completed_at'] === null);
?>
<div class="panel">
  <div class="panel__head"><h2>Follow-ups</h2><span class="muted"><?= count($open) ? count($open) . ' open' : '' ?></span></div>
  <?php if ($tasks): ?>
    <div><?php foreach ($tasks as $t) { partial('admin/partials/task-item', ['t' => $t]); } ?></div>
  <?php endif; ?>
  <form method="post" action="<?= e(admin_url('tasks/new')) ?>" class="panel__body stack-sm" style="<?= $tasks ? 'border-top:1px solid var(--line)' : '' ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="entity_type" value="<?= e($entityType) ?>">
    <input type="hidden" name="entity_id" value="<?= (int) $entityId ?>">
    <input class="input" name="title" placeholder="Add a follow-up, e.g. Call back about the quote" aria-label="Follow-up" required maxlength="190">
    <div class="grid-2 grid-tight">
      <input class="input input--sm" name="due_at" type="datetime-local" aria-label="Due">
      <select class="select select--sm" name="assigned_to" aria-label="Assign to"><?= user_options(auth_id(), 'Me') ?></select>
    </div>
    <div><button class="btn btn--quiet btn--sm" type="submit"><?= icon('plus') ?>Add follow-up</button></div>
  </form>
</div>
