<?php
/* Homepage careers teaser. Lists up to three live openings when the database is set up. */
$roles = [];
$roleCount = 0;
if (app_installed()) {
    try {
        $all = public_jobs();
        $roleCount = count($all);
        $roles = array_slice($all, 0, 3);
    } catch (Throwable $e) {
        $roles = [];
    }
}
$img = 'https://images.unsplash.com/photo-1603195827187-459ab02554a0?auto=format&fit=crop&crop=faces,edges&q=72&sat=-12&con=5';
?>
  <section class="sec band" id="careers">
    <div class="wrap join">
      <figure class="join__media rv">
        <img src="<?= e($img) ?>&amp;w=900&amp;h=1125" srcset="<?= e($img) ?>&amp;w=560&amp;h=700 560w, <?= e($img) ?>&amp;w=900&amp;h=1125 900w, <?= e($img) ?>&amp;w=1400&amp;h=1750 1400w" sizes="(min-width: 900px) 40vw, 92vw" width="900" height="1125" alt="Three colleagues working through a problem together on a laptop" loading="lazy" decoding="async">
      </figure>
      <div class="rv" style="--rd:.12s">
        <h2>Build the systems businesses run on.</h2>
        <p class="join__lede">We hire Odoo consultants and developers, web developers, designers and marketers. The work runs real shops, warehouses and month-end closes.</p>
        <?php if ($roles): ?>
          <div class="roles">
            <?php foreach ($roles as $r): ?>
              <a class="role" href="<?= e(url('careers/' . $r['slug'])) ?>">
                <span class="role__title"><?= e($r['title']) ?></span>
                <span class="role__meta">
                  <?php if ($r['department']): ?><span><?= e($r['department']) ?></span><?php endif; ?>
                  <?php if ($r['location']): ?><span><?= e($r['location']) ?></span><?php endif; ?>
                  <span><?= e(EMPLOYMENT_TYPES[$r['employment_type']] ?? '') ?></span>
                </span>
                <span class="role__go" aria-hidden="true"><svg class="ic"><use href="#i-arrow-right"/></svg></span>
              </a>
            <?php endforeach; ?>
          </div>
          <a class="btn btn--quiet" href="<?= e(url('careers/')) ?>"><?= $roleCount > 3 ? 'See all ' . $roleCount . ' open roles' : 'See careers' ?></a>
        <?php else: ?>
          <a class="btn btn--quiet" href="<?= e(url('careers/')) ?>">See careers</a>
        <?php endif; ?>
      </div>
    </div>
  </section>
