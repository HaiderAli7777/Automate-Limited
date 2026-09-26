<?php /** @var array $rows  @var array $p */ $unread = nav_counts()['notifications']; ?>
<div class="phead">
  <div><h1>Notifications</h1><p class="phead__sub">New applications, enquiries, and anything assigned to you.</p></div>
  <?php if ($unread): ?><div class="phead__actions"><form method="post" action="<?= e(admin_url('notifications/read-all')) ?>"><?= csrf_field() ?><button class="btn btn--quiet btn--sm" type="submit"><?= icon('checks') ?>Mark all as read</button></form></div><?php endif; ?>
</div>
<div class="panel">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('bell') ?><h3>Nothing yet</h3><p>When a candidate applies, an enquiry comes in, or someone assigns you work, it shows up here and on the bell at the top.</p></div>
  <?php else: ?>
  <div class="list">
    <?php foreach ($rows as $n): ?>
      <a class="notif notif--row<?= $n['read_at'] ? '' : ' is-unread' ?>" href="<?= e(admin_url('notifications/' . $n['id'] . '/open')) ?>">
        <span class="notif__ic"><?= icon((string) ($n['icon'] ?: 'bell')) ?></span>
        <span class="notif__main"><span class="notif__title"><?= e($n['title']) ?></span><?php if ($n['body']): ?><span class="notif__body"><?= e($n['body']) ?></span><?php endif; ?></span>
        <span class="notif__when" title="<?= e(fmt_datetime($n['created_at'])) ?>"><?= e(time_ago($n['created_at'])) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <?= pager($p) ?>
  <?php endif; ?>
</div>
