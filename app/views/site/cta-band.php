<?php
/**
 * Closing call to action on inner pages.
 * @var string|null $title @var string|null $text @var string|null $service  pre-selects the enquiry form
 */
$title = $title ?? 'Tell us what\'s breaking.';
$text = $text ?? 'Send the messy version. A scoping call comes before any quote, and the quote arrives in writing.';
?>
<section class="sec sec--tight" aria-label="Get in touch">
  <div class="wrap">
    <div class="cta cta--band rv">
      <svg class="cta__mark" viewBox="0 0 402 342" aria-hidden="true"><use href="#mk"/></svg>
      <div class="cta__band">
        <div>
          <h2><?= e($title) ?></h2>
          <p><?= e($text) ?></p>
        </div>
        <div class="cta__actions">
          <a class="btn btn--light" href="<?= e(enquiry_url((string) ($service ?? ''))) ?>">Start an enquiry</a>
          <a class="cta__alt" href="mailto:info@automateltd.com"><svg class="ic" aria-hidden="true"><use href="#i-mail"/></svg>info@automateltd.com</a>
        </div>
      </div>
    </div>
  </div>
</section>
