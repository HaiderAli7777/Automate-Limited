<?php
/** @var array $job */
partial('site/head', ['title' => 'Application sent | Automate Limited', 'description' => 'Your application has been sent.', 'path' => 'careers/' . $job['slug'], 'noindex' => true]);
?>
<body data-page="done">
<?php partial('site/header', ['active' => 'careers']); ?>
<main id="main" class="done">
  <div class="wrap">
    <div class="done__card">
      <div class="done__ic"><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg></div>
      <h1>Application sent.</h1>
      <p>Thank you for applying for <strong><?= e($job['title']) ?></strong>. It has reached our hiring team, and a confirmation is on its way to your inbox.</p>
      <p>If your experience matches what the role needs, we'll contact you about a screening call.</p>
      <div class="done__cta">
        <a class="btn btn--primary" href="<?= e(url('careers/')) ?>">See other roles</a>
        <a class="btn btn--quiet" href="<?= e(url('')) ?>">Back to the homepage</a>
      </div>
    </div>
  </div>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
