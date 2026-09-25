<?php /** @var array $t  @var bool|null $showAbout */ $cls = task_due_class($t['due_at'], $t['completed_at']); ?>
<div class="task <?= e($cls) ?>">
  <form method="post" action="<?= e(admin_url('tasks/' . $t['id'] . '/toggle')) ?>"><?= csrf_field() ?>
    <button class="task__check" type="submit" aria-label="<?= $t['completed_at'] ? 'Mark as not done' : 'Mark as done' ?>"><?= icon('check') ?></button>
  </form>
  <div class="task__main">
    <div class="task__title"><?= e($t['title']) ?></div>
    <div class="task__meta">
      <?php if ($t['due_at']): ?><span class="task__due"><?= $cls === 'is-overdue' ? 'Overdue: ' : '' ?><?= e(fmt_day($t['due_at'])) ?>, <?= e(fmt_time($t['due_at'])) ?></span><?php endif; ?>
      <?php if (!empty($t['assignee'])): ?><span><?= e($t['assignee']) ?></span><?php endif; ?>
      <?php if (!empty($showAbout) && !empty($t['link'])): ?><a href="<?= e($t['link']) ?>"><?= e((string) $t['about']) ?></a><?php endif; ?>
    </div>
    <?php if ($t['notes']): ?><p class="small muted" style="margin-top:4px;white-space:pre-wrap"><?= e($t['notes']) ?></p><?php endif; ?>
  </div>
  <form method="post" action="<?= e(admin_url('tasks/' . $t['id'] . '/delete')) ?>" data-confirm="Delete this task?"><?= csrf_field() ?>
    <button class="btn btn--ghost btn--icon btn--sm" type="submit" aria-label="Delete task"><?= icon('trash') ?></button>
  </form>
</div>
