<?php
/**
 * App shell for the team area.
 * @var string $content  @var string|null $title  @var string|null $nav  active nav key
 */
$me = auth_user();
$nav = $nav ?? '';
$counts = nav_counts();
$link = static function (string $key, string $href, string $icon, string $label, int $count = 0, bool $alert = false) use ($nav): string {
    return '<a class="side__link' . ($nav === $key ? ' is-active' : '') . '" href="' . e($href) . '"' . ($nav === $key ? ' aria-current="page"' : '') . '>'
        . icon($icon) . '<span>' . e($label) . '</span>'
        . ($count > 0 ? '<span class="side__count' . ($alert ? ' is-alert' : '') . '">' . ($count > 99 ? '99+' : $count) . '</span>' : '')
        . '</a>';
};
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e(($title ?? 'Team') . ' | ' . company_name()) ?></title>
<link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="32x32">
<link rel="preload" href="<?= e(url('fonts/outfit-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('fonts/ibm-plex-sans-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<script src="<?= e(asset('assets/js/theme-init.js')) ?>"></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</head>
<body data-csrf="<?= e(csrf_token()) ?>">
<div class="app" id="app">
  <aside class="side" id="side" aria-label="Main navigation">
    <a class="side__logo" href="<?= e(admin_url()) ?>"><img src="<?= e(url('logo-automate-reversed.svg')) ?>" alt="<?= e(company_name()) ?>" width="142" height="32"></a>

    <nav class="side__group" aria-label="Overview">
      <?= $link('home', admin_url(), 'squares-four', 'Dashboard') ?>
      <?= $link('tasks', admin_url('tasks'), 'check-square', 'Tasks', $counts['tasks'], $counts['overdue'] > 0) ?>
    </nav>

    <?php if (user_can('ats')): ?>
    <nav class="side__group" aria-label="Recruitment">
      <p class="side__label">Recruitment</p>
      <?= $link('ats', admin_url('dashboard/ats'), 'chart-bar', 'ATS dashboard') ?>
      <?= $link('jobs', admin_url('jobs'), 'briefcase', 'Jobs') ?>
      <?= $link('candidates', admin_url('candidates'), 'users-three', 'Candidates') ?>
      <?= $link('pipeline', admin_url('pipeline'), 'kanban', 'Hiring pipeline', $counts['new_apps']) ?>
      <?= $link('interviews', admin_url('interviews'), 'calendar-dots', 'Interviews', $counts['my_interviews']) ?>
    </nav>
    <?php elseif (user_can('interviews')): ?>
    <nav class="side__group" aria-label="Recruitment">
      <p class="side__label">Recruitment</p>
      <?= $link('interviews', admin_url('interviews'), 'calendar-dots', 'My interviews', $counts['my_interviews']) ?>
    </nav>
    <?php endif; ?>

    <?php if (user_can('crm')): ?>
    <nav class="side__group" aria-label="Sales">
      <p class="side__label">Sales</p>
      <?= $link('crm', admin_url('dashboard/crm'), 'chart-line-up', 'CRM dashboard') ?>
      <?= $link('leads', admin_url('leads'), 'funnel', 'Leads', $counts['new_leads']) ?>
      <?= $link('leadboard', admin_url('leads/board'), 'kanban', 'Sales pipeline') ?>
      <?= $link('contacts', admin_url('contacts'), 'address-book', 'Contacts') ?>
    </nav>
    <?php endif; ?>

    <?php if (user_can('settings')): ?>
    <nav class="side__group" aria-label="Administration">
      <p class="side__label">Admin</p>
      <?= $link('team', admin_url('team'), 'user-gear', 'Team') ?>
      <?= $link('settings', admin_url('settings'), 'gear-six', 'Settings') ?>
    </nav>
    <?php endif; ?>

    <div class="side__foot">
      <a class="side__site" href="<?= e(url('')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>View website</a>
      <a class="side__site" href="<?= e(url('careers/')) ?>" target="_blank" rel="noopener"><?= icon('arrow-square-out') ?>View careers page</a>
    </div>
  </aside>
  <div class="scrim" data-nav-close></div>

  <div class="main">
    <header class="top">
      <button class="icon-btn top__menu" type="button" data-nav-toggle aria-controls="side" aria-expanded="false" aria-label="Open navigation"><?= icon('list') ?></button>
      <form class="search-box" action="<?= e(admin_url('search')) ?>" method="get" role="search">
        <?= icon('magnifying-glass') ?>
        <input type="search" name="q" value="<?= e($nav === 'search' ? input('q') : '') ?>" placeholder="Search candidates, leads, contacts, jobs" aria-label="Search">
      </form>
      <div class="top__tools">
        <?php if (user_can('ats') || user_can('crm')): ?>
        <details class="dropdown">
          <summary class="btn btn--primary btn--sm"><?= icon('plus') ?>New</summary>
          <div class="dropdown__menu">
            <?php if (user_can('ats')): ?>
              <a href="<?= e(admin_url('jobs/new')) ?>"><?= icon('briefcase') ?>Job</a>
              <a href="<?= e(admin_url('candidates/new')) ?>"><?= icon('user-plus') ?>Candidate</a>
            <?php endif; ?>
            <?php if (user_can('crm')): ?>
              <a href="<?= e(admin_url('leads/new')) ?>"><?= icon('funnel') ?>Lead</a>
              <a href="<?= e(admin_url('contacts/new')) ?>"><?= icon('address-book') ?>Contact</a>
            <?php endif; ?>
            <div class="dropdown__sep"></div>
            <a href="<?= e(admin_url('tasks')) ?>#new-task"><?= icon('check-square') ?>Task</a>
          </div>
        </details>
        <?php endif; ?>
        <button class="icon-btn" type="button" data-theme-toggle aria-label="Dark theme" aria-pressed="false"><?= icon('moon', 'ic ic--moon') ?><?= icon('sun', 'ic ic--sun') ?></button>
        <details class="dropdown usermenu">
          <summary aria-label="Account menu"><?= avatar((string) $me['name']) ?><span class="usermenu__text usermenu__name"><?= e($me['name']) ?><span class="usermenu__role"><?= e(role_label((string) $me['role'])) ?></span></span></summary>
          <div class="dropdown__menu">
            <a href="<?= e(admin_url('account')) ?>"><?= icon('user-circle') ?>My account</a>
            <div class="dropdown__sep"></div>
            <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('sign-out') ?>Sign out</button></form>
          </div>
        </details>
      </div>
    </header>

    <main class="page<?= !empty($wide) ? ' page--wide' : '' ?>" id="main">
      <?= $content ?>
    </main>
  </div>
</div>

<?php $flashes = take_flashes(); if ($flashes): ?>
<div class="flashes" role="status" aria-live="polite">
  <?php foreach ($flashes as $f): ?>
    <div class="flash flash--<?= e($f['type']) ?>"><?= icon($f['type'] === 'error' ? 'warning' : ($f['type'] === 'success' ? 'check-circle' : 'info')) ?><span><?= e($f['message']) ?></span><button type="button" data-dismiss aria-label="Dismiss">&times;</button></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="tip" id="tip" role="tooltip"></div>
</body>
</html>
