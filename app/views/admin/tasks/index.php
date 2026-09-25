<?php /** @var array $tasks @var string $view @var string $status */ ?>
<div class="phead">
  <div><h1>Tasks</h1><p class="phead__sub">Follow-ups on leads and candidates, and anything else on your list.</p></div>
</div>
<div class="split">
  <div>
    <div class="row" style="margin-bottom:14px">
      <?php if (user_can('ats') || user_can('crm')): ?>
      <nav class="seg" aria-label="Whose tasks">
        <a href="<?= e(qs(['view' => 'mine'])) ?>" class="<?= $view === 'mine' ? 'is-active' : '' ?>">Mine</a>
        <a href="<?= e(qs(['view' => 'created'])) ?>" class="<?= $view === 'created' ? 'is-active' : '' ?>">I assigned</a>
        <a href="<?= e(qs(['view' => 'all'])) ?>" class="<?= $view === 'all' ? 'is-active' : '' ?>">Everyone</a>
      </nav>
      <?php endif; ?>
      <nav class="seg" aria-label="Status">
        <a href="<?= e(qs(['status' => 'open'])) ?>" class="<?= $status !== 'done' ? 'is-active' : '' ?>">To do</a>
        <a href="<?= e(qs(['status' => 'done'])) ?>" class="<?= $status === 'done' ? 'is-active' : '' ?>">Done</a>
      </nav>
    </div>
    <div class="panel">
      <?php if (!$tasks): ?>
        <div class="empty"><?= icon('check-square') ?><h3><?= $status === 'done' ? 'Nothing finished yet.' : 'Nothing on the list.' ?></h3><p>Add follow-ups here, or from any lead or candidate.</p></div>
      <?php else: ?>
        <?php foreach ($tasks as $t) { partial('admin/partials/task-item', ['t' => $t, 'showAbout' => true]); } ?>
      <?php endif; ?>
    </div>
  </div>
  <form method="post" action="<?= e(admin_url('tasks/new')) ?>" class="panel" id="new-task">
    <?= csrf_field() ?>
    <div class="panel__head"><h2>New task</h2></div>
    <div class="panel__body stack-sm">
      <div class="field"><label for="t-title">What needs doing?</label><input class="input" id="t-title" name="title" required maxlength="190"></div>
      <div class="field"><label for="t-due">Due</label><input class="input" id="t-due" name="due_at" type="datetime-local"></div>
      <div class="field"><label for="t-who">Assign to</label><select class="select" id="t-who" name="assigned_to"><?= user_options(auth_id(), 'Me') ?></select></div>
      <div class="field"><label for="t-notes">Notes <span class="opt">(optional)</span></label><textarea class="textarea" id="t-notes" name="notes" rows="3"></textarea></div>
      <div><button class="btn btn--primary" type="submit">Add task</button></div>
    </div>
  </form>
</div>
