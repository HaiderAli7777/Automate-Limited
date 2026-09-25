<?php
/** @var array $jobs @var array|null $openApp */
$departments = array_values(array_unique(array_filter(array_column($jobs, 'department'))));
$locations = array_values(array_unique(array_filter(array_column($jobs, 'location'))));
$types = array_values(array_unique(array_column($jobs, 'employment_type')));
sort($departments);
sort($locations);
$hero = 'https://images.unsplash.com/photo-1758691737083-0e7fdbde0f05?auto=format&fit=crop&crop=faces,edges&q=72&sat=-12&con=5';
$side = 'https://images.unsplash.com/photo-1580894899378-92e56886cd4d?auto=format&fit=crop&crop=faces,edges&q=72&sat=-12&con=5';

partial('site/head', [
    'title' => 'Careers | Automate Limited',
    'description' => 'Open roles at Automate Limited: Odoo consultants and developers, web developers, designers and marketers in Pakistan and the Gulf.',
    'path' => 'careers/',
    'jsonld' => [[
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => abs_url('')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Careers', 'item' => abs_url('careers/')],
        ],
    ]],
]);
?>
<body data-page="careers">
<?php partial('site/header', ['active' => 'careers']); ?>
<main id="main">

  <section class="phero">
    <div class="wrap phero__grid">
      <div class="rv">
        <h1>Build the systems businesses run on.</h1>
        <p class="phero__sub">We implement Odoo, build websites and run marketing for businesses in Pakistan, the Gulf and beyond.</p>
        <div class="phero__cta">
          <a class="btn btn--primary" href="#roles">See open roles</a>
          <a class="btn btn--quiet" href="#hiring">How hiring works</a>
        </div>
      </div>
      <figure class="phero__media rv" style="--rd:.12s">
        <img src="<?= e($hero) ?>&amp;w=1200&amp;h=960" srcset="<?= e($hero) ?>&amp;w=720&amp;h=576 720w, <?= e($hero) ?>&amp;w=1200&amp;h=960 1200w, <?= e($hero) ?>&amp;w=1800&amp;h=1440 1800w" sizes="(min-width: 900px) 44vw, 92vw" width="1200" height="960" alt="Two colleagues talking through a problem on a laptop in a shared office" loading="eager" fetchpriority="high" decoding="async">
      </figure>
    </div>
  </section>

  <section class="sec band" id="work">
    <div class="wrap work">
      <figure class="work__media rv">
        <img src="<?= e($side) ?>&amp;w=900&amp;h=1125" srcset="<?= e($side) ?>&amp;w=560&amp;h=700 560w, <?= e($side) ?>&amp;w=900&amp;h=1125 900w, <?= e($side) ?>&amp;w=1400&amp;h=1750 1400w" sizes="(min-width: 900px) 38vw, 92vw" width="900" height="1125" alt="An engineer reviewing work with a colleague at a desk" loading="lazy" decoding="async">
      </figure>
      <div>
        <div class="sec-head rv">
          <h2>What the work is like.</h2>
          <p>Small teams, real clients, and systems people depend on every day.</p>
        </div>
        <div class="points">
          <div class="point rv">
            <span class="point__ic"><svg class="ic" aria-hidden="true"><use href="#i-target"/></svg></span>
            <div><h3>Work that gets used</h3><p>What you build runs a shop counter, a warehouse or a month-end close. You'll see who it helps.</p></div>
          </div>
          <div class="point rv">
            <span class="point__ic"><svg class="ic" aria-hidden="true"><use href="#i-erp"/></svg></span>
            <div><h3>Standard first</h3><p>We configure before we customise, and we write down how things work so the next person can pick them up.</p></div>
          </div>
          <div class="point rv">
            <span class="point__ic"><svg class="ic" aria-hidden="true"><use href="#i-hr"/></svg></span>
            <div><h3>Named ownership</h3><p>Every client has a named consultant and every piece of work has an owner. You'll know what's yours.</p></div>
          </div>
          <div class="point rv">
            <span class="point__ic"><svg class="ic" aria-hidden="true"><use href="#i-graduation-cap"/></svg></span>
            <div><h3>Across the stack</h3><p>ERP, websites, search, marketing and design sit under one roof, so there's room to learn the parts next to yours.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sec" id="hiring">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>How hiring works.</h2>
        <p>Four steps. You'll know which one you're at.</p>
      </div>
      <ol class="hsteps rv" style="--rd:.1s">
        <li class="hstep"><h3>Apply</h3><p>Send your CV and answer a few short questions about the role. It takes about ten minutes.</p></li>
        <li class="hstep"><h3>Screening call</h3><p>A short call about your experience, what you're looking for and the practical details.</p></li>
        <li class="hstep"><h3>Interviews</h3><p>Conversations with the people you'd work with. Technical roles include a practical exercise.</p></li>
        <li class="hstep"><h3>Offer</h3><p>A written offer with the salary, start date and terms, and time to ask questions.</p></li>
      </ol>
    </div>
  </section>

  <section class="sec band" id="roles">
    <div class="wrap">
      <div class="openings__head rv">
        <h2>Open roles.</h2>
        <p class="openings__count" id="jobCount" aria-live="polite"><?= $jobs ? plural(count($jobs), 'open role') : '' ?></p>
      </div>

      <?php if ($jobs): ?>
        <?php if (count($jobs) > 3): ?>
        <div class="filters" role="search">
          <label class="search">
            <span class="hp">Search roles</span>
            <svg class="ic" aria-hidden="true"><use href="#i-magnifying-glass"/></svg>
            <input class="inp" type="search" placeholder="Search roles" data-job-filter="q" aria-label="Search roles">
          </label>
          <select class="inp" data-job-filter="department" aria-label="Team">
            <option value="">All teams</option>
            <?php foreach ($departments as $d): ?><option><?= e($d) ?></option><?php endforeach; ?>
          </select>
          <select class="inp" data-job-filter="location" aria-label="Location">
            <option value="">All locations</option>
            <?php foreach ($locations as $l): ?><option><?= e($l) ?></option><?php endforeach; ?>
          </select>
          <select class="inp" data-job-filter="type" aria-label="Job type">
            <option value="">All types</option>
            <?php foreach ($types as $t): ?><option value="<?= e($t) ?>"><?= e(EMPLOYMENT_TYPES[$t] ?? $t) ?></option><?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div class="jobs rv" style="--rd:.1s">
          <?php foreach ($jobs as $j): ?>
            <a class="jrow" href="<?= e(url('careers/' . $j['slug'])) ?>"
               data-q="<?= e(mb_strtolower($j['title'] . ' ' . $j['department'] . ' ' . $j['location'] . ' ' . $j['summary'])) ?>"
               data-department="<?= e((string) $j['department']) ?>" data-location="<?= e((string) $j['location']) ?>" data-type="<?= e($j['employment_type']) ?>">
              <div>
                <div class="jrow__title"><?= e($j['title']) ?></div>
                <?php if ($j['summary']): ?><p class="jrow__sum"><?= e(str_limit((string) $j['summary'], 160)) ?></p><?php endif; ?>
              </div>
              <div class="jrow__tags">
                <?php if ($j['department']): ?><span class="tag"><?= e($j['department']) ?></span><?php endif; ?>
                <?php if ($j['location']): ?><span class="tag"><svg class="ic" aria-hidden="true"><use href="#i-map-pin"/></svg><?= e($j['location']) ?></span><?php endif; ?>
                <span class="tag"><?= e(EMPLOYMENT_TYPES[$j['employment_type']] ?? '') ?> · <?= e(WORKPLACES[$j['workplace']] ?? '') ?></span>
              </div>
              <span class="role__go" aria-hidden="true"><svg class="ic"><use href="#i-arrow-right"/></svg></span>
            </a>
          <?php endforeach; ?>
          <div class="empty" id="jobEmpty" hidden>
            <h3>No roles match those filters.</h3>
            <p>Try a different team or location, or clear the search.</p>
          </div>
        </div>
      <?php else: ?>
        <div class="jobs">
          <div class="empty">
            <svg class="ic" aria-hidden="true"><use href="#i-briefcase"/></svg>
            <h3>No open roles right now.</h3>
            <p><?= $openApp ? 'We still want to hear from good people. Send an open application and we\'ll look there first when a role opens.' : 'New roles are posted here first. Check back soon, or email your CV to info@automateltd.com.' ?></p>
            <?php if ($openApp): ?><a class="btn btn--primary" href="<?= e(url('careers/open-application')) ?>">Send an open application</a><?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($jobs && $openApp): ?>
        <div class="openapp rv">
          <div>
            <h3>Don't see your role?</h3>
            <p>Send an open application. When a role opens that fits, we look there first.</p>
          </div>
          <a class="btn btn--quiet" href="<?= e(url('careers/open-application')) ?>">Send an open application</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php partial('site/footer'); ?>
</body>
</html>
