<?php /** @var array $items */ ?>
<?php if (!$items): ?>
  <div class="empty empty--sm"><p>Nothing yet. Notes, emails and stage changes will appear here.</p></div>
<?php else: ?>
<div class="timeline">
  <?php foreach ($items as $a): $meta = $a['meta'] ? (json_decode((string) $a['meta'], true) ?: []) : []; $deletable = isset(ACTIVITY_KINDS[$a['type']]) && ((int) $a['user_id'] === auth_id() || is_role('admin', 'manager')); ?>
    <div class="tl tl--<?= e($a['type']) ?>">
      <span class="tl__ic"><?= icon(activity_icon((string) $a['type'])) ?></span>
      <div>
        <div class="tl__head">
          <span class="tl__title"><?= e($a['title']) ?></span>
          <span class="tl__when" title="<?= e(fmt_datetime($a['created_at'])) ?>"><?= e($a['user_name'] ?: 'System') ?>, <?= e(time_ago($a['created_at'])) ?></span>
          <?php if ($deletable): ?>
            <form method="post" action="<?= e(admin_url('activities/' . $a['id'] . '/delete')) ?>" class="inline-form" style="margin-left:auto" data-confirm="Delete this entry?"><?= csrf_field() ?><button class="tl__del" type="submit">Delete</button></form>
          <?php endif; ?>
        </div>
        <?php if (!empty($meta['from']) && !empty($meta['to'])): ?><p class="small muted"><?= e($meta['from']) ?> to <?= e($meta['to']) ?></p><?php endif; ?>
        <?php if ($a['body'] !== null && trim((string) $a['body']) !== ''): ?><div class="tl__body"><?= e($a['body']) ?></div><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
