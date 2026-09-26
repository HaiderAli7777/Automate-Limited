<?php
/* Pricing: the three engagement models and how quotes work. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$quoteFaq = [
    ['Why is there no price list?', 'Two businesses asking for "Odoo with inventory" can need very different amounts of work. A scoping call first means the quote matches your business, not an average.'],
    ['What does a quote include?', 'The scope in plain language, the price, what is and isn\'t included, the go-live or delivery date, and how changes are handled.'],
    ['Can we start small?', 'Yes. Many clients start with a block of hours for one report or fix, and only move to a project or retainer once they\'ve seen how we work.'],
    ['Do quotes change once the work starts?', 'Only if the scope changes. When it does, you get the new price and date in writing before any extra work begins.'],
];
partial('site/head', [
    'title' => 'Pricing | Automate Limited',
    'description' => 'Fixed-scope projects, monthly retainers or blocks of hours. Every quote follows a scoping call, arrives in writing and holds unless the scope changes.',
    'path' => 'pricing/',
    'jsonld' => [breadcrumb_jsonld(['Pricing' => 'pricing/']), faq_jsonld($quoteFaq)],
]);
?>
<body data-page="pricing">
<?php partial('site/header', ['active' => 'pricing']); ?>
<main id="main">
  <section class="phero phero--plain">
    <div class="wrap">
      <div class="phero__head rv">
        <p class="eyebrow">Pricing</p>
        <h1>Quoted in writing, after we understand the work.</h1>
        <p class="phero__sub">A fixed-scope project, a monthly retainer or blocks of hours. Whichever fits, the price is agreed on paper before any work starts.</p>
      </div>
    </div>
  </section>

  <?php partial('site/sec-plans', ['plain' => true, 'class' => 'sec--flush-top']); ?>

  <section class="sec band">
    <div class="wrap">
      <div class="sec-head rv"><h2>How a quote comes together.</h2></div>
      <ol class="hsteps rv" style="--rd:.1s">
        <li class="hstep"><h3>You get in touch</h3><p>Send a short message about what you need. The form takes a minute.</p></li>
        <li class="hstep"><h3>Scoping call</h3><p>We ask about your processes, systems and deadlines, and say what we'd recommend.</p></li>
        <li class="hstep"><h3>Written quote</h3><p>Scope, price, dates and what's excluded, all on paper before you commit.</p></li>
        <li class="hstep"><h3>Work begins</h3><p>Once you sign, the work starts on the agreed date with a named consultant.</p></li>
      </ol>
    </div>
  </section>

  <section class="sec">
    <div class="wrap faq-grid">
      <div class="sec-head rv">
        <h2>About quotes.</h2>
        <p class="faq-more"><a href="<?= e(url('faq/')) ?>">All questions</a></p>
      </div>
      <?php partial('site/faq-list', ['items' => $quoteFaq, 'openFirst' => true]); ?>
    </div>
  </section>

  <?php partial('site/cta-band', ['title' => 'Get a quote for your project.', 'text' => 'Tell us what you need. We\'ll set up a scoping call and send the quote in writing.']); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
