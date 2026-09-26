<?php
/**
 * Enquiry form. Posts to /contact/, which creates a lead in the CRM.
 * Works without JavaScript; with it, it submits in place.
 * @var bool|null $sent  @var array|null $errors  @var array|null $values
 */
$errors = $errors ?? [];
$values = $values ?? [];
$val = static fn (string $k): string => e((string) ($values[$k] ?? ''));
$services = app_installed() ? lead_services() : ['Odoo ERP', 'Website development', 'SEO', 'Digital marketing', 'Graphic design', 'Custom solutions'];
$err = static function (string $k) use ($errors): string {
    return isset($errors[$k]) ? '<p class="fld__err" id="err-' . e($k) . '">' . e($errors[$k]) . '</p>' : '';
};
$cls = static fn (string $k): string => isset($errors[$k]) ? 'fld has-error' : 'fld';
$described = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . e($k) . '"' : '';
?>
<form class="form" id="contactForm" method="post" action="<?= e(url('contact/')) ?>" data-ajax novalidate>
  <input type="hidden" name="_t" value="<?= e(form_token()) ?>">
  <input type="hidden" name="utm_source" value="">
  <input type="hidden" name="utm_medium" value="">
  <input type="hidden" name="utm_campaign" value="">
  <input type="hidden" name="landing" value="">
  <input type="hidden" name="referrer" value="">
  <div class="hp" aria-hidden="true"><label for="cf-website">Leave this empty</label><input id="cf-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>

  <div class="form__row">
    <div class="<?= $cls('name') ?>" data-field="name">
      <label for="cf-name">Your name</label>
      <input class="inp" id="cf-name" name="name" type="text" autocomplete="name" required maxlength="120" value="<?= $val('name') ?>"<?= $described('name') ?>>
      <?= $err('name') ?>
    </div>
    <div class="<?= $cls('email') ?>" data-field="email">
      <label for="cf-email">Work email</label>
      <input class="inp" id="cf-email" name="email" type="email" autocomplete="email" required maxlength="190" value="<?= $val('email') ?>"<?= $described('email') ?>>
      <?= $err('email') ?>
    </div>
  </div>
  <div class="form__row">
    <div class="fld" data-field="company">
      <label for="cf-company">Company <span class="opt">(optional)</span></label>
      <input class="inp" id="cf-company" name="company" type="text" autocomplete="organization" maxlength="120" value="<?= $val('company') ?>">
    </div>
    <div class="fld" data-field="phone">
      <label for="cf-phone">Phone <span class="opt">(optional)</span></label>
      <input class="inp" id="cf-phone" name="phone" type="tel" autocomplete="tel" maxlength="40" value="<?= $val('phone') ?>">
    </div>
  </div>
  <div class="form__row">
    <div class="fld" data-field="service">
      <label for="cf-service">What do you need help with?</label>
      <select class="inp" id="cf-service" name="service" data-service-select>
        <?php foreach ($services as $s): ?>
          <option<?= selected($values['service'] ?? '', $s) ?>><?= e($s) ?></option>
        <?php endforeach; ?>
        <option value="Not sure yet"<?= selected($values['service'] ?? '', 'Not sure yet') ?>>Not sure yet</option>
      </select>
    </div>
    <div class="fld" data-field="module" data-module-field>
      <label for="cf-module">Odoo module <span class="opt">(optional)</span></label>
      <select class="inp" id="cf-module" name="module">
        <option value="">Several, or not sure</option>
        <?php foreach (site_modules() as $m): ?>
          <option<?= selected($values['module'] ?? '', $m['name']) ?>><?= e($m['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="<?= $cls('message') ?>" data-field="message">
    <label for="cf-message">What's going on?</label>
    <textarea class="inp" id="cf-message" name="message" rows="5" required maxlength="5000" placeholder="The version you're on, what's stuck, any deadline"<?= $described('message') ?>><?= $val('message') ?></textarea>
    <?= $err('message') ?>
  </div>
  <div class="status<?= !empty($sent) ? ' is-ok' : (isset($errors['form']) ? ' is-bad' : '') ?>" role="status" aria-live="polite"><?php
    if (!empty($sent)) {
        echo 'Thanks, your message is with our team. We\'ll reply by email.';
    } elseif (isset($errors['form'])) {
        echo e($errors['form']);
    }
  ?></div>
  <div class="form__foot">
    <button class="btn btn--primary" type="submit" data-busy="Sending">Send message</button>
    <p class="form__note">We use these details only to reply to you. See our <a href="<?= e(url('privacy/')) ?>">privacy notice</a>.</p>
  </div>
</form>
