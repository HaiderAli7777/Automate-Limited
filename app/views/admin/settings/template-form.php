<?php /** @var string $tab @var array $tpl */ partial('admin/settings/_tabs', ['tab' => $tab]); ?>
<a class="crumb" href="<?= e(admin_url('settings/templates')) ?>"><?= icon('arrow-left') ?>Email templates</a>
<div class="split">
  <form method="post" action="<?= e(admin_url('settings/templates/' . $tpl['id'])) ?>" class="panel">
    <?= csrf_field() ?>
    <div class="panel__body stack">
      <?= fi('name', 'Template name', $tpl['name']) ?>
      <?= fi('subject', 'Subject', $tpl['subject'], ['required' => true]) ?>
      <?= ft('body', 'Message', $tpl['body'], ['size' => 'tall', 'required' => true, 'help' => 'Plain text. Blank lines become paragraphs.']) ?>
      <div class="form-actions"><button class="btn btn--primary" type="submit">Save template</button></div>
    </div>
  </form>
  <div class="panel">
    <div class="panel__head"><h2>Placeholders</h2></div>
    <div class="panel__body">
      <p class="small muted" style="margin-bottom:10px">These are replaced when the email is sent.</p>
      <div class="chips"><?php foreach (TEMPLATE_VARS[$tpl['module']] ?? [] as $v): ?><code>{{<?= e($v) ?>}}</code><?php endforeach; ?></div>
    </div>
  </div>
</div>
