<?php /* Industries we work with: a bento of photo tiles, each opening a pre-filled enquiry. */
$u = static fn (string $id, int $w, int $h): string => 'https://images.unsplash.com/' . $id . '?auto=format&fit=crop&crop=entropy&w=' . $w . '&h=' . $h . '&q=70&sat=-10';
?>
<section class="sec" id="industries">
  <div class="wrap">
    <div class="sec-head rv">
      <h2>Built for how your industry runs.</h2>
      <p>The same system, set up around the way your business actually trades.</p>
    </div>
    <ul class="inds rv" style="--rd:.08s">
      <?php foreach (site_industries() as $ind): $wide = $ind['size'] === 'wide'; ?>
        <li class="ind<?= $ind['size'] ? ' ind--' . e($ind['size']) : '' ?>">
          <a href="<?= e(enquiry_url('Odoo ERP', $ind['module'])) ?>">
            <img src="<?= e($u($ind['img'], $wide ? 1200 : 700, $wide ? 620 : 760)) ?>" srcset="<?= e($u($ind['img'], $wide ? 800 : 480, $wide ? 413 : 521)) ?> <?= $wide ? 800 : 480 ?>w, <?= e($u($ind['img'], $wide ? 1200 : 700, $wide ? 620 : 760)) ?> <?= $wide ? 1200 : 700 ?>w, <?= e($u($ind['img'], $wide ? 1800 : 1050, $wide ? 930 : 1140)) ?> <?= $wide ? 1800 : 1050 ?>w" sizes="(min-width: 960px) <?= $wide ? '50vw' : '25vw' ?>, 92vw" width="<?= $wide ? 1200 : 700 ?>" height="<?= $wide ? 620 : 760 ?>" alt="<?= e($ind['alt']) ?>" loading="lazy" decoding="async">
            <span class="ind__body">
              <h3><?= e($ind['name']) ?></h3>
              <span class="ind__text"><?= e($ind['text']) ?></span>
              <span class="ind__go">Talk to us<svg class="ic" aria-hidden="true"><use href="#i-arrow-up-right"/></svg></span>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
