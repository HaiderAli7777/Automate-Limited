<?php
/**
 * Site header, services menu and mobile menu.
 * @var bool|null $home   true on the homepage (the logo scrolls to the top)
 * @var string|null $active  current section: services, modules, how, pricing, careers, faq, contact
 */
$home = !empty($home);
$active = $active ?? '';
$cur = static fn (string $key): string => $active === $key ? ' aria-current="page"' : '';
partial('site/sprite');
?>
<span class="sentinel" id="topSentinel" aria-hidden="true"></span>
<a class="skip" href="#main">Skip to content</a>

<header class="hdr" id="hdr">
  <div class="wrap hdr__in">
    <a class="hdr__logo" href="<?= $home ? '#top' : e(url('')) ?>" aria-label="Automate Limited, <?= $home ? 'back to top' : 'home' ?>">
      <svg viewBox="0 0 3309 753" aria-hidden="true"><use href="#lk"/></svg>
    </a>
    <nav class="hdr__nav" aria-label="Primary">
      <div class="dd" data-dd>
        <a class="dd__link" href="<?= e(url('services/')) ?>"<?= $cur('services') ?>>Services</a>
        <button class="dd__toggle" type="button" aria-expanded="false" aria-controls="dd-services" aria-label="Show services">
          <svg class="ic" aria-hidden="true"><use href="#i-caret-down"/></svg>
        </button>
        <div class="dd__panel" id="dd-services">
          <div class="dd__grid">
            <?php foreach (site_services() as $slug => $s): ?>
              <a class="dd__item" href="<?= e(url('services/' . $slug)) ?>">
                <span class="dd__ic"><svg class="ic" aria-hidden="true"><use href="#i-<?= e($s['icon']) ?>"/></svg></span>
                <span><b><?= e($s['name']) ?></b><span><?= e($s['line']) ?></span></span>
              </a>
            <?php endforeach; ?>
          </div>
          <a class="dd__feature" href="<?= e(url('odoo-modules/')) ?>">
            <span class="dd__feature-k">Odoo, module by module</span>
            <b>Pick the modules you need and send us a quick enquiry.</b>
            <span class="dd__feature-go">See all <?= count(site_modules()) ?> modules <svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
          </a>
        </div>
      </div>
      <a href="<?= e(url('odoo-modules/')) ?>"<?= $cur('modules') ?>>Odoo modules</a>
      <a href="<?= e(url('how-we-work/')) ?>"<?= $cur('how') ?>>How we work</a>
      <a href="<?= e(url('pricing/')) ?>"<?= $cur('pricing') ?>>Pricing</a>
      <a href="<?= e(url('careers/')) ?>"<?= $cur('careers') ?>>Careers</a>
    </nav>
    <div class="hdr__tools">
      <button class="icon-btn" id="themeBtn" type="button" aria-label="Dark theme" aria-pressed="false">
        <svg class="ic ic--moon" aria-hidden="true"><use href="#i-moon"/></svg>
        <svg class="ic ic--sun" aria-hidden="true"><use href="#i-sun"/></svg>
      </button>
      <a class="btn btn--primary" href="<?= e(url('contact/')) ?>"<?= $cur('contact') ?>>Get in touch</a>
      <button class="menu-btn" id="menuBtn" type="button" aria-expanded="false" aria-controls="menu"><span>Menu</span></button>
    </div>
  </div>
</header>

<div class="menu" id="menu" aria-hidden="true">
  <nav class="menu__nav" aria-label="Menu">
    <a class="menu__big" href="<?= e(url('services/')) ?>">Services</a>
    <ul class="menu__sub">
      <?php foreach (site_services() as $slug => $s): ?><li><a href="<?= e(url('services/' . $slug)) ?>"><?= e($s['name']) ?></a></li><?php endforeach; ?>
    </ul>
    <a class="menu__big" href="<?= e(url('odoo-modules/')) ?>">Odoo modules</a>
    <a class="menu__big" href="<?= e(url('how-we-work/')) ?>">How we work</a>
    <a class="menu__big" href="<?= e(url('pricing/')) ?>">Pricing</a>
    <a class="menu__big" href="<?= e(url('careers/')) ?>">Careers</a>
    <a class="menu__big" href="<?= e(url('faq/')) ?>">FAQ</a>
  </nav>
  <a class="btn btn--primary" href="<?= e(url('contact/')) ?>">Get in touch</a>
</div>
