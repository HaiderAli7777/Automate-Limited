<?php
/* Not-found page. The web server sends missing URLs here (see .htaccess). */
declare(strict_types=1);
if (!defined('APP_ROOT')) {
    require __DIR__ . '/app/bootstrap.php';
}
if (!headers_sent()) {
    http_response_code(404);
}
partial('site/head', ['title' => 'Page not found | Automate Limited', 'description' => 'That page isn\'t here.', 'path' => '', 'noindex' => true]);
?>
<body data-page="404">
<?php partial('site/header'); ?>
<main id="main" class="done">
  <div class="wrap">
    <div class="done__card">
      <h1>That page isn't here.</h1>
      <p>The link may be out of date, or the address may have a typo in it. Everything else is where you left it.</p>
      <div class="done__cta">
        <a class="btn btn--primary" href="<?= e(url('')) ?>">Back to the site</a>
        <a class="btn btn--quiet" href="<?= e(url('careers/')) ?>">See careers</a>
      </div>
    </div>
  </div>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
