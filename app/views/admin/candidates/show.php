<?php /** @var array $c @var array $apps @var array $files @var array $timeline @var array $openJobs */ $name = candidate_name($c); $stages = ats_stages(); ?>
<a class="crumb" href="<?= e(admin_url('candidates')) ?>"><?= icon('arrow-left') ?>Candidates</a>
<div class="profile">
  <?= avatar($name, 'lg') ?>
  <div>
    <h1><?= e($name) ?></h1>
    <div class="profile__meta">
      <span><?= icon('envelope-simple') ?><a class="link" href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></span>
      <?php if ($c['phone']): ?><span><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $c['phone'])) ?>"><?= e($c['phone']) ?></a></span><?php endif; ?>
      <?php if ($c['location']): ?><span><?= icon('map-pin') ?><?= e($c['location']) ?></span><?php endif; ?>
      <?php if ($c['linkedin_url']): ?><span><?= icon('linkedin-logo') ?><a class="link" href="<?= e($c['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a></span><?php endif; ?>
    </div>
  </div>
  <div class="profile__actions">
    <?php if (user_can('ats.manage')): ?><a class="btn btn--quiet" href="<?= e(admin_url('candidates/' . $c['id'] . '/edit')) ?>"><?= icon('pencil-simple') ?>Edit</a><?php endif; ?>
    <?php if (user_can('ats.manage') && user_can('data.delete')): ?><form method="post" action="<?= e(admin_url('candidates/' . $c['id'] . '/delete')) ?>" data-confirm="Delete <?= e($name) ?>, every application, note and file? This can't be undone."><?= csrf_field() ?><button class="btn btn--danger" type="submit"><?= icon('trash') ?>Delete</button></form><?php endif; ?>
  </div>
</div>
<div class="split">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Applications</h2></div>
      <?php if ($apps): ?>
      <div class="list">
        <?php foreach ($apps as $a): ?>
          <div class="list__item">
            <div class="list__main"><a class="list__title" href="<?= e(admin_url('applications/' . $a['id'])) ?>"><?= e($a['job_title']) ?></a><span class="list__sub">Applied <?= e(fmt_date($a['applied_at'])) ?> · <?= e(CANDIDATE_SOURCES[$a['source']] ?? (string) $a['source']) ?><?= $a['rejection_reason'] ? ' · ' . e($a['rejection_reason']) : '' ?></span></div>
            <?= stage_badge($stages[(int) $a['stage_id']] ?? null) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?><div class="empty empty--sm"><p>In the talent pool, not in any pipeline yet.</p></div><?php endif; ?>
      <?php if ($openJobs && user_can('ats.manage')): ?>
      <form method="post" action="<?= e(admin_url('candidates/' . $c['id'] . '/apply')) ?>" class="panel__foot row">
        <?= csrf_field() ?>
        <select class="select select--sm" name="job_id" aria-label="Job" style="max-width:320px"><?php foreach ($openJobs as $j): ?><option value="<?= (int) $j['id'] ?>"><?= e($j['title']) ?></option><?php endforeach; ?></select>
        <button class="btn btn--quiet btn--sm" type="submit"><?= icon('plus') ?>Add to job</button>
      </form>
      <?php endif; ?>
    </div>
    <div class="panel">
      <div class="panel__head"><h2>History</h2></div>
      <div class="panel__body"><?php partial('admin/partials/timeline', ['items' => $timeline]); ?></div>
    </div>
  </div>
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Profile</h2></div>
      <div class="panel__body"><dl class="dl">
        <dt>Current role</dt><dd><?= e(implode(' at ', array_filter([$c['current_title'], $c['current_company']])) ?: '-') ?></dd>
        <dt>Experience</dt><dd><?= $c['experience_years'] !== null ? e((string) (float) $c['experience_years']) . ' years' : '-' ?></dd>
        <dt>Expected salary</dt><dd><?= e($c['expected_salary'] ?: '-') ?></dd>
        <dt>Notice period</dt><dd><?= e($c['notice_period'] ?: '-') ?></dd>
        <dt>Portfolio</dt><dd><?= $c['portfolio_url'] ? '<a class="link" href="' . e($c['portfolio_url']) . '" target="_blank" rel="noopener noreferrer">' . e(preg_replace('~^https?://~', '', (string) $c['portfolio_url'])) . '</a>' : '-' ?></dd>
        <dt>Source</dt><dd><?= e(CANDIDATE_SOURCES[$c['source']] ?? (string) $c['source']) ?></dd>
        <dt>Tags</dt><dd><?php $tags = tag_list($c['tags']); if ($tags): ?><div class="chips"><?php foreach ($tags as $t): ?><a class="chip" href="<?= e(admin_url('candidates') . '?tag=' . rawurlencode($t)) ?>"><?= e($t) ?></a><?php endforeach; ?></div><?php else: ?>-<?php endif; ?></dd>
        <dt>First seen</dt><dd><?= e(fmt_date($c['created_at'])) ?></dd>
      </dl></div>
    </div>
    <?php partial('admin/partials/files-panel', ['files' => $files, 'uploadUrl' => admin_url('candidates/' . $c['id'] . '/files')]); ?>
  </div>
</div>
