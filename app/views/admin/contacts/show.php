<?php
/** @var array|null $contact @var array $leads @var array $timeline @var array $tasks */
$isNew = !$contact;
$c = $contact ?? [];
$stages = lead_stages();
$edit = user_can('crm.manage');
?>
<a class="crumb" href="<?= e(admin_url('contacts')) ?>"><?= icon('arrow-left') ?>Contacts</a>
<div class="profile">
  <?php if (!$isNew): ?><?= avatar($c['name'], 'lg') ?><?php endif; ?>
  <div><h1><?= $isNew ? 'New contact' : e($c['name']) ?></h1><?php if (!$isNew): ?><div class="profile__meta"><?php if ($c['company']): ?><span><?= icon('buildings') ?><?= e($c['company']) ?></span><?php endif; ?><span>Added <?= e(fmt_date($c['created_at'])) ?></span></div><?php endif; ?></div>
  <?php if (!$isNew && $edit): ?>
  <div class="profile__actions">
    <a class="btn btn--primary" href="<?= e(admin_url('leads/new') . '?contact=' . $c['id']) ?>"><?= icon('plus') ?>New lead</a>
    <?php if (user_can('data.delete')): ?><form method="post" action="<?= e(admin_url('contacts/' . $c['id'] . '/delete')) ?>" data-confirm="Delete this contact?"><?= csrf_field() ?><button class="btn btn--danger" type="submit"><?= icon('trash') ?>Delete</button></form><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<div class="split">
  <div class="stack">
    <?php if (!$isNew): ?>
    <div class="panel">
      <div class="panel__head"><h2>Leads</h2></div>
      <?php if ($leads): ?>
        <div class="list"><?php foreach ($leads as $l): ?><div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('leads/' . $l['id'])) ?>"><?= e($l['title']) ?></a><span class="list__sub"><?= e(fmt_date($l['created_at'])) ?><?= $l['value'] !== null ? ' · ' . e(fmt_money($l['value'], $l['currency'])) : '' ?><?= $l['service'] ? ' · ' . e($l['service']) : '' ?></span></div><?= stage_badge($stages[(int) $l['stage_id']] ?? null) ?></div><?php endforeach; ?></div>
      <?php else: ?><div class="empty empty--sm"><p>No leads yet.</p></div><?php endif; ?>
    </div>
    <div class="panel"><div class="panel__head"><h2>History</h2></div><div class="panel__body"><?php partial('admin/partials/timeline', ['items' => $timeline]); ?></div></div>
    <?php endif; ?>
  </div>
  <div class="stack">
    <?php if (!$edit): ?>
    <div class="panel">
      <div class="panel__head"><h2>Details</h2></div>
      <div class="panel__body"><dl class="dl">
        <dt>Email</dt><dd><?= $c['email'] ? '<a class="link" href="mailto:' . e($c['email']) . '">' . e($c['email']) . '</a>' : '-' ?></dd>
        <dt>Phone</dt><dd><?= e($c['phone'] ?: '-') ?></dd>
        <dt>Company</dt><dd><?= e($c['company'] ?: '-') ?></dd>
        <dt>Job title</dt><dd><?= e($c['job_title'] ?: '-') ?></dd>
        <dt>Location</dt><dd><?= e(implode(', ', array_filter([$c['city'], $c['country']])) ?: '-') ?></dd>
        <dt>Website</dt><dd><?= e($c['website'] ?: '-') ?></dd>
        <?php if ($c['notes']): ?><dt>Notes</dt><dd class="pre"><?= e($c['notes']) ?></dd><?php endif; ?>
      </dl></div>
    </div>
    <?php else: ?>
    <form method="post" action="<?= e($isNew ? admin_url('contacts/new') : admin_url('contacts/' . $c['id'])) ?>" class="panel">
      <?= csrf_field() ?>
      <div class="panel__head"><h2>Details</h2></div>
      <div class="panel__body stack-sm">
        <?= fi('name', 'Name', $c['name'] ?? '', ['required' => true]) ?>
        <?= fi('email', 'Email', $c['email'] ?? '', ['type' => 'email']) ?>
        <?= fi('phone', 'Phone', $c['phone'] ?? '', ['type' => 'tel']) ?>
        <div class="grid-2 grid-tight">
          <?= fi('company', 'Company', $c['company'] ?? '') ?>
          <?= fi('job_title', 'Job title', $c['job_title'] ?? '') ?>
          <?= fi('city', 'City', $c['city'] ?? '') ?>
          <?= fi('country', 'Country', $c['country'] ?? '') ?>
        </div>
        <?= fi('website', 'Website', $c['website'] ?? '') ?>
        <?= ft('notes', 'Notes', $c['notes'] ?? '', ['attrs' => ['rows' => 3]]) ?>
        <div><button class="btn btn--primary btn--sm" type="submit"><?= $isNew ? 'Create contact' : 'Save' ?></button></div>
      </div>
    </form>
    <?php endif; ?>
    <?php if (!$isNew) { partial('admin/partials/tasks-panel', ['entityType' => 'contact', 'entityId' => (int) $c['id'], 'tasks' => $tasks]); } ?>
  </div>
</div>
