<?php
/** @var array|null $candidate @var array $jobs */
$isNew = !$candidate;
$c = $candidate ?? ['source' => 'linkedin'];
$action = $isNew ? admin_url('candidates/new') : admin_url('candidates/' . $candidate['id'] . '/edit');
?>
<a class="crumb" href="<?= e($isNew ? admin_url('candidates') : admin_url('candidates/' . $candidate['id'])) ?>"><?= icon('arrow-left') ?><?= $isNew ? 'Candidates' : e(candidate_name($candidate)) ?></a>
<div class="phead"><div><h1><?= $isNew ? 'Add a candidate' : 'Edit profile' ?></h1><?php if ($isNew): ?><p class="phead__sub">For people you found yourself: referrals, LinkedIn, agencies. Careers page applicants are added automatically.</p><?php endif; ?></div></div>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="split">
    <div class="panel"><div class="panel__body">
      <fieldset class="fieldset">
        <legend>Contact details</legend>
        <div class="grid-2">
          <?= fi('first_name', 'First name', $c['first_name'] ?? '', ['required' => true]) ?>
          <?= fi('last_name', 'Last name', $c['last_name'] ?? '') ?>
          <?= fi('email', 'Email', $c['email'] ?? '', ['type' => 'email', 'required' => true]) ?>
          <?= fi('phone', 'Phone', $c['phone'] ?? '', ['type' => 'tel', 'optional' => true]) ?>
          <?= fi('location', 'Location', $c['location'] ?? '', ['optional' => true]) ?>
          <?= fi('linkedin_url', 'LinkedIn', $c['linkedin_url'] ?? '', ['optional' => true]) ?>
        </div>
      </fieldset>
      <fieldset class="fieldset">
        <legend>Experience</legend>
        <div class="grid-2">
          <?= fi('current_title', 'Current title', $c['current_title'] ?? '', ['optional' => true]) ?>
          <?= fi('current_company', 'Current employer', $c['current_company'] ?? '', ['optional' => true]) ?>
          <?= fi('experience_years', 'Years of experience', $c['experience_years'] ?? '', ['optional' => true, 'attrs' => ['inputmode' => 'decimal']]) ?>
          <?= fi('notice_period', 'Notice period', $c['notice_period'] ?? '', ['optional' => true]) ?>
          <?= fi('expected_salary', 'Expected salary', $c['expected_salary'] ?? '', ['optional' => true]) ?>
          <?= fi('portfolio_url', 'Portfolio or GitHub', $c['portfolio_url'] ?? '', ['optional' => true]) ?>
        </div>
      </fieldset>
    </div></div>
    <div class="stack">
      <div class="panel"><div class="panel__body stack">
        <?= fs('source', 'Source', CANDIDATE_SOURCES, $c['source'] ?? 'other') ?>
        <?= fi('tags', 'Tags', $c['tags'] ?? '', ['optional' => true, 'help' => 'Comma separated, e.g. Odoo, Arabic, Senior']) ?>
        <?php if ($isNew): ?>
          <?php $sel = input('job'); ?>
          <?= fs('job_id', 'Add to job', '<option value="">No job yet (talent pool)</option>' . implode('', array_map(static fn ($j) => '<option value="' . (int) $j['id'] . '"' . selected(fval('job_id', $sel), $j['id']) . '>' . e($j['title']) . '</option>', $jobs)), $sel) ?>
          <?= fs('stage_id', 'Starting stage', array_map(static fn ($s) => $s['name'], ats_stages()), (string) first_stage_id(ats_stages(), 'active')) ?>
          <div class="field"><label for="f-resume">CV <span class="opt">(optional)</span></label><input class="input" id="f-resume" type="file" name="resume" accept=".pdf,.doc,.docx,.rtf"><?php $er = form_errors(); if (!empty($er['resume'])): ?><p class="error"><?= e($er['resume']) ?></p><?php endif; ?></div>
          <?= ft('note', 'First note', '', ['optional' => true, 'attrs' => ['rows' => 3], 'placeholder' => 'How you found them, who referred them']) ?>
        <?php endif; ?>
      </div></div>
      <button class="btn btn--primary btn--block" type="submit"><?= $isNew ? 'Add candidate' : 'Save profile' ?></button>
    </div>
  </div>
</form>
