<?php
/** @var array|null $job @var array $questions @var array $departments @var array $locations */
$isNew = !$job;
$j = $job ?? ['status' => 'draft', 'employment_type' => 'full_time', 'workplace' => 'onsite', 'openings' => 1, 'salary_currency' => setting('default_currency', 'PKR'), 'salary_period' => 'month', 'listed' => 1, 'salary_visible' => 0];
$qs = old_array('q_text') !== null ? array_map(static fn ($i) => [
    'id' => old_array('q_id')[$i] ?? 0, 'question' => old_array('q_text')[$i], 'qtype' => old_array('q_type')[$i] ?? 'text',
    'options' => old_array('q_options')[$i] ?? '', 'required' => (old_array('q_required')[$i] ?? '') === '1',
], array_keys(old_array('q_text'))) : $questions;
$qRow = static function (array $q): string {
    ob_start(); ?>
    <div class="qrow" data-question-row>
      <input type="hidden" name="q_id[]" value="<?= (int) ($q['id'] ?? 0) ?>">
      <div class="stack-sm">
        <input class="input" name="q_text[]" value="<?= e((string) ($q['question'] ?? '')) ?>" placeholder="e.g. Have you worked with Odoo 17 or later?" aria-label="Question" maxlength="255">
        <div data-q-options><textarea class="textarea" name="q_options[]" placeholder="One option per line" aria-label="Options"><?= e((string) ($q['options'] ?? '')) ?></textarea></div>
      </div>
      <select class="select" name="q_type[]" aria-label="Answer type"><?= options(QUESTION_TYPES, $q['qtype'] ?? 'text') ?></select>
      <select class="select" name="q_required[]" aria-label="Required"><option value="1"<?= !empty($q['required']) ? ' selected' : '' ?>>Required</option><option value="0"<?= empty($q['required']) ? ' selected' : '' ?>>Optional</option></select>
      <button class="btn btn--ghost btn--icon" type="button" data-remove-question aria-label="Remove question"><?= icon('trash') ?></button>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<a class="crumb" href="<?= e($isNew ? admin_url('jobs') : admin_url('jobs/' . $job['id'])) ?>"><?= icon('arrow-left') ?><?= $isNew ? 'Jobs' : e($job['title']) ?></a>
<div class="phead"><div><h1><?= $isNew ? 'New job' : 'Edit job' ?></h1><p class="phead__sub">Descriptions support simple formatting: start a line with "- " for a bullet, "## " for a heading, and wrap words in **double stars** for bold.</p></div></div>

<form method="post" action="<?= e($isNew ? admin_url('jobs/new') : admin_url('jobs/' . $job['id'] . '/edit')) ?>">
  <?= csrf_field() ?>
  <div class="split split--wide-side">
    <div class="stack">
      <div class="panel"><div class="panel__body">
        <fieldset class="fieldset">
          <legend>The role</legend>
          <?= fi('title', 'Job title', $j['title'] ?? '', ['required' => true, 'placeholder' => 'Odoo functional consultant']) ?>
          <?= ft('summary', 'Summary', $j['summary'] ?? '', ['help' => 'One or two sentences. Shown in the careers list and in search results.', 'attrs' => ['maxlength' => 400, 'rows' => 3]]) ?>
          <?= ft('description', 'About the role', $j['description'] ?? '', ['size' => 'tall', 'help' => 'What they will do day to day, and who they will work with.']) ?>
          <?= ft('requirements', 'What they will need', $j['requirements'] ?? '', ['optional' => true, 'help' => 'Skills and experience, one bullet per line.']) ?>
          <?= ft('benefits', 'What you offer', $j['benefits'] ?? '', ['optional' => true, 'help' => 'Salary details, leave, equipment, learning budget. Only what is true.']) ?>
        </fieldset>
      </div></div>

      <div class="panel" data-questions>
        <div class="panel__head"><div><h2>Screening questions</h2><p class="muted small">Asked on the application form. Answers show on each application.</p></div><button class="btn btn--quiet btn--sm" type="button" data-add-question><?= icon('plus') ?>Add question</button></div>
        <div class="panel__body">
          <div data-question-list><?php foreach ($qs as $q) { echo $qRow($q); } ?></div>
          <?php if (!$qs): ?><p class="small muted">No questions yet. Good ones are quick to answer and rule people in or out, such as notice period or right to work.</p><?php endif; ?>
          <template><?= $qRow([]) ?></template>
        </div>
      </div>
    </div>

    <div class="stack">
      <div class="panel"><div class="panel__body">
        <fieldset class="fieldset">
          <legend>Publishing</legend>
          <?= fs('status', 'Status', JOB_STATUSES, $j['status'], ['help' => 'Open jobs appear on the careers page.']) ?>
          <?= fc('listed', 'Show in the careers list', (bool) $j['listed'], 'Untick for a role you only share by link.') ?>
          <?= fi('closes_at', 'Stop taking applications on', $j['closes_at'] ?? '', ['type' => 'date', 'optional' => true]) ?>
          <?= fs('hiring_manager_id', 'Hiring manager', user_options(isset($j['hiring_manager_id']) ? (int) $j['hiring_manager_id'] : null, 'None'), $j['hiring_manager_id'] ?? '', ['help' => 'Also receives new application emails.']) ?>
        </fieldset>
      </div></div>
      <div class="panel"><div class="panel__body">
        <fieldset class="fieldset">
          <legend>Details</legend>
          <?= fi('department', 'Team', $j['department'] ?? '', ['attrs' => ['list' => 'dl-departments'], 'placeholder' => 'Odoo, Web, Marketing']) ?>
          <datalist id="dl-departments"><?php foreach ($departments as $d): ?><option value="<?= e($d) ?>"><?php endforeach; ?></datalist>
          <?= fi('location', 'Location', $j['location'] ?? '', ['attrs' => ['list' => 'dl-locations'], 'placeholder' => 'Lahore, Pakistan', 'help' => 'City, country.']) ?>
          <datalist id="dl-locations"><?php foreach ($locations as $l): ?><option value="<?= e($l) ?>"><?php endforeach; ?></datalist>
          <div class="grid-2 grid-tight">
            <?= fs('employment_type', 'Type', EMPLOYMENT_TYPES, $j['employment_type']) ?>
            <?= fs('workplace', 'Workplace', WORKPLACES, $j['workplace']) ?>
            <?= fs('experience_level', 'Level', EXPERIENCE_LEVELS, $j['experience_level'] ?? '') ?>
            <?= fi('openings', 'Openings', $j['openings'], ['type' => 'number', 'attrs' => ['min' => 1, 'max' => 99]]) ?>
          </div>
        </fieldset>
      </div></div>
      <div class="panel"><div class="panel__body">
        <fieldset class="fieldset">
          <legend>Salary</legend>
          <div class="grid-2 grid-tight">
            <?= fi('salary_min', 'From', isset($j['salary_min']) && $j['salary_min'] !== null ? (string) (float) $j['salary_min'] : '', ['attrs' => ['inputmode' => 'decimal']]) ?>
            <?= fi('salary_max', 'To', isset($j['salary_max']) && $j['salary_max'] !== null ? (string) (float) $j['salary_max'] : '', ['attrs' => ['inputmode' => 'decimal']]) ?>
            <?= fs('salary_currency', 'Currency', array_combine(currencies(), currencies()), $j['salary_currency'] ?? 'PKR') ?>
            <?= fs('salary_period', 'Per', ['month' => 'Month', 'year' => 'Year'], $j['salary_period'] ?? 'month') ?>
          </div>
          <?= fc('salary_visible', 'Show the salary on the careers page', (bool) $j['salary_visible']) ?>
        </fieldset>
      </div></div>
      <button class="btn btn--primary btn--block" type="submit"><?= $isNew ? 'Create job' : 'Save job' ?></button>
    </div>
  </div>
</form>
