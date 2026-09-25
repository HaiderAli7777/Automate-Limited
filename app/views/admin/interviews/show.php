<?php
/** @var array $iv @var array $feedback @var array|null $mine @var bool $canSeeAll @var bool $onPanel @var array|null $resume */
$name = candidate_name($iv);
$past = (ts($iv['scheduled_at']) ?? 0) < time();
$submitted = array_map('intval', array_column($feedback, 'user_id'));
?>
<a class="crumb" href="<?= e(admin_url('interviews')) ?>"><?= icon('arrow-left') ?>Interviews</a>
<div class="phead">
  <div>
    <div class="row"><h1><?= e($iv['title']) ?> with <?= e($name) ?></h1><?= badge(INTERVIEW_STATUSES[$iv['status']] ?? $iv['status'], INTERVIEW_STATUS_COLORS[$iv['status']] ?? 'slate') ?></div>
    <p class="phead__sub"><?= e($iv['job_title']) ?>. <?= e(date('l j F Y', ts($iv['scheduled_at']) ?? time())) ?>, <?= e(fmt_time($iv['scheduled_at'])) ?> for <?= (int) $iv['duration_minutes'] ?> minutes.</p>
  </div>
  <div class="phead__actions">
    <a class="btn btn--quiet" href="<?= e(admin_url('interviews/' . $iv['id'] . '/ics')) ?>"><?= icon('calendar-plus') ?>Add to calendar</a>
    <?php if ($iv['meeting_url']): ?><a class="btn btn--quiet" href="<?= e($iv['meeting_url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('video-camera') ?>Join call</a><?php endif; ?>
    <a class="btn btn--quiet" href="<?= e(admin_url('applications/' . $iv['application_id'])) ?>"><?= icon('identification-card') ?>Application</a>
    <?php if (user_can('ats')): ?>
      <a class="btn btn--primary" href="<?= e(admin_url('interviews/' . $iv['id'] . '/edit')) ?>"><?= icon('pencil-simple') ?>Edit or reschedule</a>
      <details class="dropdown">
        <summary class="btn btn--quiet btn--icon" aria-label="More actions"><?= icon('dots-three') ?></summary>
        <div class="dropdown__menu">
          <?php foreach (['completed' => ['check-circle', 'Mark as completed'], 'no_show' => ['x-circle', 'Mark as no-show'], 'scheduled' => ['calendar-dots', 'Mark as scheduled']] as $st => [$ic, $label]): if ($st === $iv['status']) continue; ?>
            <form method="post" action="<?= e(admin_url('interviews/' . $iv['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $st ?>"><button type="submit"><?= icon($ic) ?><?= e($label) ?></button></form>
          <?php endforeach; ?>
          <?php if ($iv['status'] !== 'cancelled'): ?>
            <div class="dropdown__sep"></div>
            <button type="button" class="is-danger" data-open-dialog="cancelDialog"><?= icon('x') ?>Cancel interview</button>
          <?php endif; ?>
        </div>
      </details>
    <?php endif; ?>
  </div>
</div>

<div class="split">
  <div class="stack">
    <?php if ($onPanel && $iv['status'] !== 'cancelled'): ?>
    <form method="post" action="<?= e(admin_url('interviews/' . $iv['id'] . '/feedback')) ?>" class="panel" id="feedback">
      <?= csrf_field() ?>
      <div class="panel__head"><h2>Your scorecard</h2><?= $mine ? '<span class="muted">Saved ' . e(time_ago($mine['updated_at'])) . '</span>' : ($past ? badge('Waiting for you', 'amber') : '<span class="muted">Fill this in after the interview</span>') ?></div>
      <div class="panel__body stack">
        <div class="field"><span class="lbl">Overall score</span>
          <div class="composer__kinds" role="radiogroup" aria-label="Overall score from 1 to 5">
            <?php foreach ([1 => 'Poor', 2 => 'Weak', 3 => 'Fair', 4 => 'Good', 5 => 'Excellent'] as $n => $label): ?>
              <label><input type="radio" name="rating" value="<?= $n ?>"<?= checked((int) ($mine['rating'] ?? 0) === $n) ?> required><?= $n ?> <?= e($label) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="field"><span class="lbl">Recommendation</span>
          <div class="composer__kinds" role="radiogroup" aria-label="Recommendation">
            <?php foreach (RECOMMENDATIONS as $k => $label): ?><label><input type="radio" name="recommendation" value="<?= $k ?>"<?= checked(($mine['recommendation'] ?? '') === $k) ?> required><?= e($label) ?></label><?php endforeach; ?>
          </div>
        </div>
        <div class="grid-2">
          <div class="field"><label for="fb-s">Strengths</label><textarea class="textarea" id="fb-s" name="strengths" rows="4"><?= e((string) ($mine['strengths'] ?? '')) ?></textarea></div>
          <div class="field"><label for="fb-c">Concerns</label><textarea class="textarea" id="fb-c" name="concerns" rows="4"><?= e((string) ($mine['concerns'] ?? '')) ?></textarea></div>
        </div>
        <div class="field"><label for="fb-n">Other notes <span class="opt">(optional)</span></label><textarea class="textarea" id="fb-n" name="notes" rows="3"><?= e((string) ($mine['notes'] ?? '')) ?></textarea></div>
        <div><button class="btn btn--primary" type="submit"><?= $mine ? 'Update scorecard' : 'Submit scorecard' ?></button></div>
      </div>
    </form>
    <?php endif; ?>

    <div class="panel">
      <div class="panel__head"><h2>Scorecards</h2><span class="muted"><?= count($feedback) ?> of <?= count($iv['panel']) ?> in</span></div>
      <?php if (!$feedback): ?>
        <div class="empty empty--sm"><p>No scorecards yet.</p></div>
      <?php elseif (!$canSeeAll): ?>
        <div class="empty empty--sm"><p>Submit your own scorecard to see what the others wrote. It keeps everyone's first impressions independent.</p></div>
      <?php else: ?>
        <div class="list">
          <?php foreach ($feedback as $f): ?>
            <div class="list__item" style="align-items:flex-start">
              <?= avatar($f['user_name']) ?>
              <div class="list__main">
                <div class="row"><strong><?= e($f['user_name']) ?></strong><?= badge(RECOMMENDATIONS[$f['recommendation']] ?? '', RECOMMENDATION_COLORS[$f['recommendation']] ?? 'slate') ?><?= stars((float) $f['rating']) ?></div>
                <?php if ($f['strengths']): ?><p class="small" style="margin-top:8px"><strong>Strengths.</strong> <span class="pre"><?= e($f['strengths']) ?></span></p><?php endif; ?>
                <?php if ($f['concerns']): ?><p class="small" style="margin-top:6px"><strong>Concerns.</strong> <span class="pre"><?= e($f['concerns']) ?></span></p><?php endif; ?>
                <?php if ($f['notes']): ?><p class="small muted" style="margin-top:6px"><span class="pre"><?= e($f['notes']) ?></span></p><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Details</h2></div>
      <div class="panel__body"><dl class="dl">
        <dt>Candidate</dt><dd><a class="link" href="<?= e(admin_url('applications/' . $iv['application_id'])) ?>"><?= e($name) ?></a></dd>
        <dt>Role</dt><dd><?= e($iv['job_title']) ?></dd>
        <dt>Type</dt><dd><?= e(INTERVIEW_TYPES[$iv['itype']] ?? $iv['itype']) ?></dd>
        <dt>When</dt><dd><?= e(fmt_day($iv['scheduled_at'])) ?>, <?= e(fmt_time($iv['scheduled_at'])) ?> (<?= (int) $iv['duration_minutes'] ?> min)</dd>
        <?php if ($iv['meeting_url']): ?><dt>Link</dt><dd><a class="link" href="<?= e($iv['meeting_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('~^https?://~', '', (string) $iv['meeting_url'])) ?></a></dd><?php endif; ?>
        <?php if ($iv['location']): ?><dt>Where</dt><dd><?= e($iv['location']) ?></dd><?php endif; ?>
        <dt>Phone</dt><dd><?= e((string) ($iv['phone'] ?: '-')) ?></dd>
      </dl></div>
    </div>
    <div class="panel">
      <div class="panel__head"><h2>Panel</h2></div>
      <div class="list"><?php foreach ($iv['panel'] as $p): ?>
        <div class="list__item"><?= avatar($p['name'], 'sm') ?><div class="list__main"><span class="list__title"><?= e($p['name']) ?></span></div><?= in_array((int) $p['id'], $submitted, true) ? badge('Scorecard in', 'green') : badge('Pending', 'slate') ?></div>
      <?php endforeach; ?></div>
    </div>
    <?php if ($iv['notes']): ?><div class="panel"><div class="panel__head"><h2>Notes for the panel</h2></div><div class="panel__body"><p class="pre small"><?= e($iv['notes']) ?></p></div></div><?php endif; ?>
    <?php if ($resume): ?><div class="panel"><div class="panel__head"><h2>CV</h2></div><div class="file-row"><span class="file-row__ic"><?= icon('file-pdf') ?></span><div class="list__main"><a class="list__title" href="<?= e(admin_url('files/' . $resume['id']) . ($resume['mime'] === 'application/pdf' ? '?inline=1' : '')) ?>" target="_blank" rel="noopener"><?= e($resume['original_name']) ?></a></div></div></div><?php endif; ?>
  </div>
</div>

<?php if (user_can('ats')): ?>
<dialog class="modal" id="cancelDialog" aria-labelledby="cancelTitle">
  <form method="post" action="<?= e(admin_url('interviews/' . $iv['id'] . '/status')) ?>">
    <?= csrf_field() ?><input type="hidden" name="status" value="cancelled">
    <div class="modal__head"><h2 id="cancelTitle">Cancel this interview?</h2></div>
    <div class="modal__body"><label class="checkbox"><input type="checkbox" name="notify_candidate" value="1" checked><span>Email <?= e($iv['first_name']) ?> that it's cancelled, with a calendar update</span></label></div>
    <div class="modal__foot"><button class="btn btn--ghost" type="button" data-close-dialog>Keep it</button><button class="btn btn--primary" type="submit">Cancel interview</button></div>
  </form>
</dialog>
<?php endif; ?>
