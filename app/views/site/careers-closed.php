<?php
/** @var array $job */
partial('site/head', ['title' => $job['title'] . ' | Careers at Automate Limited', 'description' => 'This role is no longer open.', 'path' => 'careers/' . $job['slug'], 'noindex' => true]);
?>
<body data-page="closed">
<?php partial('site/header', ['active' => 'careers']); ?>
<main id="main" class="done">
  <div class="wrap">
    <div class="done__card">
      <h1>This role has closed.</h1>
      <p><strong><?= e($job['title']) ?></strong> is no longer taking applications. Take a look at what's open now.</p>
      <div class="done__cta">
        <a class="btn btn--primary" href="<?= e(url('careers/')) ?>#roles">See open roles</a>
      </div>
    </div>
  </div>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
