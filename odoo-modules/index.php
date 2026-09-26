<?php
/* Odoo modules. Every tile opens the enquiry page with that module filled in. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$modules = site_modules();
partial('site/head', [
    'title' => 'Odoo modules we implement | Automate Limited',
    'description' => 'Accounting, Inventory, Sales, Purchase, Manufacturing, Point of Sale, HR and Payroll, eCommerce and more. Pick a module and ask us about it.',
    'path' => 'odoo-modules/',
    'jsonld' => [breadcrumb_jsonld(['Services' => 'services/', 'Odoo modules' => 'odoo-modules/'])],
]);
?>
<body data-page="modules">
<?php partial('site/header', ['active' => 'modules']); ?>
<main id="main">
  <section class="phero phero--plain">
    <div class="wrap">
      <div class="phero__head rv">
        <nav class="crumbs" aria-label="Breadcrumb">
          <a href="<?= e(url('services/odoo-erp')) ?>">Odoo ERP</a>
          <svg class="ic" aria-hidden="true"><use href="#i-caret-right"/></svg>
          <span aria-current="page">Modules</span>
        </nav>
        <h1>Odoo, module by module.</h1>
        <p class="phero__sub">Each module reads from the same records as the others, so nobody copies figures from one screen into another. Choose a module to ask about it.</p>
      </div>
      <ul class="facts__list facts__list--inline rv" style="--rd:.1s">
        <li><svg class="ic" aria-hidden="true"><use href="#i-erp"/></svg><span>Odoo 17, 18 and 19</span></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-receipt"/></svg><span>Tax for UAE, KSA and Pakistan</span></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-globe"/></svg><span>English and Arabic</span></li>
      </ul>
    </div>
  </section>

  <section class="sec sec--flush-top" id="modules">
    <div class="wrap">
      <?php partial('site/mods', ['items' => $modules, 'full' => true]); ?>
      <div class="notsure rv">
        <div>
          <h2>Not sure which modules you need?</h2>
          <p>Tell us how the business runs today. We'll map it to Odoo during scoping and tell you which modules cover it, including which ones need an Enterprise licence.</p>
        </div>
        <a class="btn btn--primary" href="<?= e(enquiry_url('Odoo ERP')) ?>">Ask about Odoo</a>
      </div>
    </div>
  </section>

  <?php partial('site/sec-process', ['class' => 'band']); ?>
  <?php partial('site/cta-band', ['title' => 'Planning a move to Odoo?', 'service' => 'Odoo ERP']); ?>
</main>
<?php partial('site/footer'); ?>
</body>
</html>
