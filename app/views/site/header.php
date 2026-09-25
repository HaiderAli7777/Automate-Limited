<?php
/**
 * Site header and mobile menu.
 * @var bool|null $home   true on the homepage, where section links scroll in place
 * @var string|null $active  'careers' to mark the Careers link current
 */
$home = !empty($home);
$active = $active ?? '';
$to = static fn (string $hash): string => $home ? '#' . $hash : url('') . '#' . $hash;
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
      <a href="<?= e($to('services')) ?>"<?= $home ? ' data-watch="services"' : '' ?>>Services</a>
      <a href="<?= e($to('how')) ?>"<?= $home ? ' data-watch="how"' : '' ?>>How we work</a>
      <a href="<?= e($to('engage')) ?>"<?= $home ? ' data-watch="engage"' : '' ?>>Pricing</a>
      <a href="<?= e(url('careers/')) ?>"<?= $active === 'careers' ? ' aria-current="true"' : '' ?>>Careers</a>
      <a href="<?= e($to('faq')) ?>"<?= $home ? ' data-watch="faq"' : '' ?>>FAQ</a>
    </nav>
    <div class="hdr__tools">
      <button class="icon-btn" id="themeBtn" type="button" aria-label="Dark theme" aria-pressed="false">
        <svg class="ic ic--moon" aria-hidden="true"><use href="#i-moon"/></svg>
        <svg class="ic ic--sun" aria-hidden="true"><use href="#i-sun"/></svg>
      </button>
      <a class="btn btn--primary" href="<?= e($to('contact')) ?>">Get in touch</a>
      <button class="menu-btn" id="menuBtn" type="button" aria-expanded="false" aria-controls="menu"><span>Menu</span></button>
    </div>
  </div>
</header>

<div class="menu" id="menu" aria-hidden="true">
  <a href="<?= e($to('services')) ?>">Services</a>
  <a href="<?= e($to('how')) ?>">How we work</a>
  <a href="<?= e($to('engage')) ?>">Pricing</a>
  <a href="<?= e(url('careers/')) ?>">Careers</a>
  <a href="<?= e($to('faq')) ?>">FAQ</a>
  <a class="btn btn--primary" href="<?= e($to('contact')) ?>">Get in touch</a>
</div>
