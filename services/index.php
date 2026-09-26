<?php
/*
 * /services/ lists the six services; /services/{slug} is one service.
 * .htaccess rewrites /services/{slug} to this file with ?s={slug}.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$slug = trim((string) ($_GET['s'] ?? ''), '/');
$services = site_services();

/* ------------------------------------------------------------ overview */
if ($slug === '') {
    partial('site/head', [
        'title' => 'Services | Automate Limited',
        'description' => 'Odoo ERP, website development, SEO, digital marketing, graphic design and custom solutions from one team.',
        'path' => 'services/',
        'jsonld' => [breadcrumb_jsonld(['Services' => 'services/'])],
    ]);
    ?>
<body data-page="services">
<?php partial('site/header', ['active' => 'services']); ?>
<main id="main">
  <section class="phero phero--plain">
    <div class="wrap">
      <div class="phero__head rv">
        <p class="eyebrow">Services</p>
        <h1>One team for the system and everything around it.</h1>
        <p class="phero__sub">Most clients start with Odoo. The website, search rankings and brand usually come next, so we do those too.</p>
      </div>
    </div>
  </section>

  <section class="sec sec--flush-top">
    <div class="wrap">
      <ul class="svc-grid">
        <?php $i = 0; foreach ($services as $s_slug => $s): ?>
          <li class="rv" style="--rd:<?= number_format(($i++ % 3) * 0.06, 2) ?>s">
            <a class="svc-card<?= $s['key'] === 'odoo' ? ' svc-card--lead' : '' ?>" href="<?= e(url('services/' . $s_slug)) ?>">
              <span class="svc-card__ic"><svg class="ic" aria-hidden="true"><use href="#i-<?= e($s['icon']) ?>"/></svg></span>
              <h2><?= e($s['name']) ?></h2>
              <p class="svc-card__line"><?= e($s['lede']) ?></p>
              <ul class="svc-card__list">
                <?php foreach (array_slice($s['spec'], 0, 3) as $pt): ?><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span><?= e($pt) ?></span></li><?php endforeach; ?>
              </ul>
              <span class="svc-card__go">Learn more<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section class="sec band">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Odoo, module by module.</h2>
        <p>Tap a module to ask about it. Your enquiry reaches our team with the module already noted.</p>
      </div>
      <div class="rv" style="--rd:.1s"><?php partial('site/mods', ['items' => array_values(array_filter(site_modules(), static fn ($m) => $m['home'] !== false))]); ?></div>
      <p class="more-link rv"><a href="<?= e(url('odoo-modules/')) ?>">See all <?= count(site_modules()) ?> Odoo modules<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p>
    </div>
  </section>

  <?php partial('site/cta-band', ['title' => 'Not sure where to start?', 'text' => 'Tell us what\'s slowing the business down. We\'ll suggest the smallest piece of work that fixes it.']); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
<?php
    exit;
}

/* ------------------------------------------------------------ one service */
$s = preg_match('/^[a-z0-9-]{1,60}$/', $slug) ? site_service($slug) : null;
if (!$s) {
    http_response_code(404);
    require APP_ROOT . '/404.php';
    exit;
}
$faqs = faq_for($s['key'], 4);
$related = array_values(array_filter(array_map(static fn ($k) => service_slug($k), $s['related'])));
partial('site/head', [
    'title' => $s['title'] . ' | Automate Limited',
    'description' => $s['meta'],
    'path' => 'services/' . $slug,
    'jsonld' => array_filter([
        breadcrumb_jsonld(['Services' => 'services/', $s['name'] => 'services/' . $slug]),
        ['@context' => 'https://schema.org', '@type' => 'Service', 'name' => $s['name'], 'description' => $s['meta'], 'serviceType' => $s['name'],
            'provider' => ['@type' => 'Organization', 'name' => 'Automate Limited', 'url' => abs_url('')],
            'areaServed' => ['Pakistan', 'United Arab Emirates', 'Saudi Arabia']],
        $faqs ? faq_jsonld($faqs) : null,
    ]),
]);
?>
<body data-page="service">
<?php partial('site/header', ['active' => 'services']); ?>
<main id="main">
  <section class="phero">
    <div class="wrap phero__grid">
      <div class="rv">
        <nav class="crumbs" aria-label="Breadcrumb">
          <a href="<?= e(url('services/')) ?>">Services</a>
          <svg class="ic" aria-hidden="true"><use href="#i-caret-right"/></svg>
          <span aria-current="page"><?= e($s['name']) ?></span>
        </nav>
        <h1><?= e($s['name']) ?></h1>
        <p class="phero__sub"><?= e($s['lede']) ?></p>
        <div class="phero__cta">
          <a class="btn btn--primary" href="<?= e(enquiry_url($s['name'])) ?>">Enquire about <?= e($s['name']) ?></a>
          <a class="btn btn--quiet" href="<?= e(url('how-we-work/')) ?>">How we work</a>
        </div>
      </div>
      <div class="phero__art <?= e($s['art_class']) ?> is-active rv" style="--rd:.12s"><?= $s['art'] ?></div>
    </div>
  </section>

  <section class="sec band">
    <div class="wrap svc-split">
      <div class="sec-head rv">
        <p class="eyebrow"><?= e($s['kicker']) ?></p>
        <h2><?= e($s['headline']) ?></h2>
        <p><?= e($s['desc']) ?></p>
      </div>
      <div class="included rv" style="--rd:.1s">
        <p class="included__h">What's included</p>
        <ul class="spec">
          <?php foreach (array_merge($s['spec'], $s['included']) as $pt): ?><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span><?= e($pt) ?></span></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

  <section class="sec">
    <div class="wrap">
      <div class="sec-head rv"><h2>What changes for you.</h2></div>
      <div class="outcomes">
        <?php foreach ($s['outcomes'] as $i => [$t, $b]): ?>
          <article class="outcome rv" style="--rd:<?= number_format($i * 0.06, 2) ?>s"><span class="outcome__n"><?= sprintf('%02d', $i + 1) ?></span><h3><?= e($t) ?></h3><p><?= e($b) ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ($s['key'] === 'odoo'): ?>
  <section class="sec band" id="modules">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Odoo, module by module.</h2>
        <p>Each module reads from the same records as the others. Tap one to ask about it.</p>
      </div>
      <div class="rv" style="--rd:.1s"><?php partial('site/mods', ['items' => array_values(array_filter(site_modules(), static fn ($m) => $m['home'] !== false))]); ?></div>
      <p class="more-link rv"><a href="<?= e(url('odoo-modules/')) ?>">See all <?= count(site_modules()) ?> modules<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p>
    </div>
  </section>
  <?php endif; ?>

  <section class="sec<?= $s['key'] === 'odoo' ? '' : ' band' ?>">
    <div class="wrap">
      <div class="sec-head rv"><h2>How <?= e($s['project']) ?> runs.</h2></div>
      <ol class="hsteps rv" style="--rd:.1s">
        <?php foreach ($s['steps'] as [$t, $b]): ?><li class="hstep"><h3><?= e($t) ?></h3><p><?= e($b) ?></p></li><?php endforeach; ?>
      </ol>
    </div>
  </section>

  <?php if ($faqs): ?>
  <section class="sec<?= $s['key'] === 'odoo' ? ' band' : '' ?>">
    <div class="wrap faq-grid">
      <div class="sec-head rv">
        <h2>Common questions.</h2>
        <p class="faq-more"><a href="<?= e(url('faq/')) ?>">All questions</a> or email <a href="mailto:info@automateltd.com">info@automateltd.com</a>.</p>
      </div>
      <?php partial('site/faq-list', ['items' => $faqs, 'openFirst' => true]); ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="sec<?= $s['key'] === 'odoo' ? '' : ' band' ?>">
    <div class="wrap">
      <div class="sec-head rv"><h2>Often paired with.</h2></div>
      <ul class="svc-grid svc-grid--sm">
        <?php foreach ($related as $i => $r_slug): $r = $services[$r_slug]; ?>
          <li class="rv" style="--rd:<?= number_format($i * 0.06, 2) ?>s"><a class="svc-card" href="<?= e(url('services/' . $r_slug)) ?>">
            <span class="svc-card__ic"><svg class="ic" aria-hidden="true"><use href="#i-<?= e($r['icon']) ?>"/></svg></span>
            <h3><?= e($r['name']) ?></h3><p class="svc-card__line"><?= e($r['line']) ?></p>
            <span class="svc-card__go">Explore<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
          </a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <?php partial('site/cta-band', ['title' => 'Talk to us about ' . $s['noun'] . '.', 'service' => $s['name']]); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
