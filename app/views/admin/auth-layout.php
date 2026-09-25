<?php /** Minimal shell for sign-in and password pages. @var string $content @var string $title */ ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> | <?= e(company_name()) ?></title>
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<script src="<?= e(asset('assets/js/theme-init.js')) ?>"></script>
</head>
<body class="auth">
<main class="auth__wrap">
  <div class="auth__logo"><a href="<?= e(url('')) ?>"><img src="<?= e(url('logo-automate.svg')) ?>" alt="<?= e(company_name()) ?>" width="170" height="39"></a></div>
  <div class="panel auth__card">
    <?php foreach (take_flashes() as $f): ?>
      <p class="notice notice--<?= $f['type'] === 'error' ? 'bad' : ($f['type'] === 'success' ? 'ok' : 'info') ?>" style="margin-bottom:16px"><?= e($f['message']) ?></p>
    <?php endforeach; ?>
    <?= $content ?>
  </div>
  <p class="auth__foot"><a href="<?= e(url('')) ?>">Back to the website</a></p>
</main>
</body>
</html>
