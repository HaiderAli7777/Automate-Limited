<?php /* The four-step process with a scroll-linked progress line (site.js). @var string|null $class */ ?>
  <section class="sec<?= isset($class) ? ' ' . e($class) : '' ?>" id="how">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Four steps, with the <span class="nobr">go-live</span> date agreed up front.</h2>
      </div>
      <div class="proc" id="proc">
        <div class="proc__track" aria-hidden="true"><span class="proc__fill"></span></div>
        <ol class="proc__list">
          <li class="step">
            <span class="step__dot" aria-hidden="true">1</span>
            <h3>Scope</h3>
            <p>Two weeks with your finance, stores and sales people. We write down what happens today, workarounds included, and you sign it off before anything is configured.</p>
          </li>
          <li class="step">
            <span class="step__dot" aria-hidden="true">2</span>
            <h3>Configure</h3>
            <p>Standard Odoo first. Customization is quoted separately, and only for what the default genuinely can't do.</p>
          </li>
          <li class="step">
            <span class="step__dot" aria-hidden="true">3</span>
            <h3>Migrate</h3>
            <p>Master data and opening balances move under a reconciliation you can check. Both systems run side by side until the numbers match.</p>
          </li>
          <li class="step">
            <span class="step__dot" aria-hidden="true">4</span>
            <h3>Support</h3>
            <p>A named consultant, a response time agreed in writing, and a quarterly review of what your team still does by hand.</p>
          </li>
        </ol>
      </div>
    </div>
  </section>
