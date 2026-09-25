<?php
/* Privacy notice for the contact and job application forms. Review the wording with your advisor before relying on it. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$email = app_installed() ? (string) setting('privacy_email', 'info@automateltd.com') : 'info@automateltd.com';
if (!valid_email($email)) {
    $email = 'info@automateltd.com';
}
partial('site/head', ['title' => 'Privacy notice | Automate Limited', 'description' => 'How Automate Limited handles the details you send through this website.', 'path' => 'privacy/']);
?>
<body data-page="privacy">
<?php partial('site/header'); ?>
<main id="main" class="doc">
  <div class="wrap">
    <h1>Privacy notice.</h1>
    <p class="doc__meta">How we handle what you send us through this website.</p>
    <div class="prose">
      <section>
        <h2>What we collect</h2>
        <p>When you send an enquiry, we receive your name, email address and message, and your company and phone number if you add them. We also note which page you came from and any campaign tags in the link, so we know how people find us.</p>
        <p>When you apply for a role, we receive the details on the application form, your CV and any answers or cover letter you add.</p>
      </section>
      <section>
        <h2>Why we use it</h2>
        <ul>
          <li>To reply to your enquiry and, if you want to work with us, to prepare a proposal.</li>
          <li>To review your application, contact you about it and arrange interviews.</li>
        </ul>
        <p>We don't sell your details or add you to marketing lists.</p>
      </section>
      <section>
        <h2>Who can see it</h2>
        <p>Only our team, through a password-protected system. CVs are stored privately and are never published on the website. We send email through our email provider.</p>
      </section>
      <section>
        <h2>How long we keep it</h2>
        <p>Enquiries are kept while we're in touch and for a reasonable time after, as a record of the conversation. Applications are kept for up to twelve months so we can consider you for other roles, unless you ask us to delete them sooner.</p>
      </section>
      <section>
        <h2>Your choices</h2>
        <p>You can ask to see, correct or delete the details we hold about you at any time. Email <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> and we'll take care of it.</p>
      </section>
    </div>
  </div>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
