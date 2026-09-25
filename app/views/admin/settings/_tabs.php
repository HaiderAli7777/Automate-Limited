<?php /** @var string $tab */ ?>
<div class="phead"><div><h1>Settings</h1><p class="phead__sub">How the website, the ATS and the CRM behave.</p></div></div>
<nav class="tabs" aria-label="Settings sections">
  <a href="<?= e(admin_url('settings')) ?>" class="<?= $tab === 'general' ? 'is-active' : '' ?>">General and email</a>
  <a href="<?= e(admin_url('settings/stages')) ?>" class="<?= $tab === 'stages' ? 'is-active' : '' ?>">Pipeline stages</a>
  <a href="<?= e(admin_url('settings/templates')) ?>" class="<?= $tab === 'templates' ? 'is-active' : '' ?>">Email templates</a>
  <a href="<?= e(admin_url('team')) ?>">Team</a>
</nav>
