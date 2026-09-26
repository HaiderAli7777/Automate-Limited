<?php /** Shell for sign-in and password pages: brand panel + form. @var string $content @var string $title */ ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> | <?= e(company_name()) ?></title>
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="preload" href="<?= e(url('fonts/outfit-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<script src="<?= e(asset('assets/js/theme-init.js')) ?>"></script>
</head>
<body class="signin">
<aside class="signin__brand" aria-label="<?= e(company_name()) ?> team area">
  <a class="signin__logo" href="<?= e(url('')) ?>"><img src="<?= e(url('logo-automate-reversed.svg')) ?>" alt="<?= e(company_name()) ?>" width="160" height="36"></a>
  <div class="signin__pitch">
    <p class="signin__eyebrow">Team area</p>
    <h2>Hiring and sales, in one place.</h2>
    <ul class="signin__points">
      <li><?= icon('kanban') ?><span><b>Hiring pipeline</b>Applications from the careers page, stages, interviews and scorecards.</span></li>
      <li><?= icon('funnel') ?><span><b>Sales CRM</b>Every website enquiry becomes a lead you can follow to a signed deal.</span></li>
      <li><?= icon('shield-check') ?><span><b>Right access for everyone</b>Recruiters, sales and interviewers each see only what they need.</span></li>
    </ul>
  </div>
  <p class="signin__foot">&copy; <?= date('Y') ?> <?= e(company_name()) ?></p>
</aside>
<main class="signin__main">
  <div class="signin__card">
    <a class="signin__mlogo" href="<?= e(url('')) ?>"><img src="<?= e(url('logo-automate.svg')) ?>" alt="<?= e(company_name()) ?>" width="150" height="34"></a>
    <?php foreach (take_flashes() as $f): ?>
      <p class="notice notice--<?= $f['type'] === 'error' ? 'bad' : ($f['type'] === 'success' ? 'ok' : 'info') ?>" style="margin-bottom:16px"><?= e($f['message']) ?></p>
    <?php endforeach; ?>
    <?= $content ?>
    <p class="signin__back"><a href="<?= e(url('')) ?>"><?= icon('arrow-left') ?>Back to the website</a></p>
  </div>
</main>
</body>
</html>
