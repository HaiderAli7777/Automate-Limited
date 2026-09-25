<?php /** @var array $files @var string|null $uploadUrl */ ?>
<div class="panel">
  <div class="panel__head"><h2>CV and files</h2></div>
  <?php if ($files): ?>
    <div>
      <?php foreach ($files as $f): ?>
        <div class="file-row">
          <span class="file-row__ic"><?= icon($f['mime'] === 'application/pdf' ? 'file-pdf' : 'file-text') ?></span>
          <div class="list__main"><a class="list__title" href="<?= e(admin_url('files/' . $f['id'])) ?>"><?= e($f['original_name']) ?></a><span class="list__sub"><?= $f['kind'] === 'resume' ? 'CV · ' : '' ?><?= e(human_size((int) $f['size'])) ?> · <?= e(fmt_date($f['created_at'])) ?></span></div>
          <?php if ($f['mime'] === 'application/pdf'): ?><a class="btn btn--ghost btn--icon btn--sm" href="<?= e(admin_url('files/' . $f['id']) . '?inline=1') ?>" target="_blank" rel="noopener" aria-label="Open in a new tab"><?= icon('eye') ?></a><?php endif; ?>
          <a class="btn btn--ghost btn--icon btn--sm" href="<?= e(admin_url('files/' . $f['id'])) ?>" aria-label="Download"><?= icon('download-simple') ?></a>
          <?php if (user_can('ats')): ?>
          <form method="post" action="<?= e(admin_url('files/' . $f['id'] . '/delete')) ?>" data-confirm="Delete <?= e($f['original_name']) ?>?"><?= csrf_field() ?><button class="btn btn--ghost btn--icon btn--sm" type="submit" aria-label="Delete file"><?= icon('trash') ?></button></form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?><div class="empty empty--sm"><p>No files yet.</p></div><?php endif; ?>
  <?php if (!empty($uploadUrl) && user_can('ats')): ?>
  <form method="post" action="<?= e($uploadUrl) ?>" enctype="multipart/form-data" class="panel__foot stack-sm">
    <?= csrf_field() ?>
    <input class="input input--sm" type="file" name="file" required aria-label="File">
    <div class="row"><select class="select select--sm" name="kind" aria-label="File type" style="width:auto"><option value="resume">This is a new CV</option><option value="attachment">Other file</option></select><button class="btn btn--quiet btn--sm" type="submit"><?= icon('upload-simple') ?>Upload</button></div>
  </form>
  <?php endif; ?>
</div>
