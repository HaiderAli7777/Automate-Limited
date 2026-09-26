<?php
/** @var string $tab */
partial('admin/settings/_tabs', ['tab' => $tab]);
$s = static fn (string $k, string $d = ''): string => (string) setting($k, $d);
$zones = [];
foreach (['Asia/Karachi', 'Asia/Dubai', 'Asia/Riyadh', 'Asia/Qatar', 'Europe/London', 'UTC'] as $z) {
    $zones[$z] = str_replace('_', ' ', $z);
}
if (!isset($zones[$s('timezone')]) && $s('timezone') !== '') {
    $zones[$s('timezone')] = $s('timezone');
}
$hasPw = $s('smtp_password') !== '';
?>
<div class="split">
<form method="post" action="<?= e(admin_url('settings')) ?>" class="panel">
  <?= csrf_field() ?>
  <div class="panel__body">
    <fieldset class="fieldset">
      <legend>Company</legend>
      <div class="grid-2">
        <?= fi('company_name', 'Company name', $s('company_name', 'Automate Limited'), ['required' => true, 'help' => 'Used in emails to candidates and customers.']) ?>
        <?= fs('timezone', 'Time zone', $zones, $s('timezone', 'Asia/Karachi'), ['help' => 'Interview times and dashboards use this.']) ?>
        <?= fs('default_currency', 'Default currency', array_combine(currencies(), currencies()), $s('default_currency', 'PKR'), ['help' => 'For deal values and salaries.']) ?>
        <?= fi('upload_max_mb', 'Largest CV upload (MB)', $s('upload_max_mb', '8'), ['type' => 'number', 'attrs' => ['min' => 1, 'max' => 50]]) ?>
      </div>
      <?= ft('lead_services', 'Services offered on the enquiry form', $s('lead_services'), ['help' => 'One per line. Visitors choose from these, and leads are grouped by them.']) ?>
    </fieldset>

    <fieldset class="fieldset">
      <legend>Who gets notified</legend>
      <div class="grid-2">
        <?= fi('hr_email', 'New applications go to', $s('hr_email'), ['help' => 'Separate several addresses with commas. The hiring manager on a job is added automatically.']) ?>
        <?= fi('sales_email', 'New enquiries go to', $s('sales_email'), ['help' => 'Separate several addresses with commas.']) ?>
        <?= fs('lead_default_owner', 'Assign new website leads to', user_options((int) $s('lead_default_owner', '0'), 'Nobody (assign by hand)', 'crm.manage'), $s('lead_default_owner')) ?>
        <?= fi('privacy_email', 'Privacy requests go to', $s('privacy_email'), ['type' => 'email', 'help' => 'Shown on the privacy notice.']) ?>
      </div>
      <?= fc('notify_new_application', 'Email the hiring team about each new application', $s('notify_new_application', '1') === '1') ?>
      <?= fc('autoreply_application', 'Send candidates an acknowledgement when they apply', $s('autoreply_application', '1') === '1', 'Uses the "Application received" template.') ?>
      <?= fc('notify_new_inquiry', 'Email the sales team about each new enquiry', $s('notify_new_inquiry', '1') === '1') ?>
      <?= fc('autoreply_inquiry', 'Send an acknowledgement to people who use the contact form', $s('autoreply_inquiry', '1') === '1', 'Uses the "Enquiry received" template.') ?>
    </fieldset>

    <fieldset class="fieldset" id="email">
      <legend>Sending email</legend>
      <p class="help">On Hostinger, create the mailbox in hPanel under Emails, then use its address and password here with SMTP. PHP mail() works without setup but is more likely to land in spam.</p>
      <div class="grid-2">
        <?= fs('mail_transport', 'Send with', ['smtp' => 'SMTP (recommended)', 'mail' => 'PHP mail()'], $s('mail_transport', 'mail')) ?>
        <div></div>
        <?= fi('mail_from_email', 'From address', $s('mail_from_email'), ['type' => 'email', 'help' => 'Should be the SMTP mailbox, e.g. info@automateltd.com.']) ?>
        <?= fi('mail_from_name', 'From name', $s('mail_from_name', company_name())) ?>
        <?= fi('smtp_host', 'SMTP server', $s('smtp_host', 'smtp.hostinger.com')) ?>
        <div class="grid-2 grid-tight">
          <?= fi('smtp_port', 'Port', $s('smtp_port', '465'), ['type' => 'number']) ?>
          <?= fs('smtp_encryption', 'Security', ['ssl' => 'SSL (465)', 'tls' => 'STARTTLS (587)', 'none' => 'None'], $s('smtp_encryption', 'ssl')) ?>
        </div>
        <?= fi('smtp_username', 'SMTP username', $s('smtp_username'), ['help' => 'Usually the full mailbox address.', 'attrs' => ['autocomplete' => 'off']]) ?>
        <?= fi('smtp_password', 'SMTP password', '', ['type' => 'password', 'placeholder' => $hasPw ? 'Saved. Type to replace it.' : '', 'help' => 'Stored encrypted.', 'attrs' => ['autocomplete' => 'new-password']]) ?>
      </div>
      <?php if ($hasPw): ?><?= fc('smtp_password_clear', 'Remove the saved SMTP password', false) ?><?php endif; ?>
    </fieldset>
    <div class="form-actions"><button class="btn btn--primary" type="submit">Save settings</button></div>
  </div>
</form>
<div class="stack">
  <div class="panel">
    <div class="panel__head"><h2>Test your email</h2></div>
    <div class="panel__body stack-sm">
      <p class="small muted">Save first, then send yourself a test to confirm notifications will arrive.</p>
      <form method="post" action="<?= e(admin_url('settings/test-email')) ?>"><?= csrf_field() ?><button class="btn btn--quiet" type="submit"><?= icon('envelope-simple') ?>Send a test to <?= e(auth_user()['email']) ?></button></form>
    </div>
  </div>
  <div class="panel">
    <div class="panel__head"><h2>Where things live</h2></div>
    <div class="panel__body">
      <dl class="dl dl--stack small">
        <dt>Careers page</dt><dd><a class="link" href="<?= e(url('careers/')) ?>" target="_blank" rel="noopener"><?= e(abs_url('careers/')) ?></a></dd>
        <dt>Team sign-in</dt><dd><?= e(abs_url('admin/login')) ?></dd>
        <dt>Private files and config</dt><dd><code><?= e(storage_path()) ?></code></dd>
        <dt>Version</dt><dd><?= e(APP_VERSION) ?>, database schema <?= e((string) setting('schema_version', '1')) ?></dd>
      </dl>
    </div>
  </div>
</div>
</div>
