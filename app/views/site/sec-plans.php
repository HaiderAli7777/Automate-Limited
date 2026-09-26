<?php /* The three engagement models. @var bool|null $plain  no band background  @var string|null $class */ ?>
  <section class="sec<?= empty($plain) ? ' band' : '' ?><?= isset($class) ? ' ' . e($class) : '' ?>" id="engage">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Three ways to work with us.</h2>
        <p>There's no menu price. Every quote follows a scoping call, arrives in writing and holds unless the scope changes.</p>
      </div>
      <div class="plans rv" style="--rd:.1s">
        <article class="plan"><p class="plan__tag" aria-hidden="true"></p><h3>Fixed-scope project</h3><p class="plan__for">A go-live with a real date against it.</p><ul><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Two weeks of scoping before any configuration</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>One price, quoted against the signed scope</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Build, data migration, training and handover</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Both systems run until the numbers agree</span></li></ul><p class="plan__bill">Fixed price, quoted after scoping</p></article>
        <article class="plan plan--lead"><p class="plan__tag">Where most clients start</p><h3>Monthly retainer</h3><p class="plan__for">Running Odoo properly once you're live.</p><ul><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A named consultant, not a ticket queue</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A response time agreed in writing</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Small changes and new reports each month</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A quarterly review of what's still done by hand</span></li></ul><p class="plan__bill">The same amount every month</p></article>
        <article class="plan"><p class="plan__tag" aria-hidden="true"></p><h3>Blocks of hours</h3><p class="plan__for">A fix, a report or a question.</p><ul><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Pre-paid hours, used as you need them</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Right for one report or a stuck migration</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>No minimum term and no notice period</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Becomes a retainer only if you want it to</span></li></ul><p class="plan__bill">Hourly, against a block you top up</p></article>
      </div>
    </div>
  </section>
