<?php
/** @var array $job @var array $questions @var array $errors @var array $values */
$salary = job_salary($job);
$type = EMPLOYMENT_TYPES[$job['employment_type']] ?? '';
$workplace = WORKPLACES[$job['workplace']] ?? '';
$level = EXPERIENCE_LEVELS[(string) $job['experience_level']] ?? '';
$isGeneral = $job['slug'] === 'open-application';

$v = static fn (string $k): string => e((string) ($values[$k] ?? ''));
$answer = static fn (int $qid): string => (string) (($values['q'] ?? [])[$qid] ?? '');
$has = static fn (string $k): bool => isset($errors[$k]);
$cls = static fn (string $k): string => isset($errors[$k]) ? 'fld has-error' : 'fld';
$err = static fn (string $k): string => isset($errors[$k]) ? '<p class="fld__err" id="err-' . e($k) . '">' . e($errors[$k]) . '</p>' : '';
$aria = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . e($k) . '"' : '';

/* Google for Jobs structured data */
$countryCodes = ['pakistan' => 'PK', 'united arab emirates' => 'AE', 'uae' => 'AE', 'saudi arabia' => 'SA', 'ksa' => 'SA', 'qatar' => 'QA', 'oman' => 'OM', 'bahrain' => 'BH', 'kuwait' => 'KW'];
$locParts = array_map('trim', explode(',', (string) $job['location']));
$country = $countryCodes[strtolower((string) end($locParts))] ?? 'PK';
$descHtml = markdown($job['description']) . ($job['requirements'] ? '<h3>Requirements</h3>' . markdown($job['requirements']) : '') . ($job['benefits'] ? '<h3>Benefits</h3>' . markdown($job['benefits']) : '');
$posting = [
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => $job['title'],
    'description' => $descHtml !== '' ? $descHtml : e((string) $job['summary']),
    'datePosted' => date('Y-m-d', ts($job['published_at'] ?: $job['created_at']) ?? time()),
    'employmentType' => ['full_time' => 'FULL_TIME', 'part_time' => 'PART_TIME', 'contract' => 'CONTRACTOR', 'internship' => 'INTERN', 'temporary' => 'TEMPORARY'][$job['employment_type']] ?? 'OTHER',
    'directApply' => true,
    'hiringOrganization' => ['@type' => 'Organization', 'name' => 'Automate Limited', 'sameAs' => abs_url(''), 'logo' => abs_url('logo-automate.svg')],
    'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $locParts[0] ?? '', 'addressCountry' => $country]],
];
if ($job['closes_at']) {
    $posting['validThrough'] = $job['closes_at'] . 'T23:59:59';
}
if ($job['workplace'] === 'remote') {
    $posting['jobLocationType'] = 'TELECOMMUTE';
    $posting['applicantLocationRequirements'] = ['@type' => 'Country', 'name' => $country];
}
if ($salary !== '') {
    $posting['baseSalary'] = ['@type' => 'MonetaryAmount', 'currency' => $job['salary_currency'] ?: 'PKR', 'value' => array_filter([
        '@type' => 'QuantitativeValue',
        'minValue' => $job['salary_min'] !== null ? (float) $job['salary_min'] : null,
        'maxValue' => $job['salary_max'] !== null ? (float) $job['salary_max'] : null,
        'unitText' => ($job['salary_period'] ?? 'month') === 'year' ? 'YEAR' : 'MONTH',
    ], static fn ($x) => $x !== null)];
}

partial('site/head', [
    'title' => $job['title'] . ' | Careers at Automate Limited',
    'description' => str_limit($job['summary'] ?: markdown_plain($job['description']), 158),
    'path' => 'careers/' . $job['slug'],
    'noindex' => $isGeneral,
    'jsonld' => $isGeneral ? [] : [$posting],
]);
?>
<body data-page="job">
<?php partial('site/header', ['active' => 'careers']); ?>
<main id="main">

  <header class="jhead">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('careers/')) ?>">Careers</a>
        <svg class="ic" aria-hidden="true"><use href="#i-caret-right"/></svg>
        <span aria-current="page"><?= e($job['title']) ?></span>
      </nav>
      <h1><?= e($job['title']) ?></h1>
      <?php if ($job['summary']): ?><p class="jhead__sum"><?= e($job['summary']) ?></p><?php endif; ?>
      <div class="jhead__row">
        <div class="chips">
          <?php if ($job['department']): ?><span class="tag"><svg class="ic" aria-hidden="true"><use href="#i-users-three"/></svg><?= e($job['department']) ?></span><?php endif; ?>
          <?php if ($job['location']): ?><span class="tag"><svg class="ic" aria-hidden="true"><use href="#i-map-pin"/></svg><?= e($job['location']) ?></span><?php endif; ?>
          <span class="tag"><svg class="ic" aria-hidden="true"><use href="#i-briefcase"/></svg><?= e($type) ?></span>
          <span class="tag"><svg class="ic" aria-hidden="true"><use href="#i-buildings"/></svg><?= e($workplace) ?></span>
        </div>
        <a class="btn btn--primary" href="#apply">Apply for this role</a>
      </div>
    </div>
  </header>

  <div class="wrap jbody">
    <article class="prose">
      <?php if ($job['description']): ?><section><h2>About the role</h2><?= markdown($job['description']) ?></section><?php endif; ?>
      <?php if ($job['requirements']): ?><section><h2>What you'll need</h2><?= markdown($job['requirements']) ?></section><?php endif; ?>
      <?php if ($job['benefits']): ?><section><h2>What we offer</h2><?= markdown($job['benefits']) ?></section><?php endif; ?>
    </article>
    <aside class="jside" aria-label="Role summary">
      <p class="jside__t">At a glance</p>
      <dl>
        <?php if ($job['department']): ?><div><dt>Team</dt><dd><?= e($job['department']) ?></dd></div><?php endif; ?>
        <?php if ($job['location']): ?><div><dt>Location</dt><dd><?= e($job['location']) ?> (<?= e($workplace) ?>)</dd></div><?php endif; ?>
        <div><dt>Type</dt><dd><?= e($type) ?></dd></div>
        <?php if ($level && $job['experience_level']): ?><div><dt>Level</dt><dd><?= e($level) ?></dd></div><?php endif; ?>
        <?php if ($salary): ?><div><dt>Salary</dt><dd><?= e($salary) ?></dd></div><?php endif; ?>
        <?php if ($job['closes_at']): ?><div><dt>Apply by</dt><dd><?= e(fmt_date($job['closes_at'], 'j F Y')) ?></dd></div><?php endif; ?>
      </dl>
      <a class="btn btn--primary" href="#apply">Apply now</a>
      <button class="jside__share" type="button" data-copy="<?= e(abs_url('careers/' . $job['slug'])) ?>"><svg class="ic" aria-hidden="true"><use href="#i-copy"/></svg><span>Copy link to this role</span></button>
    </aside>
  </div>

  <section class="apply" id="apply">
    <div class="wrap">
      <div class="apply__card">
        <h2><?= $isGeneral ? 'Send an open application.' : 'Apply for this role.' ?></h2>
        <p>Fields marked optional can be left blank. Everything else helps us review your application properly.</p>

        <form method="post" action="<?= e(url('careers/' . $job['slug'])) ?>#apply" enctype="multipart/form-data" data-apply novalidate>
          <?php if ($errors): ?>
            <div class="errsum" role="alert" tabindex="-1" id="errsum">
              <strong><?= isset($errors['form']) ? e($errors['form']) : 'Please fix the following and send it again.' ?></strong>
              <?php $list = array_diff_key($errors, ['form' => 1]); if ($list): ?>
                <ul><?php foreach ($list as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul>
              <?php endif; ?>
              <?php if (!$has('resume')): ?><p style="margin-top:6px">For your security, please attach your CV again.</p><?php endif; ?>
            </div>
          <?php endif; ?>
          <input type="hidden" name="_t" value="<?= e(form_token()) ?>">
          <input type="hidden" name="MAX_FILE_SIZE" value="<?= upload_max_bytes() ?>">
          <div class="hp" aria-hidden="true"><label for="ap-website">Leave this empty</label><input id="ap-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>

          <fieldset class="fset">
            <legend>About you</legend>
            <div class="form__row">
              <div class="<?= $cls('first_name') ?>"><label for="ap-first">First name</label><input class="inp" id="ap-first" name="first_name" autocomplete="given-name" required maxlength="80" value="<?= $v('first_name') ?>"<?= $aria('first_name') ?>><?= $err('first_name') ?></div>
              <div class="<?= $cls('last_name') ?>"><label for="ap-last">Last name</label><input class="inp" id="ap-last" name="last_name" autocomplete="family-name" required maxlength="80" value="<?= $v('last_name') ?>"<?= $aria('last_name') ?>><?= $err('last_name') ?></div>
            </div>
            <div class="form__row">
              <div class="<?= $cls('email') ?>"><label for="ap-email">Email</label><input class="inp" id="ap-email" name="email" type="email" autocomplete="email" required maxlength="190" value="<?= $v('email') ?>"<?= $aria('email') ?>><?= $err('email') ?></div>
              <div class="<?= $cls('phone') ?>"><label for="ap-phone">Phone</label><input class="inp" id="ap-phone" name="phone" type="tel" autocomplete="tel" required maxlength="40" value="<?= $v('phone') ?>"<?= $aria('phone') ?>><?= $err('phone') ?></div>
            </div>
            <div class="<?= $cls('location') ?>"><label for="ap-location">Where are you based? <span class="opt">(optional)</span></label><input class="inp" id="ap-location" name="location" autocomplete="address-level2" maxlength="120" placeholder="City, country" value="<?= $v('location') ?>"><?= $err('location') ?></div>
          </fieldset>

          <fieldset class="fset">
            <legend>Your experience</legend>
            <div class="form__row">
              <div class="<?= $cls('current_title') ?>"><label for="ap-title">Current job title <span class="opt">(optional)</span></label><input class="inp" id="ap-title" name="current_title" autocomplete="organization-title" maxlength="120" value="<?= $v('current_title') ?>"><?= $err('current_title') ?></div>
              <div class="<?= $cls('current_company') ?>"><label for="ap-company">Current employer <span class="opt">(optional)</span></label><input class="inp" id="ap-company" name="current_company" autocomplete="organization" maxlength="120" value="<?= $v('current_company') ?>"><?= $err('current_company') ?></div>
            </div>
            <div class="form__row">
              <div class="<?= $cls('experience_years') ?>"><label for="ap-years">Years of relevant experience <span class="opt">(optional)</span></label><input class="inp" id="ap-years" name="experience_years" inputmode="decimal" maxlength="4" value="<?= $v('experience_years') ?>"<?= $aria('experience_years') ?>><?= $err('experience_years') ?></div>
              <div class="<?= $cls('notice_period') ?>"><label for="ap-notice">Notice period <span class="opt">(optional)</span></label><input class="inp" id="ap-notice" name="notice_period" maxlength="80" placeholder="For example, one month" value="<?= $v('notice_period') ?>"><?= $err('notice_period') ?></div>
            </div>
            <div class="form__row">
              <div class="<?= $cls('expected_salary') ?>"><label for="ap-salary">Expected salary <span class="opt">(optional)</span></label><input class="inp" id="ap-salary" name="expected_salary" maxlength="80" placeholder="Amount, currency and per month or year" value="<?= $v('expected_salary') ?>"><?= $err('expected_salary') ?></div>
              <div class="<?= $cls('linkedin_url') ?>"><label for="ap-linkedin">LinkedIn profile <span class="opt">(optional)</span></label><input class="inp" id="ap-linkedin" name="linkedin_url" type="url" inputmode="url" maxlength="255" value="<?= $v('linkedin_url') ?>"<?= $aria('linkedin_url') ?>><?= $err('linkedin_url') ?></div>
            </div>
            <div class="<?= $cls('portfolio_url') ?>"><label for="ap-portfolio">Portfolio, GitHub or website <span class="opt">(optional)</span></label><input class="inp" id="ap-portfolio" name="portfolio_url" type="url" inputmode="url" maxlength="255" value="<?= $v('portfolio_url') ?>"<?= $aria('portfolio_url') ?>><?= $err('portfolio_url') ?></div>
          </fieldset>

          <fieldset class="fset">
            <legend>CV and cover letter</legend>
            <div class="<?= $cls('resume') ?>">
              <span class="lbl" id="cv-label">Your CV</span>
              <label class="drop" data-drop>
                <input type="file" name="resume" accept=".pdf,.doc,.docx,.rtf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required aria-labelledby="cv-label cv-hint"<?= $aria('resume') ?>>
                <span class="drop__ic"><svg class="ic" aria-hidden="true"><use href="#i-upload-simple"/></svg></span>
                <span><span class="drop__t" data-drop-name>Choose a file or drop it here</span><br><span class="drop__s" id="cv-hint">PDF or Word, up to <?= (int) (upload_max_bytes() / 1048576) ?> MB</span></span>
              </label>
              <?= $err('resume') ?>
            </div>
            <div class="<?= $cls('cover_letter') ?>"><label for="ap-cover">Why this role? <span class="opt">(optional)</span></label><textarea class="inp" id="ap-cover" name="cover_letter" rows="6" maxlength="8000" placeholder="A few lines is plenty"><?= $v('cover_letter') ?></textarea><?= $err('cover_letter') ?></div>
          </fieldset>

          <?php if ($questions): ?>
          <fieldset class="fset">
            <legend>A few questions</legend>
            <?php foreach ($questions as $q):
                $qid = (int) $q['id'];
                $name = 'q[' . $qid . ']';
                $id = 'q-' . $qid;
                $req = (int) $q['required'];
                $label = e($q['question']) . ($req ? '' : ' <span class="opt">(optional)</span>');
                $cur = $answer($qid);
            ?>
              <div class="<?= $cls('q' . $qid) ?>">
                <?php if ($q['qtype'] === 'yesno'): ?>
                  <span class="lbl" id="<?= $id ?>-l"><?= $label ?></span>
                  <div class="choices" role="radiogroup" aria-labelledby="<?= $id ?>-l">
                    <?php foreach (['Yes', 'No'] as $opt): ?>
                      <label><input type="radio" name="<?= e($name) ?>" value="<?= $opt ?>"<?= checked($cur === $opt) ?><?= $req ? ' required' : '' ?>><?= $opt ?></label>
                    <?php endforeach; ?>
                  </div>
                <?php elseif ($q['qtype'] === 'select'): ?>
                  <label for="<?= $id ?>"><?= $label ?></label>
                  <select class="inp" id="<?= $id ?>" name="<?= e($name) ?>"<?= $req ? ' required' : '' ?>>
                    <option value="">Choose one</option>
                    <?php foreach (array_filter(array_map('trim', explode("\n", (string) $q['options']))) as $opt): ?>
                      <option<?= selected($cur, $opt) ?>><?= e($opt) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php elseif ($q['qtype'] === 'textarea'): ?>
                  <label for="<?= $id ?>"><?= $label ?></label>
                  <textarea class="inp" id="<?= $id ?>" name="<?= e($name) ?>" rows="4" maxlength="4000"<?= $req ? ' required' : '' ?>><?= e($cur) ?></textarea>
                <?php else: ?>
                  <label for="<?= $id ?>"><?= $label ?></label>
                  <input class="inp" id="<?= $id ?>" name="<?= e($name) ?>" <?= $q['qtype'] === 'number' ? 'inputmode="decimal"' : 'type="text"' ?> maxlength="4000" value="<?= e($cur) ?>"<?= $req ? ' required' : '' ?>>
                <?php endif; ?>
                <?= $err('q' . $qid) ?>
              </div>
            <?php endforeach; ?>
          </fieldset>
          <?php endif; ?>

          <fieldset class="fset">
            <legend>Before you send</legend>
            <div class="<?= $cls('consent') ?>">
              <label class="check"><input type="checkbox" name="consent" value="1"<?= checked(($values['consent'] ?? '') === '1' || input('consent') === '1') ?> required>
                <span>I agree that Automate Limited can store my details and CV to review this application and contact me about it. See the <a href="<?= e(url('privacy/')) ?>">privacy notice</a>.</span></label>
              <?= $err('consent') ?>
            </div>
            <div class="form__foot">
              <button class="btn btn--primary" type="submit" data-busy="Sending application">Send application</button>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
  </section>

</main>
<?php partial('site/footer'); ?>
</body>
</html>
