<?php /** @var array|null $contact */ $cur = (string) setting('default_currency', 'PKR'); ?>
<a class="crumb" href="<?= e(admin_url('leads')) ?>"><?= icon('arrow-left') ?>Leads</a>
<div class="phead"><div><h1>New lead</h1><p class="phead__sub">For enquiries that came by phone, email, WhatsApp or referral.</p></div></div>
<form method="post" action="<?= e(admin_url('leads/new')) ?>">
  <?= csrf_field() ?>
  <div class="split">
    <div class="panel"><div class="panel__body">
      <fieldset class="fieldset">
        <legend>Who</legend>
        <?php if ($contact): ?>
          <input type="hidden" name="contact_id" value="<?= (int) $contact['id'] ?>">
          <div class="person"><?= avatar($contact['name']) ?><span><strong><?= e($contact['name']) ?></strong><span class="t-sub muted small" style="display:block"><?= e(implode(' · ', array_filter([$contact['email'], $contact['company']]))) ?></span></span></div>
        <?php else: ?>
          <div class="grid-2">
            <?= fi('contact_name', 'Name', '', ['required' => true]) ?>
            <?= fi('contact_company', 'Company', '', ['optional' => true]) ?>
            <?= fi('contact_email', 'Email', '', ['type' => 'email', 'optional' => true, 'help' => 'If this email already belongs to a contact, the lead joins them.']) ?>
            <?= fi('contact_phone', 'Phone or WhatsApp', '', ['type' => 'tel', 'optional' => true]) ?>
          </div>
        <?php endif; ?>
      </fieldset>
      <fieldset class="fieldset">
        <legend>What</legend>
        <?= fi('title', 'Title', '', ['required' => true, 'placeholder' => 'Odoo inventory and POS for a 3-branch retailer']) ?>
        <div class="grid-2">
          <?= fs('service', 'Service', array_combine(array_merge(lead_services(), ['Not sure yet']), array_merge(lead_services(), ['Not sure yet']))) ?>
          <?= fs('source', 'Source', LEAD_SOURCES, 'phone') ?>
        </div>
        <?= fi('topic', 'Module or topic', '', ['optional' => true, 'placeholder' => 'For example Inventory, or Shopify store', 'help' => 'Handy for Odoo enquiries: which module they asked about.']) ?>
        <?= ft('message', 'What they asked for', '', ['optional' => true, 'attrs' => ['rows' => 4]]) ?>
      </fieldset>
    </div></div>
    <div class="stack">
      <div class="panel"><div class="panel__body stack-sm">
        <?= fs('stage_id', 'Stage', array_map(static fn ($s) => $s['name'], lead_stages())) ?>
        <div class="grid-2 grid-tight">
          <?= fi('value', 'Estimated value', '', ['optional' => true, 'attrs' => ['inputmode' => 'decimal']]) ?>
          <?= fs('currency', 'Currency', array_combine(currencies(), currencies()), $cur) ?>
        </div>
        <?= fs('priority', 'Priority', LEAD_PRIORITIES, 'normal') ?>
        <?= fs('owner_id', 'Owner', user_options(auth_id(), 'Unassigned', 'crm.manage')) ?>
        <?= fi('next_follow_up', 'Next follow-up', '', ['type' => 'datetime-local', 'optional' => true]) ?>
        <?= fi('expected_close', 'Expected close', '', ['type' => 'date', 'optional' => true]) ?>
      </div></div>
      <button class="btn btn--primary btn--block" type="submit">Add lead</button>
    </div>
  </div>
</form>
