<?php
/* How we work: the process, what changes day to day, and the principles behind it. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

partial('site/head', [
    'title' => 'How we work | Automate Limited',
    'description' => 'Two weeks of scoping, standard Odoo first, both systems running until the numbers agree, and a named consultant after go-live.',
    'path' => 'how-we-work/',
    'jsonld' => [breadcrumb_jsonld(['How we work' => 'how-we-work/'])],
]);
$principles = [
    ['target', 'Standard first', 'Customisation is only what\'s left once the default has been proven not to fit. That keeps upgrades cheap and your system easy to hand over.'],
    ['calendar-check', 'Dates in writing', 'The go-live date goes into the scope you sign, before anything is configured. If the scope changes, the date and the quote are revisited together.'],
    ['hr', 'A named consultant', 'One person who knows your setup answers your questions. Not a ticket queue, and not a different face every week.'],
    ['file-text', 'Everything written down', 'Processes, decisions and custom code are documented so your team, or another partner, could pick them up tomorrow.'],
];
?>
<body data-page="how">
<?php partial('site/header', ['active' => 'how']); ?>
<main id="main">
  <section class="phero phero--plain">
    <div class="wrap">
      <div class="phero__head rv">
        <p class="eyebrow">How we work</p>
        <h1>A process you can plan around.</h1>
        <p class="phero__sub">Scoping comes before configuration, your old system keeps running until the numbers agree, and support comes from someone who knows your setup.</p>
        <div class="phero__cta">
          <a class="btn btn--primary" href="<?= e(url('contact/')) ?>">Book a scoping call</a>
          <a class="btn btn--quiet" href="<?= e(url('pricing/')) ?>">See pricing</a>
        </div>
      </div>
    </div>
  </section>

  <?php partial('site/sec-process', ['class' => 'sec--flush-top']); ?>

  <section class="sec band">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>What we hold ourselves to.</h2>
        <p>The same four rules on every project, whether it's an Odoo rollout or a new website.</p>
      </div>
      <div class="principles">
        <?php foreach ($principles as $i => [$ic, $t, $b]): ?>
          <article class="principle rv" style="--rd:<?= number_format(($i % 2) * 0.06, 2) ?>s">
            <span class="point__ic"><svg class="ic" aria-hidden="true"><use href="#i-<?= e($ic) ?>"/></svg></span>
            <h3><?= e($t) ?></h3>
            <p><?= e($b) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php partial('site/sec-monday'); ?>

  <section class="sec band">
    <div class="wrap faq-grid">
      <div class="sec-head rv">
        <h2>Questions about the process.</h2>
        <p class="faq-more"><a href="<?= e(url('faq/')) ?>">All questions</a></p>
      </div>
      <?php partial('site/faq-list', ['items' => faq_for('odoo', 4), 'openFirst' => true]); ?>
    </div>
  </section>

  <?php partial('site/cta-band'); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
