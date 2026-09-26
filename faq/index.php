<?php
/* Frequently asked questions, grouped, with FAQPage structured data. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$groups = site_faq();
$all = [];
foreach ($groups as $items) {
    foreach ($items as [$q, $a]) {
        $all[] = [$q, $a];
    }
}
partial('site/head', [
    'title' => 'FAQ | Automate Limited',
    'description' => 'Answers about Odoo implementations and migrations, websites, SEO, design, quotes and support after go-live.',
    'path' => 'faq/',
    'jsonld' => [breadcrumb_jsonld(['FAQ' => 'faq/']), faq_jsonld($all)],
]);
?>
<body data-page="faq">
<?php partial('site/header', ['active' => 'faq']); ?>
<main id="main">
  <section class="phero phero--plain">
    <div class="wrap">
      <div class="phero__head rv">
        <p class="eyebrow">FAQ</p>
        <h1>Questions before the first invoice.</h1>
        <p class="phero__sub">Something else? Email <a class="link" href="mailto:info@automateltd.com">info@automateltd.com</a> or <a class="link" href="<?= e(url('contact/')) ?>">send us a message</a>.</p>
      </div>
      <nav class="jump rv" aria-label="Topics">
        <?php foreach (array_keys($groups) as $g): ?><a href="#<?= e(slugify($g)) ?>"><?= e($g) ?></a><?php endforeach; ?>
      </nav>
    </div>
  </section>

  <?php $i = 0; foreach ($groups as $g => $items): ?>
  <section class="sec<?= $i === 0 ? ' sec--flush-top' : ($i % 2 ? ' band' : '') ?><?php $i++; ?>" id="<?= e(slugify($g)) ?>">
    <div class="wrap faq-grid">
      <div class="sec-head rv"><h2><?= e($g) ?></h2></div>
      <?php partial('site/faq-list', ['items' => array_map(static fn ($x) => [$x[0], $x[1]], $items), 'openFirst' => true]); ?>
    </div>
  </section>
  <?php endforeach; ?>

  <?php partial('site/cta-band', ['title' => 'Still have a question?', 'text' => 'Ask us directly. A real person on our team reads every message.']); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
