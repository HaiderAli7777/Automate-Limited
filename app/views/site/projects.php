<?php /* The kinds of project we take on, as before-and-after stories. */ ?>
<section class="sec" id="projects">
  <div class="wrap projects">
    <div class="projects__head">
      <div class="sec-head rv">
        <h2>Projects we take on.</h2>
        <p>Most engagements start with a problem like one of these. Yours will have its own details.</p>
      </div>
      <a class="btn btn--primary rv" href="<?= e(url('contact/')) ?>">Get in touch</a>
    </div>
    <ol class="projects__list">
      <?php foreach (site_projects() as $i => $p): ?>
        <li class="proj rv" style="--rd:<?= number_format($i * 0.05, 2) ?>s">
          <div class="proj__title">
            <span class="proj__kind"><?= e($p['kind']) ?></span>
            <h3><?= e($p['title']) ?></h3>
            <div class="proj__mods"><?php foreach ($p['modules'] as $m): ?><a class="tag" href="<?= e(enquiry_url('Odoo ERP', $m)) ?>"><?= e($m) ?></a><?php endforeach; ?></div>
          </div>
          <div class="proj__col">
            <p class="proj__label">Before</p>
            <ul><?php foreach ($p['before'] as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul>
          </div>
          <div class="proj__col proj__col--after">
            <p class="proj__label">After</p>
            <ul><?php foreach ($p['after'] as $a): ?><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span><?= e($a) ?></span></li><?php endforeach; ?></ul>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
