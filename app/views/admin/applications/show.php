<?php
/** @var array $app @var bool $full @var array $answers @var array|null $resume @var array $files @var array $interviews
 *  @var array $timeline @var array $others @var array $tasks @var array $templates @var array $summary */
$stages = ats_stages();
$name = candidate_name($app);
$current = $stages[(int) $app['stage_id']] ?? null;
$reached = true;
$recCounts = [];
foreach ($interviews as $iv) {
    foreach ($iv['feedback'] as $f) {
        $recCounts[$f['recommendation']] = ($recCounts[$f['recommendation']] ?? 0) + 1;
    }
}
?>
<a class="crumb" href="<?= e($full ? admin_url('jobs/' . $app['job_id']) : admin_url('interviews')) ?>"><?= icon('arrow-left') ?><?= e($app['job_title']) ?></a>
<div class="profile">
  <?= avatar($name, 'lg') ?>
  <div>
    <div class="row"><h1><?= e($name) ?></h1><?= stage_badge($current) ?></div>
    <div class="profile__meta">
      <span><?= icon('envelope-simple') ?><a class="link" href="mailto:<?= e($app['email']) ?>"><?= e($app['email']) ?></a></span>
      <?php if ($app['phone']): ?><span><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $app['phone'])) ?>"><?= e($app['phone']) ?></a></span><?php endif; ?>
      <?php if ($app['location']): ?><span><?= icon('map-pin') ?><?= e($app['location']) ?></span><?php endif; ?>
      <?php if ($app['linkedin_url']): ?><span><?= icon('linkedin-logo') ?><a class="link" href="<?= e($app['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a></span><?php endif; ?>
    </div>
  </div>
  <?php if ($full): ?>
  <div class="profile__actions">
    <?php if ($edit): ?>
    <button class="btn btn--quiet" type="button" data-open-dialog="emailDialog"><?= icon('envelope-simple') ?>Email</button>
    <a class="btn btn--primary" href="<?= e(admin_url('interviews/new') . '?application=' . $app['id']) ?>"><?= icon('calendar-plus') ?>Schedule interview</a>
    <?php else: ?><span class="chip"><?= icon('eye') ?>View only</span><?php endif; ?>
    <a class="btn btn--ghost" href="<?= e(admin_url('candidates/' . $app['candidate_id'])) ?>">Full profile</a>
  </div>
  <?php endif; ?>
</div>

<?php if ($full): ?>
<div class="panel" style="margin-bottom:18px"><div class="panel__body">
  <div class="stepper" role="group" aria-label="Move to stage">
    <?php foreach ($stages as $sid => $s):
        $isCurrent = (int) $sid === (int) $app['stage_id'];
        $cls = ($isCurrent ? 'is-current' : ($reached && $s['kind'] === 'active' ? 'is-done' : '')) . ($s['kind'] === 'rejected' ? ' is-negative' : ($s['kind'] === 'hired' ? ' is-positive' : ''));
        if ($isCurrent) { $reached = false; }
    ?>
      <?php if (!$edit): ?>
        <button type="button" class="<?= e($cls) ?>" disabled<?= $isCurrent ? ' aria-current="step"' : '' ?>><?= e($s['name']) ?></button>
      <?php elseif ($s['kind'] === 'rejected' && !$isCurrent): ?>
        <button type="button" class="<?= e($cls) ?>" data-open-dialog="rejectOneDialog" data-stage="<?= (int) $sid ?>"><?= e($s['name']) ?></button>
      <?php elseif ($s['kind'] === 'pool' && !$isCurrent): ?>
        <button type="button" class="<?= e($cls) ?>" data-open-dialog="poolOneDialog"><?= e($s['name']) ?></button>
      <?php else: ?>
        <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/stage')) ?>"><?= csrf_field() ?><input type="hidden" name="stage_id" value="<?= (int) $sid ?>">
          <button type="submit" class="<?= e($cls) ?>"<?= $isCurrent ? ' aria-current="step" disabled' : '' ?>><?= e($s['name']) ?></button></form>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php if ($app['status'] === 'rejected' && $app['rejection_reason']): ?><p class="small muted" style="margin-top:10px">Not moving forward: <?= e($app['rejection_reason']) ?>, <?= e(fmt_date($app['rejected_at'])) ?>.</p><?php endif; ?>
  <?php if ($app['status'] === 'pool'): ?>
    <div class="notice notice--info poolnote"><?= icon('user-list') ?><div><strong>In the talent pool</strong> since <?= e(fmt_date($app['pooled_at'])) ?><?= $app['pool_reason'] ? ': ' . e($app['pool_reason']) : '' ?>.<?php if ($app['revisit_on']): ?> Revisit on <strong><?= e(fmt_date($app['revisit_on'])) ?></strong>.<?php endif; ?><?php if ($app['pool_note']): ?><br><span class="small"><?= e($app['pool_note']) ?></span><?php endif; ?> Move them to any stage above when the time is right.</div></div>
  <?php endif; ?>
  <?php if ($app['status'] === 'hired' && user_can('hr.view')): $empId = db()->value('SELECT id FROM employees WHERE application_id = ?', [(int) $app['id']]); ?>
    <div class="notice notice--ok poolnote"><?= icon('identification-card') ?><div>
      <?php if ($empId): ?><strong>Now an employee.</strong> <a class="link" href="<?= e(admin_url('employees/' . $empId)) ?>">Open their employee profile</a>.
      <?php elseif (user_can('hr.manage')): ?><strong>Hired.</strong> Create their employee profile to add them to payroll. <a class="btn btn--primary btn--sm" style="margin-left:8px" href="<?= e(admin_url('employees/new') . '?application=' . $app['id']) ?>"><?= icon('user-plus') ?>Convert to employee</a>
      <?php else: ?><strong>Hired.</strong> Ask HR to create their employee profile.<?php endif; ?>
    </div></div>
  <?php endif; ?>
</div></div>
<?php endif; ?>

<div class="split split--wide-side">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>CV</h2>
        <?php if ($resume): ?><div class="row"><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('files/' . $resume['id']) . '?inline=1') ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>Open</a><a class="btn btn--ghost btn--sm" href="<?= e(admin_url('files/' . $resume['id'])) ?>"><?= icon('download-simple') ?>Download</a></div><?php endif; ?>
      </div>
      <?php if ($resume && $resume['mime'] === 'application/pdf'): ?>
        <iframe class="viewer" src="<?= e(admin_url('files/' . $resume['id']) . '?inline=1') ?>" title="CV of <?= e($name) ?>" loading="lazy"></iframe>
      <?php elseif ($resume): ?>
        <div class="empty empty--sm"><?= icon('file-text') ?><p><?= e($resume['original_name']) ?> is a Word file. Download it to read it.</p><a class="btn btn--quiet btn--sm" href="<?= e(admin_url('files/' . $resume['id'])) ?>">Download CV</a></div>
      <?php else: ?>
        <div class="empty empty--sm"><p>No CV on this application.<?= $full ? ' Upload one under Files.' : '' ?></p></div>
      <?php endif; ?>
    </div>

    <?php if ($app['cover_letter'] || $answers): ?>
    <div class="panel">
      <div class="panel__head"><h2>Application</h2><span class="muted">Applied <?= e(fmt_datetime($app['applied_at'])) ?></span></div>
      <div class="panel__body stack">
        <?php if ($app['cover_letter']): ?><div><p class="small muted" style="margin-bottom:6px">Why this role</p><p class="pre"><?= e($app['cover_letter']) ?></p></div><?php endif; ?>
        <?php if ($answers): ?><dl class="dl dl--stack"><?php foreach ($answers as $a): ?><dt><?= e($a['question']) ?></dt><dd><strong><?= $a['answer'] !== null && $a['answer'] !== '' ? e($a['answer']) : '<span class="muted">No answer</span>' ?></strong></dd><?php endforeach; ?></dl><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel__head"><h2>Interviews</h2><?php if ($edit): ?><a class="btn btn--quiet btn--sm" href="<?= e(admin_url('interviews/new') . '?application=' . $app['id']) ?>"><?= icon('calendar-plus') ?>Schedule</a><?php endif; ?></div>
      <?php if (!$interviews): ?>
        <div class="empty empty--sm"><p>No interviews yet.</p></div>
      <?php else: ?>
        <div class="list">
          <?php foreach ($interviews as $iv): $mine = in_array(auth_id(), array_map('intval', array_column($iv['panel'], 'id')), true); ?>
            <div class="list__item" style="align-items:flex-start">
              <div class="date-tile"><span><?= e(fmt_date($iv['scheduled_at'], 'M')) ?></span><b><?= e(fmt_date($iv['scheduled_at'], 'j')) ?></b></div>
              <div class="list__main">
                <a class="list__title" href="<?= e(admin_url('interviews/' . $iv['id'])) ?>"><?= e($iv['title']) ?></a>
                <span class="list__sub"><?= e(fmt_day($iv['scheduled_at'])) ?>, <?= e(fmt_time($iv['scheduled_at'])) ?> · <?= (int) $iv['duration_minutes'] ?> min · <?= e(implode(', ', array_column($iv['panel'], 'name')) ?: 'No panel') ?></span>
                <?php if ($iv['feedback']): ?>
                  <div class="chips" style="margin-top:8px"><?php foreach ($iv['feedback'] as $f): ?><?= badge($f['user_name'] . ': ' . (RECOMMENDATIONS[$f['recommendation']] ?? $f['recommendation']) . ', ' . (int) $f['rating'] . '/5', RECOMMENDATION_COLORS[$f['recommendation']] ?? 'slate', 'badge--plain') ?><?php endforeach; ?></div>
                <?php endif; ?>
              </div>
              <div class="stack-sm" style="justify-items:end">
                <?= badge(INTERVIEW_STATUSES[$iv['status']] ?? $iv['status'], INTERVIEW_STATUS_COLORS[$iv['status']] ?? 'slate') ?>
                <?php if ($mine && $iv['status'] !== 'cancelled'): ?><a class="btn btn--quiet btn--sm" href="<?= e(admin_url('interviews/' . $iv['id'])) ?>#feedback">Scorecard</a><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel" id="timeline">
      <div class="panel__head"><h2>Notes and history</h2></div>
      <div class="panel__body">
        <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/note')) ?>" class="composer" style="margin-bottom:22px">
          <?= csrf_field() ?>
          <div class="composer__kinds" role="radiogroup" aria-label="What are you logging?">
            <?php foreach (['note' => 'Note', 'call' => 'Call', 'meeting' => 'Meeting'] as $k => $label): ?>
              <label><input type="radio" name="kind" value="<?= $k ?>"<?= $k === 'note' ? ' checked' : '' ?>><?= icon(activity_icon($k)) ?><?= e($label) ?></label>
            <?php endforeach; ?>
          </div>
          <textarea class="textarea" name="body" rows="3" placeholder="What happened, what you think, what's next" aria-label="Note" required></textarea>
          <div><button class="btn btn--primary btn--sm" type="submit">Save</button></div>
        </form>
        <?php partial('admin/partials/timeline', ['items' => $timeline]); ?>
      </div>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Summary</h2></div>
      <div class="panel__body">
        <dl class="dl">
          <dt>Stage</dt><dd><?= stage_badge($current) ?> <span class="muted small"><?= days_since($app['stage_changed_at']) ?> days</span></dd>
          <dt>Applied</dt><dd><?= e(fmt_date($app['applied_at'])) ?></dd>
          <dt>Source</dt><dd><?= e(CANDIDATE_SOURCES[$app['source']] ?? (string) $app['source']) ?></dd>
          <dt>Interview score</dt><dd><?= stars($summary['avg']) ?><?= $summary['count'] ? ' <span class="muted small">from ' . plural($summary['count'], 'scorecard') . '</span>' : '' ?></dd>
          <?php if ($recCounts): ?><dt>Recommendations</dt><dd><div class="chips"><?php foreach (RECOMMENDATIONS as $k => $label): if (empty($recCounts[$k])) continue; ?><?= badge($label . ' ' . $recCounts[$k], RECOMMENDATION_COLORS[$k], 'badge--plain') ?><?php endforeach; ?></div></dd><?php endif; ?>
        </dl>
        <?php if ($edit): ?>
        <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/owner')) ?>" class="row" style="margin-top:16px" data-autosubmit>
          <?= csrf_field() ?>
          <label class="small muted" for="owner">Owner</label>
          <select class="select select--sm" id="owner" name="owner_id" style="flex:1"><?= user_options(isset($app['owner_id']) ? (int) $app['owner_id'] : null, 'Nobody', 'ats.manage') ?></select>
          <noscript><button class="btn btn--quiet btn--sm" type="submit">Save</button></noscript>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Candidate</h2></div>
      <div class="panel__body"><dl class="dl">
        <dt>Current role</dt><dd><?= e(implode(' at ', array_filter([$app['current_title'], $app['current_company']])) ?: '-') ?></dd>
        <dt>Experience</dt><dd><?= $app['experience_years'] !== null ? e((string) (float) $app['experience_years']) . ' years' : '-' ?></dd>
        <dt>Expected salary</dt><dd><?= e($app['expected_salary'] ?: '-') ?></dd>
        <dt>Notice period</dt><dd><?= e($app['notice_period'] ?: '-') ?></dd>
        <?php if ($app['portfolio_url']): ?><dt>Portfolio</dt><dd><a class="link" href="<?= e($app['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('~^https?://~', '', (string) $app['portfolio_url'])) ?></a></dd><?php endif; ?>
        <?php $tags = tag_list($app['tags']); if ($tags): ?><dt>Tags</dt><dd><div class="chips"><?php foreach ($tags as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?></div></dd><?php endif; ?>
      </dl></div>
    </div>

    <?php if ($edit): ?>
    <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/offer')) ?>" class="panel">
      <?= csrf_field() ?>
      <div class="panel__head"><h2>Offer</h2><?= $app['offer_status'] ? badge(OFFER_STATUSES[$app['offer_status']] ?? '', ['draft' => 'slate', 'sent' => 'blue', 'accepted' => 'green', 'declined' => 'red'][$app['offer_status']] ?? 'slate') : '' ?></div>
      <div class="panel__body stack-sm">
        <div class="grid-2 grid-tight">
          <div class="field"><label for="of-salary">Salary</label><input class="input input--sm" id="of-salary" name="offer_salary" value="<?= e((string) $app['offer_salary']) ?>" placeholder="PKR 250,000 / month"></div>
          <div class="field"><label for="of-start">Start date</label><input class="input input--sm" id="of-start" type="date" name="offer_start_date" value="<?= e((string) $app['offer_start_date']) ?>"></div>
        </div>
        <div class="field"><label for="of-status">Status</label><select class="select select--sm" id="of-status" name="offer_status"><?= options(OFFER_STATUSES, (string) $app['offer_status']) ?></select></div>
        <div class="field"><label for="of-notes">Notes</label><textarea class="textarea" id="of-notes" name="offer_notes" rows="2"><?= e((string) $app['offer_notes']) ?></textarea></div>
        <?php if ($app['status'] !== 'hired'): ?><label class="checkbox small"><input type="checkbox" name="mark_hired" value="1" checked><span>If accepted, move to Hired</span></label><?php endif; ?>
        <div><button class="btn btn--quiet btn--sm" type="submit">Save offer</button></div>
      </div>
    </form>
    <?php endif; ?>
    <?php if ($full) partial('admin/partials/tasks-panel', ['entityType' => 'application', 'entityId' => (int) $app['id'], 'tasks' => $tasks]); ?>

    <?php partial('admin/partials/files-panel', ['files' => $files, 'uploadUrl' => $edit ? admin_url('candidates/' . $app['candidate_id'] . '/files') : null]); ?>

    <?php if ($others && $full): ?>
    <div class="panel">
      <div class="panel__head"><h2>Other applications</h2></div>
      <div class="list"><?php foreach ($others as $o): ?><div class="list__item"><div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $o['id'])) ?>"><?= e($o['title']) ?></a></div><?= stage_badge($stages[(int) $o['stage_id']] ?? null) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($edit): ?>
<dialog class="modal modal--wide" id="emailDialog" aria-labelledby="emailTitle">
  <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/email')) ?>">
    <?= csrf_field() ?>
    <div class="modal__head"><h2 id="emailTitle">Email <?= e($app['first_name']) ?></h2><button class="btn btn--ghost btn--icon" type="button" data-close-dialog aria-label="Close"><?= icon('x') ?></button></div>
    <div class="modal__body">
      <div class="field"><label for="em-tpl">Start from a template</label>
        <select class="select" id="em-tpl" data-template-picker data-preview-url="<?= e(admin_url('applications/' . $app['id'] . '/email-preview')) ?>"><option value="">Choose a template</option><?php foreach ($templates as $t): ?><option value="<?= e($t['tkey']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="field"><label for="em-to">To</label><input class="input" id="em-to" value="<?= e($name . ' <' . $app['email'] . '>') ?>" readonly></div>
      <div class="field"><label for="em-subject">Subject</label><input class="input" id="em-subject" name="subject" required value="<?= e($app['job_title'] . ' at ' . company_name()) ?>"></div>
      <div class="field"><label for="em-body">Message</label><textarea class="textarea textarea--tall" id="em-body" name="body" required></textarea><p class="help">Replies go to <?= e(auth_user()['email']) ?>. The email is saved to the timeline.</p></div>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit" data-busy="Sending…"><?= icon('envelope-simple') ?>Send</button></div>
  </form>
</dialog>

<?php partial('admin/partials/pool-dialog', ['id' => 'poolOneDialog', 'method' => 'post', 'action' => admin_url('applications/' . $app['id'] . '/stage'), 'stageId' => first_stage_id($stages, 'pool')]); ?>
<dialog class="modal" id="rejectOneDialog" aria-labelledby="rejectOneTitle">
  <form method="post" action="<?= e(admin_url('applications/' . $app['id'] . '/stage')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="stage_id" value="<?= (int) first_stage_id($stages, 'rejected') ?>">
    <div class="modal__head"><h2 id="rejectOneTitle">Not moving forward with <?= e($app['first_name']) ?></h2></div>
    <div class="modal__body">
      <div class="field"><label for="rj1-reason">Reason</label><select class="select" id="rj1-reason" name="reason"><?php foreach (REJECTION_REASONS as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select><p class="help">Only your team sees this.</p></div>
      <label class="checkbox"><input type="checkbox" name="notify" value="1" checked><span>Email <?= e($app['first_name']) ?> the "Not moving forward" message</span></label>
    </div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Cancel</button><button class="btn btn--primary" type="submit">Confirm</button></div>
  </form>
</dialog>
<?php endif; ?>
