<?php
/**
 * The enquiry page. Submissions become leads in the CRM.
 * @var array $errors @var array $values @var bool $sent
 */
$module = (string) ($values['module'] ?? '');
$service = (string) ($values['service'] ?? '');
partial('site/head', [
    'title' => 'Contact us | Automate Limited',
    'description' => 'Tell us about your Odoo, website, SEO, marketing or design project. Your message goes straight to our team.',
    'path' => 'contact/',
    'noindex' => $errors !== [],
    'jsonld' => [breadcrumb_jsonld(['Contact' => 'contact/'])],
]);
?>
<body data-page="contact">
<?php partial('site/header', ['active' => 'contact']); ?>
<main id="main">
  <section class="phero phero--plain phero--contact">
    <div class="wrap">
      <div class="phero__head rv">
        <p class="eyebrow">Contact</p>
        <h1><?= $errors ? 'Almost there.' : 'Tell us what\'s breaking.' ?></h1>
        <p class="phero__sub"><?= $errors ? 'Please check the highlighted fields and send it again.' : 'Send the messy version: the stalled implementation, the version you\'re stuck on, the report nobody trusts. We\'ll tell you what fixing it takes.' ?></p>
        <?php if ($module !== '' && !$errors): ?>
          <p class="asking"><svg class="ic" aria-hidden="true"><use href="#i-erp"/></svg><span>You're asking about <b>Odoo <?= e($module) ?></b>. Add anything that helps us understand your setup.</span></p>
        <?php elseif ($service !== '' && $service !== 'Not sure yet' && !$errors): ?>
          <p class="asking"><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>You're asking about <b><?= e($service) ?></b>.</span></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="sec sec--flush-top" id="enquiry">
    <div class="wrap enquiry">
      <div class="enquiry__form rv">
        <?php partial('site/contact-form', ['errors' => $errors, 'values' => $values, 'sent' => $sent]); ?>
      </div>
      <aside class="enquiry__side rv" style="--rd:.1s" aria-label="What happens next">
        <h2>What happens next</h2>
        <ol class="nextsteps">
          <li><b>Your message reaches our team</b><span>It goes straight into our CRM, so nothing gets lost in an inbox.</span></li>
          <li><b>We reply by email</b><span>With questions, or a time for a short scoping call.</span></li>
          <li><b>A written quote</b><span>Scope, price and dates on paper before you commit to anything.</span></li>
        </ol>
        <div class="enquiry__direct">
          <p>Prefer email?</p>
          <a href="mailto:info@automateltd.com?subject=Enquiry%20from%20automateltd.com"><svg class="ic" aria-hidden="true"><use href="#i-mail"/></svg>info@automateltd.com</a>
        </div>
        <div class="enquiry__direct">
          <p>Visit us</p>
          <a href="<?= e(OFFICE['maps']) ?>" target="_blank" rel="noopener"><svg class="ic" aria-hidden="true"><use href="#i-map-pin"/></svg><span><?= implode('<br>', array_map('e', office_lines())) ?></span></a>
        </div>
        <div class="enquiry__links">
          <a href="<?= e(url('pricing/')) ?>"><svg class="ic" aria-hidden="true"><use href="#i-hand-coins"/></svg><span><b>How pricing works</b>Projects, retainers and blocks of hours</span></a>
          <a href="<?= e(url('careers/')) ?>"><svg class="ic" aria-hidden="true"><use href="#i-briefcase"/></svg><span><b>Looking for a job?</b>See open roles on the careers page</span></a>
        </div>
      </aside>
    </div>
  </section>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
