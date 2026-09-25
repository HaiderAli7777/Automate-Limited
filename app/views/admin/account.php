<?php /** @var array $user */ ?>
<div class="phead"><div><h1>My account</h1><p class="phead__sub"><?= e(role_label((string) $user['role'])) ?>. <?= e(ROLE_HELP[$user['role']] ?? '') ?></p></div></div>
<form method="post" action="<?= e(admin_url('account')) ?>" class="panel" style="max-width:760px">
  <?= csrf_field() ?>
  <div class="panel__body">
    <fieldset class="fieldset">
      <legend>Profile</legend>
      <div class="grid-2">
        <?= fi('name', 'Name', $user['name'], ['required' => true, 'attrs' => ['autocomplete' => 'name']]) ?>
        <?= fi('email', 'Email', $user['email'], ['type' => 'email', 'required' => true, 'attrs' => ['autocomplete' => 'email']]) ?>
        <?= fi('title', 'Job title', $user['title'], ['optional' => true]) ?>
        <?= fi('phone', 'Phone', $user['phone'], ['optional' => true, 'type' => 'tel']) ?>
      </div>
    </fieldset>
    <fieldset class="fieldset">
      <legend>Change password</legend>
      <p class="help">Leave these empty to keep your current password. Changing it signs out your other sessions.</p>
      <div class="grid-2">
        <?= fi('current_password', 'Current password', '', ['type' => 'password', 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <?= fi('new_password', 'New password', '', ['type' => 'password', 'help' => 'At least 10 characters.', 'attrs' => ['autocomplete' => 'new-password']]) ?>
      </div>
    </fieldset>
    <div class="form-actions"><button class="btn btn--primary" type="submit">Save</button></div>
  </div>
</form>
