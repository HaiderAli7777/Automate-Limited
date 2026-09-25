<?php
/** @var array|null $iv @var array $app @var array $upcoming */
$isNew = !$iv;
$start = $iv ? ts($iv['scheduled_at']) : strtotime('tomorrow 11:00');
$panelIds = $iv ? array_map('intval', array_column($iv['panel'], 'id')) : [auth_id()];
if (old_array('panel') !== null) { $panelIds = array_map('intval', old_array('panel')); }
$errs = form_errors();
$action = $isNew ? admin_url('interviews/new') : admin_url('interviews/' . $iv['id'] . '/edit');
$busy = [];
foreach ($upcoming as $u) { $busy[substr($u['scheduled_at'], 0, 10)][] = $u; }
?>
<a class="crumb" href="<?= e(admin_url('applications/' . $app['id'])) ?>"><?= icon('arrow-left') ?><?= e(candidate_name($app)) ?>, <?= e($app['job_title']) ?></a>
<div class="phead"><div><h1><?= $isNew ? 'Schedule an interview' : 'Edit interview' ?></h1><p class="phead__sub">With <?= e(candidate_name($app)) ?> for <?= e($app['job_title']) ?>.</p></div></div>
<form method="post" action="<?= e($action) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="application_id" value="<?= (int) $app['id'] ?>">
  <div class="split">
    <div class="panel"><div class="panel__body">
      <fieldset class="fieldset">
        <legend>When and how</legend>
        <div class="grid-2">
          <?= fs('itype', 'Type', INTERVIEW_TYPES, $iv['itype'] ?? 'video') ?>
          <?= fi('title', 'Title', $iv['title'] ?? '', ['optional' => true, 'placeholder' => 'e.g. Technical interview', 'help' => 'Leave empty to use the type.']) ?>
          <?= fi('date', 'Date', date('Y-m-d', $start), ['type' => 'date', 'required' => true]) ?>
          <div class="grid-2 grid-tight">
            <?= fi('time', 'Start', date('H:i', $start), ['type' => 'time', 'required' => true, 'attrs' => ['step' => 300]]) ?>
            <?= fs('duration_minutes', 'Length', [15 => '15 min', 30 => '30 min', 45 => '45 min', 60 => '1 hour', 90 => '1.5 hours', 120 => '2 hours'], (string) ($iv['duration_minutes'] ?? 45)) ?>
          </div>
          <?= fi('meeting_url', 'Video link', $iv['meeting_url'] ?? '', ['optional' => true, 'placeholder' => 'https://meet.google.com/...']) ?>
          <?= fi('location', 'Location', $iv['location'] ?? '', ['optional' => true, 'placeholder' => 'Office address, or "We\'ll call you"']) ?>
        </div>
        <?php if (!empty($errs['date'])): ?><p class="error"><?= e($errs['date']) ?></p><?php endif; ?>
        <p class="help">Times are in <?= e(str_replace('_', ' ', date_default_timezone_get())) ?> (<?= e(date('T')) ?>). The candidate's invitation says so too.</p>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Panel</legend>
        <div class="grid-2 grid-tight">
          <?php foreach (active_users() as $u): ?>
            <label class="checkbox"><input type="checkbox" name="panel[]" value="<?= (int) $u['id'] ?>"<?= checked(in_array((int) $u['id'], $panelIds, true)) ?>><span><?= e($u['name']) ?> <span class="muted small"><?= e(role_label($u['role'])) ?></span></span></label>
          <?php endforeach; ?>
        </div>
        <?php if (!empty($errs['panel'])): ?><p class="error"><?= e($errs['panel']) ?></p><?php endif; ?>
        <p class="help">Everyone on the panel can open the CV and write a scorecard. Add interviewers from Team if someone is missing.</p>
        <?= ft('notes', 'Notes for the panel', $iv['notes'] ?? '', ['optional' => true, 'attrs' => ['rows' => 3], 'placeholder' => 'What to focus on, who covers which topic', 'help' => 'Not sent to the candidate.']) ?>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Notifications</legend>
        <?= fc('notify_candidate', $isNew ? 'Email the candidate an invitation with a calendar file' : 'If the time or place changed, email the candidate the new details', true, 'Uses the "' . ($isNew ? 'Interview invitation' : 'Interview changed') . '" template.') ?>
        <?= fc('notify_panel', 'Email the panel with the details and a calendar file', true) ?>
      </fieldset>
      <div class="form-actions"><button class="btn btn--primary" type="submit" data-busy="Saving…"><?= $isNew ? 'Schedule interview' : 'Save changes' ?></button><a class="btn btn--ghost" href="<?= e($isNew ? admin_url('applications/' . $app['id']) : admin_url('interviews/' . $iv['id'])) ?>">Cancel</a></div>
    </div></div>
    <div class="panel">
      <div class="panel__head"><h2>Team availability</h2><span class="muted">Next 14 days</span></div>
      <?php if (!$busy): ?>
        <div class="empty empty--sm"><p>No other interviews booked.</p></div>
      <?php else: ?>
        <div class="list"><?php foreach ($busy as $day => $items): ?>
          <div class="list__item" style="align-items:flex-start"><div class="list__main"><span class="list__title"><?= e(fmt_day($day . ' 12:00:00')) ?></span>
            <?php foreach ($items as $b): ?><span class="list__sub"><?= e(fmt_time($b['scheduled_at'])) ?> to <?= e(date('g:i a', (ts($b['scheduled_at']) ?? 0) + (int) $b['duration_minutes'] * 60)) ?>, <?= e($b['name']) ?></span><?php endforeach; ?>
          </div></div>
        <?php endforeach; ?></div>
      <?php endif; ?>
    </div>
  </div>
</form>
