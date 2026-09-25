<?php /** @var array|null $user */ $isNew = !$user; ?>
<a class="crumb" href="<?= e(admin_url('team')) ?>"><?= icon('arrow-left') ?>Team</a>
<div class="phead"><div><h1><?= $isNew ? 'Add a teammate' : e($user['name']) ?></h1></div></div>
<form method="post" action="<?= e(admin_url($isNew ? 'team/new' : 'team/' . $user['id'])) ?>" class="panel" style="max-width:820px">
  <?= csrf_field() ?>
  <div class="panel__body">
    <fieldset class="fieldset">
      <legend>Details</legend>
      <div class="grid-2">
        <?= fi('name', 'Name', $user['name'] ?? '', ['required' => true]) ?>
        <?= fi('email', 'Email', $user['email'] ?? '', ['type' => 'email', 'required' => true, 'help' => 'They sign in with this.']) ?>
        <?= fi('title', 'Job title', $user['title'] ?? '', ['optional' => true]) ?>
        <?= fi('phone', 'Phone', $user['phone'] ?? '', ['optional' => true, 'type' => 'tel']) ?>
      </div>
    </fieldset>
    <fieldset class="fieldset">
      <legend>Role</legend>
      <?php $cur = fval('role', $user['role'] ?? 'recruiter'); $errs = form_errors(); ?>
      <div class="stack-sm">
        <?php foreach (ROLES as $key => $label): ?>
          <label class="checkbox"><input type="radio" name="role" value="<?= e($key) ?>"<?= checked($cur === $key) ?>><span><strong><?= e($label) ?></strong><span class="help" style="display:block"><?= e(ROLE_HELP[$key]) ?></span></span></label>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($errs['role'])): ?><p class="error"><?= e($errs['role']) ?></p><?php endif; ?>
    </fieldset>
    <fieldset class="fieldset">
      <legend>Access</legend>
      <?php if (!$isNew): ?><?= fc('is_active', 'Account is active', (bool) $user['is_active'], 'Switch off to stop them signing in. Their history stays.') ?><?php endif; ?>
      <?= fc('send_invite', $isNew ? 'Email them a link to set their own password' : 'Email them a new link to set their password', $isNew) ?>
      <?= fi('password', $isNew ? 'Or set a password now' : 'Set a new password', '', ['type' => 'password', 'optional' => true, 'help' => 'At least 10 characters. Leave empty to keep it unchanged.', 'attrs' => ['autocomplete' => 'new-password']]) ?>
    </fieldset>
    <div class="form-actions"><button class="btn btn--primary" type="submit">Save</button><a class="btn btn--ghost" href="<?= e(admin_url('team')) ?>">Cancel</a></div>
  </div>
</form>
