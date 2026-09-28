<?php
/** @var array|null $user  @var array $history */
$isNew = !$user;
$isMe = !$isNew && (int) $user['id'] === auth_id();
$errs = form_errors();
$roles = grantable_roles();
$role = fval('role', $user['role'] ?? 'recruiter');
if (!isset(ROLES[$role])) {
    $role = 'recruiter';
}
$mine = user_permissions(auth_user());
$current = has_old() ? normalize_permissions(old_array('perms') ?? []) : ($user ? user_permissions($user) : (ROLE_PRESETS[$role] ?? []));
if (has_old() && $role !== 'custom') {
    $current = $role === 'admin' ? all_permissions() : (ROLE_PRESETS[$role] ?? []);
}
$presetJson = static fn (string $key): string => json_encode($key === 'admin' ? all_permissions() : ($key === 'custom' ? null : (ROLE_PRESETS[$key] ?? [])));
$roleIcon = ['admin' => 'shield-check', 'manager' => 'user-gear', 'hr' => 'identification-card', 'recruiter' => 'briefcase', 'sales' => 'funnel', 'interviewer' => 'calendar-dots', 'viewer' => 'eye', 'custom' => 'sliders-horizontal'];
?>
<a class="crumb" href="<?= e(admin_url('team')) ?>"><?= icon('arrow-left') ?>Team and access</a>
<div class="phead">
  <div>
    <h1><?= $isNew ? 'Add a team member' : e($user['name']) ?></h1>
    <p class="phead__sub"><?= $isNew ? 'They get their own sign-in. You decide what they can see and do.' : e($user['email']) . ($user['last_login_at'] ? ' · last signed in ' . e(time_ago($user['last_login_at'])) : ' · has not signed in yet') ?></p>
  </div>
  <?php if (!$isNew): ?><div class="phead__actions"><?= (int) $user['is_active'] ? badge('Active', 'green') : badge('Switched off', 'slate') ?></div><?php endif; ?>
</div>

<div class="split">
<form method="post" action="<?= e(admin_url($isNew ? 'team/new' : 'team/' . $user['id'])) ?>" class="panel" data-access-form>
  <?= csrf_field() ?>
  <div class="panel__body">
    <fieldset class="fieldset">
      <legend>Details</legend>
      <div class="grid-2">
        <?= fi('name', 'Full name', $user['name'] ?? '', ['required' => true, 'attrs' => ['autocomplete' => 'off']]) ?>
        <?= fi('email', 'Work email', $user['email'] ?? '', ['type' => 'email', 'required' => true, 'help' => 'They sign in with this, and notifications go here.', 'attrs' => ['autocomplete' => 'off']]) ?>
        <?= fi('title', 'Job title', $user['title'] ?? '', ['optional' => true, 'placeholder' => 'For example HR Executive']) ?>
        <?= fi('phone', 'Phone', $user['phone'] ?? '', ['optional' => true, 'type' => 'tel']) ?>
      </div>
    </fieldset>

    <fieldset class="fieldset" id="access">
      <legend>Access level</legend>
      <p class="help" style="margin-top:-8px">Start from a ready-made level. Change any tick below and it becomes Custom access.</p>
      <?php if ($isMe && !is_role('admin')): ?><p class="notice notice--info">You can't change your own access. Ask an administrator.</p><?php endif; ?>
      <div class="rolecards" role="radiogroup" aria-label="Access level">
        <?php foreach ($roles as $key => $label): ?>
          <label class="rolecard">
            <input type="radio" name="role" value="<?= e($key) ?>" data-preset='<?= e($presetJson($key)) ?>'<?= checked($role === $key) ?><?= $isMe && !is_role('admin') ? ' disabled' : '' ?>>
            <span class="rolecard__ic"><?= icon($roleIcon[$key] ?? 'user-circle') ?></span>
            <span class="rolecard__text"><b><?= e($label) ?></b><span><?= e(ROLE_HELP[$key]) ?></span></span>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($errs['role'])): ?><p class="error"><?= e($errs['role']) ?></p><?php endif; ?>

      <div class="perms" data-perms>
        <?php foreach (PERMISSION_GROUPS as $group => $perms): ?>
          <div class="perms__group">
            <p class="perms__title"><?= e($group) ?></p>
            <?php foreach ($perms as $perm => $text): $grantable = is_role('admin') || in_array($perm, $mine, true); ?>
              <label class="perm<?= $grantable ? '' : ' is-locked' ?>">
                <input type="checkbox" name="perms[]" value="<?= e($perm) ?>"<?= checked(in_array($perm, $current, true)) ?><?= $grantable && !($isMe && !is_role('admin')) ? '' : ' disabled' ?><?= isset(PERMISSION_REQUIRES[$perm]) ? ' data-needs="' . e(PERMISSION_REQUIRES[$perm]) . '"' : '' ?>>
                <span><?= e($text) ?><?= $grantable ? '' : ' <span class="muted">(you can\'t grant this)</span>' ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="help" data-admin-note<?= $role === 'admin' ? '' : ' hidden' ?>>Administrators always have every permission, and they are the only people who can change other administrators.</p>
    </fieldset>

    <fieldset class="fieldset">
      <legend>Sign-in</legend>
      <?php if (!$isNew && !$isMe): ?><?= fc('is_active', 'Account is active', (bool) $user['is_active'], 'Switch off when someone leaves. They are signed out straight away and their history stays.') ?><?php elseif ($isMe): ?><input type="hidden" name="is_active" value="1"><?php endif; ?>
      <?= fc('send_invite', $isNew ? 'Email them an invitation with a link to set their own password (recommended)' : 'Email them a link to set a new password', $isNew) ?>
      <?= fi('password', $isNew ? 'Or set a password for them now' : 'Or set a new password now', '', ['type' => 'password', 'optional' => true, 'help' => 'At least 10 characters. ' . ($isNew ? 'Share it with them privately.' : 'Leave empty to keep their current password.'), 'attrs' => ['autocomplete' => 'new-password']]) ?>
    </fieldset>

    <div class="form-actions">
      <button class="btn btn--primary" type="submit"><?= icon('check') ?><?= $isNew ? 'Add to the team' : 'Save changes' ?></button>
      <a class="btn btn--ghost" href="<?= e(admin_url('team')) ?>">Cancel</a>
    </div>
  </div>
</form>

<aside class="stack">
  <div class="panel">
    <div class="panel__head"><h2>At a glance</h2></div>
    <div class="panel__body">
      <dl class="dl" data-access-summary aria-live="polite">
        <?php $preview = $user ?? ['role' => $role, 'permissions' => json_encode($current)]; foreach (access_summary($preview) as $k => $v): if ($k === 'Also' && $v === '') continue; ?>
          <dt><?= e($k) ?></dt><dd><?= e($v) ?></dd>
        <?php endforeach; ?>
      </dl>
      <p class="help" style="margin-top:12px">Save to apply changes. People already signed in get the new access on their next click.</p>
    </div>
  </div>
  <?php if (!$isNew): ?>
  <div class="panel">
    <div class="panel__head"><h2>History</h2></div>
    <div class="panel__body"><?php partial('admin/partials/timeline', ['items' => $history]); ?></div>
  </div>
  <?php else: ?>
  <div class="panel">
    <div class="panel__head"><h2>What happens next</h2></div>
    <div class="panel__body">
      <ol class="steps-list" style="margin:0">
        <li>They get an email with a link to set their password. The link works for 3 days.</li>
        <li>They sign in at <span class="t-strong"><?= e(site_origin() . admin_url('login')) ?></span>.</li>
        <li>They only see the parts of the team area you ticked above.</li>
      </ol>
    </div>
  </div>
  <?php endif; ?>
</aside>
</div>
