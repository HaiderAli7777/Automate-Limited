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
      <?= $link('pool', admin_url('talent-pool'), 'user-list', 'Talent pool', $counts['pool_due'], $counts['pool_due'] > 0) ?>
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

    <?php if (user_can('hr.view')): ?>
    <nav class="side__group" aria-label="People">
      <p class="side__label">People</p>
      <?= $link('hr', admin_url('dashboard/hr'), 'chart-pie-slice', 'HR dashboard') ?>
      <?= $link('employees', admin_url('employees'), 'identification-card', 'Employees', $counts['probation_due'], $counts['probation_due'] > 0) ?>
      <?= $link('departments', admin_url('departments'), 'buildings', 'Departments') ?>
      <?php if (user_can('payroll.manage')): ?><?= $link('payroll', admin_url('payroll'), 'currency-circle-dollar', 'Payroll', $counts['draft_slips']) ?><?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php if (user_can('team') || user_can('settings') || user_can('audit.view')): ?>
    <nav class="side__group" aria-label="Administration">
      <p class="side__label">Admin</p>
      <?php if (user_can('team')): ?><?= $link('team', admin_url('team'), 'user-gear', 'Team and access') ?><?php endif; ?>
      <?php if (user_can('audit.view')): ?><?= $link('activity', admin_url('activity'), 'clock-counter-clockwise', 'Activity log') ?><?php endif; ?>
      <?php if (user_can('settings')): ?><?= $link('settings', admin_url('settings'), 'gear-six', 'Settings') ?><?php endif; ?>
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
        <input type="search" name="q" value="<?= e($nav === 'search' ? input('q') : '') ?>" placeholder="Search people, candidates, leads, jobs" aria-label="Search" aria-keyshortcuts="/"><kbd class="search-box__key" aria-hidden="true">/</kbd>
      </form>
      <div class="top__tools">
        <details class="dropdown">
          <summary class="btn btn--primary btn--sm"><?= icon('plus') ?><span class="hide-sm">New</span></summary>
          <div class="dropdown__menu">
            <?php if (user_can('ats.manage')): ?>
              <a href="<?= e(admin_url('jobs/new')) ?>"><?= icon('briefcase') ?>Job opening</a>
              <a href="<?= e(admin_url('candidates/new')) ?>"><?= icon('user-plus') ?>Candidate</a>
              <a href="<?= e(admin_url('interviews/new')) ?>"><?= icon('calendar-plus') ?>Interview</a>
            <?php endif; ?>
            <?php if (user_can('crm.manage')): ?>
              <?php if (user_can('ats.manage')): ?><div class="dropdown__sep"></div><?php endif; ?>
              <a href="<?= e(admin_url('leads/new')) ?>"><?= icon('funnel') ?>Lead</a>
              <a href="<?= e(admin_url('contacts/new')) ?>"><?= icon('address-book') ?>Contact</a>
            <?php endif; ?>
            <?php if (user_can('hr.manage') || user_can('payroll.manage')): ?>
              <div class="dropdown__sep"></div>
              <?php if (user_can('hr.manage')): ?><a href="<?= e(admin_url('employees/new')) ?>"><?= icon('identification-card') ?>Employee</a><?php endif; ?>
              <?php if (user_can('payroll.manage')): ?><a href="<?= e(admin_url('payroll/new')) ?>"><?= icon('currency-circle-dollar') ?>Payroll run</a><?php endif; ?>
            <?php endif; ?>
            <?php if (user_can('team')): ?>
              <div class="dropdown__sep"></div>
              <a href="<?= e(admin_url('team/new')) ?>"><?= icon('user-gear') ?>Team member</a>
            <?php endif; ?>
            <div class="dropdown__sep"></div>
            <a href="<?= e(admin_url('tasks')) ?>#new-task"><?= icon('check-square') ?>Task</a>
          </div>
        </details>
        <?php $recent = recent_notifications(6); ?>
        <details class="dropdown bell">
          <summary class="icon-btn" aria-label="Notifications<?= $counts['notifications'] ? ', ' . $counts['notifications'] . ' unread' : '' ?>"><?= icon('bell') ?><?php if ($counts['notifications']): ?><span class="bell__count" data-notif-count><?= $counts['notifications'] > 9 ? '9+' : $counts['notifications'] ?></span><?php endif; ?></summary>
          <div class="dropdown__menu bell__menu">
            <div class="bell__head"><b>Notifications</b>
              <?php if ($counts['notifications']): ?><form method="post" action="<?= e(admin_url('notifications/read-all')) ?>" data-read-all><?= csrf_field() ?><button type="submit" class="link small">Mark all as read</button></form><?php endif; ?>
            </div>
            <?php if (!$recent): ?>
              <p class="bell__empty">You're all caught up. New applications, enquiries and anything assigned to you will show up here.</p>
            <?php else: foreach ($recent as $n): ?>
              <a class="notif<?= $n['read_at'] ? '' : ' is-unread' ?>" href="<?= e(admin_url('notifications/' . $n['id'] . '/open')) ?>">
                <span class="notif__ic"><?= icon((string) ($n['icon'] ?: 'bell')) ?></span>
                <span class="notif__main"><span class="notif__title"><?= e($n['title']) ?></span><?php if ($n['body']): ?><span class="notif__body"><?= e($n['body']) ?></span><?php endif; ?><span class="notif__when"><?= e(time_ago($n['created_at'])) ?></span></span>
              </a>
            <?php endforeach; endif; ?>
            <a class="bell__all" href="<?= e(admin_url('notifications')) ?>">See all notifications</a>
          </div>
        </details>
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
