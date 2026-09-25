<?php
/** Site footer and the shared script. @var bool|null $home */
$home = !empty($home);
$to = static fn (string $hash): string => $home ? '#' . $hash : url('') . '#' . $hash;
?>
<footer class="ftr" id="ftr">
  <div class="wrap">
    <div class="ftr__grid">
      <div>
        <a class="ftr__logo" href="<?= $home ? '#top' : e(url('')) ?>" aria-label="Automate Limited, <?= $home ? 'back to top' : 'home' ?>">
          <svg viewBox="0 0 3309 753" aria-hidden="true"><use href="#lk"/></svg>
        </a>
        <p class="ftr__blurb">Odoo ERP, websites, and the design and marketing around them. Based in Pakistan, working with clients worldwide.</p>
      </div>
      <div>
        <p class="ftr__h">Services</p>
        <ul>
          <li><a href="<?= e($to('svc-odoo')) ?>">Odoo ERP</a></li>
          <li><a href="<?= e($to('svc-web')) ?>">Website development</a></li>
          <li><a href="<?= e($to('svc-seo')) ?>">SEO</a></li>
          <li><a href="<?= e($to('svc-marketing')) ?>">Digital marketing</a></li>
          <li><a href="<?= e($to('svc-design')) ?>">Graphic design</a></li>
          <li><a href="<?= e($to('svc-custom')) ?>">Custom solutions</a></li>
        </ul>
      </div>
      <div>
        <p class="ftr__h">Company</p>
        <ul>
          <li><a href="<?= e($to('how')) ?>">How we work</a></li>
          <li><a href="<?= e($to('engage')) ?>">Pricing</a></li>
          <li><a href="<?= e(url('careers/')) ?>">Careers</a></li>
          <li><a href="<?= e($to('faq')) ?>">FAQ</a></li>
          <li><a href="<?= e($to('contact')) ?>">Contact</a></li>
        </ul>
      </div>
      <div>
        <p class="ftr__h">Contact</p>
        <ul>
          <li><a href="mailto:info@automateltd.com">info@automateltd.com</a></li>
        </ul>
      </div>
    </div>
    <div class="ftr__base">
      <span>&copy; <span id="yr"><?= date('Y') ?></span> Automate Limited</span>
      <nav aria-label="Legal and team">
        <a href="<?= e(url('privacy/')) ?>">Privacy</a>
        <a class="ftr__team" href="<?= e(url('admin/')) ?>" rel="nofollow"><svg class="ic" aria-hidden="true"><use href="#i-lock"/></svg>Team login</a>
        <?php if ($home): ?><a href="#top">Back to top</a><?php endif; ?>
      </nav>
    </div>
  </div>
</footer>
<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
