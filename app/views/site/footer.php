<?php
/** Site footer and the shared script. @var bool|null $home */
$home = !empty($home);
?>
<footer class="ftr" id="ftr">
  <div class="wrap">
    <div class="ftr__grid">
      <div>
        <a class="ftr__logo" href="<?= $home ? '#top' : e(url('')) ?>" aria-label="Automate Limited, <?= $home ? 'back to top' : 'home' ?>">
          <svg viewBox="0 0 3309 753" aria-hidden="true"><use href="#lk"/></svg>
        </a>
        <p class="ftr__blurb">Odoo ERP, websites, and the design and marketing around them. Based in Pakistan, working with clients worldwide.</p>
        <a class="btn btn--quiet ftr__cta" href="<?= e(url('contact/')) ?>">Start an enquiry</a>
      </div>
      <div>
        <p class="ftr__h">Services</p>
        <ul>
          <?php foreach (site_services() as $slug => $s): ?><li><a href="<?= e(url('services/' . $slug)) ?>"><?= e($s['name']) ?></a></li><?php endforeach; ?>
          <li><a href="<?= e(url('odoo-modules/')) ?>">Odoo modules</a></li>
        </ul>
      </div>
      <div>
        <p class="ftr__h">Company</p>
        <ul>
          <li><a href="<?= e(url('how-we-work/')) ?>">How we work</a></li>
          <li><a href="<?= e(url('pricing/')) ?>">Pricing</a></li>
          <li><a href="<?= e(url('careers/')) ?>">Careers</a></li>
          <li><a href="<?= e(url('faq/')) ?>">FAQ</a></li>
          <li><a href="<?= e(url('contact/')) ?>">Contact</a></li>
        </ul>
      </div>
      <div>
        <p class="ftr__h">Contact</p>
        <ul>
          <li><a href="mailto:info@automateltd.com">info@automateltd.com</a></li>
          <li><a href="<?= e(url('contact/')) ?>">Send us a message</a></li>
        </ul>
        <address class="ftr__addr"><a href="<?= e(OFFICE['maps']) ?>" target="_blank" rel="noopener"><?= implode('<br>', array_map('e', office_lines())) ?></a></address>
      </div>
    </div>
    <div class="ftr__base">
      <span>&copy; <span id="yr"><?= date('Y') ?></span> Automate Limited</span>
      <nav aria-label="Legal and team">
        <a href="<?= e(url('privacy/')) ?>">Privacy</a>
        <a class="ftr__team" href="<?= e(url('admin/')) ?>" rel="nofollow"><svg class="ic" aria-hidden="true"><use href="#i-lock"/></svg>Team login</a>
        <a href="#top" data-top>Back to top</a>
      </nav>
    </div>
  </div>
</footer>
<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
