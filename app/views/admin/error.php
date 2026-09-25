<?php /** @var int $code @var string $title @var string $message */ ?>
<div class="panel"><div class="empty">
  <?= icon($code === 403 ? 'lock' : 'warning') ?>
  <h3><?= e($title) ?></h3>
  <?php if ($message): ?><p><?= e($message) ?></p><?php endif; ?>
  <a class="btn btn--quiet" href="<?= e(admin_url()) ?>">Go to the dashboard</a>
</div></div>
