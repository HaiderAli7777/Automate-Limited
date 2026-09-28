<?php
/* Homepage. Static marketing content; the contact form posts to /contact/ and lands in the CRM. */
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$sent = input('sent') === '1';
partial('site/head', [
    'title' => 'Automate Limited | Odoo ERP, websites, SEO and design',
    'description' => 'Odoo ERP implementation and support, website development, SEO, digital marketing and graphic design for businesses in Pakistan, the Gulf and beyond.',
    'path' => '',
    'jsonld' => json_decode(<<<'JSON'
[{"@context": "https://schema.org", "@type": "ProfessionalService", "name": "Automate Limited", "url": "https://automateltd.com/", "description": "Odoo ERP implementation and support, website development, SEO, digital marketing and graphic design for businesses in Pakistan, the Gulf and beyond.", "logo": "https://automateltd.com/logo-automate.svg", "image": "https://automateltd.com/og-automate.png", "email": "info@automateltd.com", "address": {"@type": "PostalAddress", "streetAddress": "Foundry Co-working Space, Chohan Tower, 16 Jail Road, Shadman II", "addressLocality": "Lahore", "addressRegion": "Punjab", "postalCode": "54000", "addressCountry": "PK"}, "areaServed": [{"@type": "Country", "name": "Pakistan"}, {"@type": "Country", "name": "United Arab Emirates"}, {"@type": "Country", "name": "Saudi Arabia"}], "knowsAbout": ["Odoo", "ERP implementation", "Odoo migration", "QWeb reports", "eCommerce", "Search engine optimisation", "Digital marketing", "Brand identity"], "hasOfferCatalog": {"@type": "OfferCatalog", "name": "Services", "itemListElement": [{"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Odoo ERP"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Website development"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "SEO"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Digital marketing"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Graphic design"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Custom solutions"}}]}}, {"@context": "https://schema.org", "@type": "WebSite", "name": "Automate Limited", "url": "https://automateltd.com/"}]
JSON, true),
]);
?>
<body data-page="home">
<?php partial('site/header', ['home' => true]); ?>

<main id="main">

  <section class="stage" id="top" aria-label="Introduction">
    <div class="stage__pin">
      <div class="wrap hero">
        <div class="hero__copy" id="heroCopy">
          <h1>Every module, one system.</h1>
          <p class="hero__sub">Odoo ERP, websites, SEO, digital marketing and design for businesses in Pakistan, the Gulf and beyond.</p>
          <div class="hero__cta">
            <a class="btn btn--primary" href="<?= e(url('contact/')) ?>">Get in touch</a>
            <a class="btn btn--quiet" href="#why">See how it works</a>
          </div>
        </div>
        <div class="orbit" aria-hidden="true">
          <svg class="orbit__lines" id="orbitLines" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M26 13 L50 50"/><path d="M68 10 L50 50"/><path d="M88 34 L50 50"/><path d="M82 72 L50 50"/><path d="M56 92 L50 50"/><path d="M20 84 L50 50"/><path d="M10 56 L50 50"/><path d="M15 30 L50 50"/><path class="pulse" pathLength="100" style="--d:0.00s" d="M26 13 L50 50"/><path class="pulse" pathLength="100" style="--d:-0.47s" d="M68 10 L50 50"/><path class="pulse" pathLength="100" style="--d:-0.94s" d="M88 34 L50 50"/><path class="pulse" pathLength="100" style="--d:-1.41s" d="M82 72 L50 50"/><path class="pulse" pathLength="100" style="--d:-1.88s" d="M56 92 L50 50"/><path class="pulse" pathLength="100" style="--d:-2.35s" d="M20 84 L50 50"/><path class="pulse" pathLength="100" style="--d:-2.82s" d="M10 56 L50 50"/><path class="pulse" pathLength="100" style="--d:-3.29s" d="M15 30 L50 50"/></svg>
          <div class="chip" style="--x:26;--y:13;--mx:24;--my:12;--t:13s;--dl:0.0s;--dx:4px;--dy:-5px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-accounting"/></svg>Accounting</div></div>
          <div class="chip" style="--x:68;--y:10;--mx:76;--my:16;--t:14s;--dl:7.1s;--dx:-5px;--dy:-6px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-sales"/></svg>Sales</div></div>
          <div class="chip" style="--x:88;--y:34;--mx:84;--my:72;--t:15s;--dl:5.2s;--dx:6px;--dy:-7px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-inventory"/></svg>Inventory</div></div>
          <div class="chip is-extra" style="--x:82;--y:72;--mx:82;--my:72;--t:16s;--dl:3.3s;--dx:-4px;--dy:-8px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-manufacturing"/></svg>Manufacturing</div></div>
          <div class="chip" style="--x:56;--y:92;--mx:50;--my:92;--t:17s;--dl:1.4s;--dx:5px;--dy:-5px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-pos"/></svg>Point of Sale</div></div>
          <div class="chip is-extra" style="--x:20;--y:84;--mx:20;--my:84;--t:18s;--dl:8.5s;--dx:-6px;--dy:-6px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-ecommerce"/></svg>eCommerce</div></div>
          <div class="chip" style="--x:10;--y:56;--mx:16;--my:64;--t:13s;--dl:6.6s;--dx:4px;--dy:-7px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-hr"/></svg>Payroll</div></div>
          <div class="chip is-extra" style="--x:15;--y:30;--mx:15;--my:30;--t:14s;--dl:4.7s;--dx:-5px;--dy:-8px"><div class="chip__in"><svg class="ic" aria-hidden="true"><use href="#i-purchase"/></svg>Purchase</div></div>
          <div class="hub" id="hub"><svg viewBox="0 0 402 342" aria-hidden="true"><use href="#mk"/></svg><span>One system</span></div>
        </div>
      </div>
      <div class="panel" id="panel">
        <div class="panel__media" id="panelMedia">
          <picture><source media="(max-width: 899px)" srcset="https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=focalpoint&amp;fp-x=.74&amp;fp-y=.5&amp;q=72&amp;sat=-12&amp;con=5&amp;w=600&amp;h=750 600w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=focalpoint&amp;fp-x=.74&amp;fp-y=.5&amp;q=72&amp;sat=-12&amp;con=5&amp;w=900&amp;h=1125 900w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=focalpoint&amp;fp-x=.74&amp;fp-y=.5&amp;q=72&amp;sat=-12&amp;con=5&amp;w=1200&amp;h=1500 1200w" sizes="100vw" width="900" height="1125"><img src="https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=2000&amp;h=1125&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=675&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=2000&amp;h=1125&amp;q=72&amp;sat=-12&amp;con=5 2000w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=3200&amp;h=1800&amp;q=72&amp;sat=-12&amp;con=5 3200w" sizes="100vw" width="2000" height="1125" alt="A businessman working on a laptop on a balcony above the city" loading="eager" fetchpriority="low" decoding="async"></picture>
        </div>
        <div class="panel__copy" id="panelCopy">
          <div class="wrap">
            <h2>Know where the business stands without asking anyone.</h2>
            <p>Stock, sales, invoices and payroll in one system, updated as it happens and readable from your phone.</p>
            <a class="btn btn--light" href="<?= e(url('contact/')) ?>">Get in touch</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="facts" aria-label="At a glance">
    <div class="wrap">
      <ul class="facts__list">
        <li><svg class="ic" aria-hidden="true"><use href="#i-erp"/></svg><span>Odoo 17, 18 and 19</span></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-globe"/></svg><span>English and Arabic, right-to-left built properly</span></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-receipt"/></svg><span>Tax set up for UAE, KSA and Pakistan</span></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-hr"/></svg><span>A named consultant, not a ticket queue</span></li>
      </ul>
    </div>
  </section>

  <?php partial('site/platforms'); ?>

  <section class="sec why" id="why">
    <div class="wrap why__grid">
      <div class="rv">
        <h2>Six places, six versions of the truth.</h2>
        <p class="why__lede">Sales on WhatsApp, stock in Excel, the accounts in another app, and one person spending the week making them agree.</p>
        <div class="seg" role="group" aria-label="Compare the two setups">
          <button type="button" data-state="today" aria-pressed="true">Today</button>
          <button type="button" data-state="after" aria-pressed="false">With one system</button>
        </div>
        <p class="why__cap" id="whyCap" aria-live="polite">Every area keeps its own record, so someone reconciles them by hand, usually at month end.</p>
      </div>
      <figure class="morph-card rv" style="--rd:.12s">
        <svg class="morph" id="morph" data-state="today" viewBox="0 0 560 420" role="img" aria-labelledby="morphTitle"><title id="morphTitle">Six areas of a business, first kept in separate tools, then connected to one system</title><g class="m-tangle"><path d="M96 72 Q206 93 312 58"/><path d="M96 72 Q200 121 268 214"/><path d="M312 58 Q382 119 474 128"/><path d="M312 58 Q263 128 268 214"/><path d="M474 128 Q429 227 440 336"/><path d="M86 262 Q184 265 268 214"/><path d="M86 262 Q119 168 96 72"/><path d="M268 214 Q338 298 440 336"/><path d="M474 128 Q382 197 268 214"/><path d="M86 262 Q269 272 440 336"/></g><path class="m-spoke" pathLength="1" d="M280 210 L280 78"/><path class="m-spoke" pathLength="1" d="M280 210 L440 144"/><path class="m-spoke" pathLength="1" d="M280 210 L440 276"/><path class="m-spoke" pathLength="1" d="M280 210 L280 342"/><path class="m-spoke" pathLength="1" d="M280 210 L120 276"/><path class="m-spoke" pathLength="1" d="M280 210 L120 144"/><path class="m-flow" pathLength="1" style="animation-delay:0.00s" d="M280 78 L280 210"/><path class="m-flow" pathLength="1" style="animation-delay:-0.53s" d="M440 144 L280 210"/><path class="m-flow" pathLength="1" style="animation-delay:-1.06s" d="M440 276 L280 210"/><path class="m-flow" pathLength="1" style="animation-delay:-1.59s" d="M280 342 L280 210"/><path class="m-flow" pathLength="1" style="animation-delay:-2.12s" d="M120 276 L280 210"/><path class="m-flow" pathLength="1" style="animation-delay:-2.65s" d="M120 144 L280 210"/><g class="m-hub"><rect x="202" y="182" width="156" height="56" rx="14"/><use href="#mk" x="219" y="197" width="30" height="26" style="color:#042F65"/><text x="258" y="215.5">One system</text></g><g class="m-node" style="--tx:96px;--ty:72px;--tr:-3deg;--ax:280px;--ay:78px;--i:0"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">Sales</text><text class="m-sub" x="0" y="15" text-anchor="middle">on WhatsApp</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g><g class="m-node" style="--tx:312px;--ty:58px;--tr:2deg;--ax:440px;--ay:144px;--i:1"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">Stock</text><text class="m-sub" x="0" y="15" text-anchor="middle">in Excel</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g><g class="m-node" style="--tx:474px;--ty:128px;--tr:-2deg;--ax:440px;--ay:276px;--i:2"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">Accounts</text><text class="m-sub" x="0" y="15" text-anchor="middle">in another app</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g><g class="m-node" style="--tx:86px;--ty:262px;--tr:3deg;--ax:280px;--ay:342px;--i:3"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">Payroll</text><text class="m-sub" x="0" y="15" text-anchor="middle">on paper</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g><g class="m-node" style="--tx:268px;--ty:214px;--tr:-4deg;--ax:120px;--ay:276px;--i:4"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">Purchase</text><text class="m-sub" x="0" y="15" text-anchor="middle">over email</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g><g class="m-node" style="--tx:440px;--ty:336px;--tr:2deg;--ax:120px;--ay:144px;--i:5"><rect x="-64" y="-26" width="128" height="52" rx="12"/><text class="m-name" x="0" y="-3" text-anchor="middle">The shop</text><text class="m-sub" x="0" y="15" text-anchor="middle">in the till</text><text class="m-live" x="0" y="15" text-anchor="middle">Live</text></g></svg>
      </figure>
    </div>
  </section>

  <section class="sec" id="services">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>One team for the system and everything around it.</h2>
        <p>Most clients start with Odoo. The website, search rankings and brand usually come next, so we do those too.</p>
      </div>
      <div class="svx rv" style="--rd:.1s">
        <div class="svx__list" role="tablist" aria-label="Services">
          <button class="svx__tab" type="button" role="tab" id="tab-odoo" aria-controls="svc-odoo" aria-selected="true" tabindex="0"><svg class="ic" aria-hidden="true"><use href="#i-erp"/></svg><span class="svx__name">Odoo ERP</span><span class="svx__line">Implementation, custom modules, migration and support</span></button>
          <button class="svx__tab" type="button" role="tab" id="tab-web" aria-controls="svc-web" aria-selected="false" tabindex="-1"><svg class="ic" aria-hidden="true"><use href="#i-web"/></svg><span class="svx__name">Website development</span><span class="svx__line">Sites and storefronts your team can run</span></button>
          <button class="svx__tab" type="button" role="tab" id="tab-seo" aria-controls="svc-seo" aria-selected="false" tabindex="-1"><svg class="ic" aria-hidden="true"><use href="#i-seo"/></svg><span class="svx__name">SEO</span><span class="svx__line">Technical fixes and local search</span></button>
          <button class="svx__tab" type="button" role="tab" id="tab-marketing" aria-controls="svc-marketing" aria-selected="false" tabindex="-1"><svg class="ic" aria-hidden="true"><use href="#i-marketing"/></svg><span class="svx__name">Digital marketing</span><span class="svx__line">Paid campaigns measured on enquiries</span></button>
          <button class="svx__tab" type="button" role="tab" id="tab-design" aria-controls="svc-design" aria-selected="false" tabindex="-1"><svg class="ic" aria-hidden="true"><use href="#i-design"/></svg><span class="svx__name">Graphic design</span><span class="svx__line">Identity, print and packaging</span></button>
          <button class="svx__tab" type="button" role="tab" id="tab-custom" aria-controls="svc-custom" aria-selected="false" tabindex="-1"><svg class="ic" aria-hidden="true"><use href="#i-custom"/></svg><span class="svx__name">Custom solutions</span><span class="svx__line">Integrations and internal tools</span></button>
        </div>
        <div class="svx__panels">
          <div class="svx__panel is-active" role="tabpanel" id="svc-odoo" aria-labelledby="tab-odoo" tabindex="0">
            <div class="svx__art svx__art--loop"><svg class="fl-d" viewBox="0 0 640 400" role="img" aria-label="One sale moving through Odoo: it rings up at the counter, updates stock, posts an invoice to the ledger and moves the dashboard"><path class="fl-loop" d="M320 70 A220 130 0 0 1 540 200 A220 130 0 0 1 320 330 A220 130 0 0 1 100 200 A220 130 0 0 1 320 70 Z"/><path class="fl-pulse" pathLength="100" d="M320 70 A220 130 0 0 1 540 200 A220 130 0 0 1 320 330 A220 130 0 0 1 100 200 A220 130 0 0 1 320 70 Z"/><text class="fl-mid" x="320" y="198" text-anchor="middle">Entered once.</text><text class="fl-mid2" x="320" y="224" text-anchor="middle">Recorded everywhere it belongs.</text><g class="fl-node" transform="translate(320 70)" style="--at:0s"><rect x="-96" y="-32" width="192" height="64" rx="14"/><rect class="fl-ring" x="-96" y="-32" width="192" height="64" rx="14"/><use href="#i-pos" x="-80" y="-12" width="24" height="24"/><text class="fl-t" x="-46" y="-3">A sale rings up</text><text class="fl-s" x="-46" y="15">at the counter</text></g><g class="fl-node" transform="translate(540 200)" style="--at:1.2s"><rect x="-96" y="-32" width="192" height="64" rx="14"/><rect class="fl-ring" x="-96" y="-32" width="192" height="64" rx="14"/><use href="#i-inventory" x="-80" y="-12" width="24" height="24"/><text class="fl-t" x="-46" y="-3">Stock updates</text><text class="fl-s" x="-46" y="15">in the warehouse</text></g><g class="fl-node" transform="translate(320 330)" style="--at:2.4s"><rect x="-96" y="-32" width="192" height="64" rx="14"/><rect class="fl-ring" x="-96" y="-32" width="192" height="64" rx="14"/><use href="#i-receipt" x="-80" y="-12" width="24" height="24"/><text class="fl-t" x="-46" y="-3">Invoice posts</text><text class="fl-s" x="-46" y="15">to the ledger</text></g><g class="fl-node" transform="translate(100 200)" style="--at:3.6s"><rect x="-96" y="-32" width="192" height="64" rx="14"/><rect class="fl-ring" x="-96" y="-32" width="192" height="64" rx="14"/><use href="#i-chart" x="-80" y="-12" width="24" height="24"/><text class="fl-t" x="-46" y="-3">Dashboard moves</text><text class="fl-s" x="-46" y="15">on your phone</text></g></svg><svg class="fl-m" viewBox="0 0 360 420" role="img" aria-label="One sale moving through Odoo: it rings up at the counter, updates stock, posts an invoice and moves the dashboard"><path class="fl-loop" d="M180 62 L180 350"/><path class="fl-pulse fl-pulse--m" pathLength="100" d="M180 62 L180 350"/><g class="fl-node" transform="translate(180 62)" style="--at:0.0s"><rect x="-136" y="-32" width="272" height="64" rx="14"/><rect class="fl-ring" x="-136" y="-32" width="272" height="64" rx="14"/><use href="#i-pos" x="-116" y="-13" width="26" height="26"/><text class="fl-t fl-t--m" x="-76" y="-3">A sale rings up</text><text class="fl-s fl-s--m" x="-76" y="17">at the counter</text></g><g class="fl-node" transform="translate(180 158)" style="--at:1.2s"><rect x="-136" y="-32" width="272" height="64" rx="14"/><rect class="fl-ring" x="-136" y="-32" width="272" height="64" rx="14"/><use href="#i-inventory" x="-116" y="-13" width="26" height="26"/><text class="fl-t fl-t--m" x="-76" y="-3">Stock updates</text><text class="fl-s fl-s--m" x="-76" y="17">in the warehouse</text></g><g class="fl-node" transform="translate(180 254)" style="--at:2.4s"><rect x="-136" y="-32" width="272" height="64" rx="14"/><rect class="fl-ring" x="-136" y="-32" width="272" height="64" rx="14"/><use href="#i-receipt" x="-116" y="-13" width="26" height="26"/><text class="fl-t fl-t--m" x="-76" y="-3">Invoice posts</text><text class="fl-s fl-s--m" x="-76" y="17">to the ledger</text></g><g class="fl-node" transform="translate(180 350)" style="--at:3.5999999999999996s"><rect x="-136" y="-32" width="272" height="64" rx="14"/><rect class="fl-ring" x="-136" y="-32" width="272" height="64" rx="14"/><use href="#i-chart" x="-116" y="-13" width="26" height="26"/><text class="fl-t fl-t--m" x="-76" y="-3">Dashboard moves</text><text class="fl-s fl-s--m" x="-76" y="17">on your phone</text></g><text class="fl-mid2" x="180" y="408" text-anchor="middle">Entered once. Recorded everywhere.</text></svg></div>
            <div class="svx__copy"><p class="svx__kicker">Odoo 17, 18 and 19, on your server or ours</p><h3>Standard Odoo first, every time.</h3><p class="svx__desc">Customization is only what's left once the default has been proven not to fit. Two weeks of scoping come before any configuration, and your old system runs alongside until the numbers agree.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Chart of accounts and tax set up for UAE, KSA and Pakistan</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Stock flows mapped before a single screen is configured</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>QWeb invoices and statements on your letterhead</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Migrations from 15, 16 and 17 with the ledger intact</span></li></ul><p class="svx__more"><a href="<?= e(url('services/odoo-erp')) ?>">More about Odoo ERP<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-web" aria-labelledby="tab-web" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="Two phones side by side showing a mobile interface" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Marketing sites, storefronts and landing pages</p><h3>Fast, editable and yours to change.</h3><p class="svx__desc">If your catalogue and stock live in Odoo, the storefront runs on Odoo eCommerce so the two never disagree. If you only need a site that loads fast and ranks, it doesn't need a database behind it.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Design and build, or a rebuild of what you have</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Odoo eCommerce wired to live stock and prices</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>English and Arabic, with right-to-left done properly</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A written guide for whoever edits it after us</span></li></ul><p class="svx__more"><a href="<?= e(url('services/website-development')) ?>">More about website development<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-seo" aria-labelledby="tab-seo" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A woman searching on her phone" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Technical, on-page and local search</p><h3>Found by buyers in your market.</h3><p class="svx__desc">A buyer in Sharjah searching for your product is a different problem from a buyer in Lahore. We fix what's broken underneath first, then build the pages and local listings that reach people ready to order.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Technical audit of crawling, indexing, speed and structured data</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Keyword mapping against how your buyers actually search</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Google Business Profile for UAE, KSA and Pakistan</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Monthly reporting on rankings, traffic and enquiries</span></li></ul><p class="svx__more"><a href="<?= e(url('services/seo')) ?>">More about SEO<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-marketing" aria-labelledby="tab-marketing" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="Two large billboards mounted on a concrete wall" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Paid search, paid social and the tracking under both</p><h3>Judged on enquiries, not impressions.</h3><p class="svx__desc">Tracking goes in before any money is spent. We agree what a good month looks like in numbers first, and if a channel isn't returning, we say so instead of asking for more budget.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Google Ads across search, Performance Max and remarketing</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Meta, TikTok and LinkedIn campaigns built by audience</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Landing pages made for the campaign, not the homepage</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>One agreed number, reported every month</span></li></ul><p class="svx__more"><a href="<?= e(url('services/digital-marketing')) ?>">More about digital marketing<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-design" aria-labelledby="tab-design" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A bound brand guidelines book" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Brand, print, packaging and signage</p><h3>What people see before they see the system.</h3><p class="svx__desc">The logo, the catalogue your sales team hands over, the label on the box. Editable source files come to you at the end, so you're never tied to us for a colour change.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Logo and brand identity with usage guidelines</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Catalogues, price lists and brochures</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Packaging, labels and retail signage</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Letterheads and invoice layouts that match Odoo</span></li></ul><p class="svx__more"><a href="<?= e(url('services/graphic-design')) ?>">More about graphic design<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-custom" aria-labelledby="tab-custom" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A monitor full of code on a developer's desk" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">For the part no product covers</p><h3>Built around the process that makes you money.</h3><p class="svx__desc">Every business has a piece of its operation that no off-the-shelf product handles. We build that piece and document it well enough that another developer could take it over tomorrow.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Odoo modules written for your process</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Integrations with banks, couriers and marketplaces</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Data migration out of any system, spreadsheets included</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Dashboards for the numbers you check every day</span></li></ul><p class="svx__more"><a href="<?= e(url('services/custom-solutions')) ?>">More about custom solutions<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php partial('site/industries'); ?>

  <section class="sec band" id="stack">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Odoo, module by module.</h2>
        <p>Each module reads from the same records as the others. Tap one to ask us about it, and your enquiry arrives with the module noted.</p>
      </div>
      <div class="rv" style="--rd:.1s"><?php partial('site/mods', ['items' => array_values(array_filter(site_modules(), static fn ($m) => $m['home'] !== false))]); ?></div>
      <p class="more-link rv"><a href="<?= e(url('odoo-modules/')) ?>">See all <?= count(site_modules()) ?> Odoo modules<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></a></p>
    </div>
  </section>

  <?php partial('site/projects'); ?>

  <section class="sec" id="more">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Before you get in touch.</h2>
        <p>How a project runs, what it costs and the questions most people ask first.</p>
      </div>
      <ul class="explore">
        <li class="rv"><a class="explore__card" href="<?= e(url('how-we-work/')) ?>">
          <span class="explore__k">How we work</span>
          <h3>Four steps, with the <span class="nobr">go-live</span> date agreed up front.</h3>
          <ol class="explore__steps"><li>Scope</li><li>Configure</li><li>Migrate</li><li>Support</li></ol>
          <span class="explore__go">See the process<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
        </a></li>
        <li class="rv" style="--rd:.06s"><a class="explore__card explore__card--deep" href="<?= e(url('pricing/')) ?>">
          <span class="explore__k">Pricing</span>
          <h3>Three ways to work with us.</h3>
          <p>Fixed-scope projects, monthly retainers or blocks of hours. Every quote arrives in writing.</p>
          <span class="explore__go">Compare the options<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
        </a></li>
        <li class="rv" style="--rd:.12s"><a class="explore__card" href="<?= e(url('faq/')) ?>">
          <span class="explore__k">FAQ</span>
          <h3>Questions before the first invoice.</h3>
          <p>Timelines, migrations from older versions, who owns the files, Arabic and support after go-live.</p>
          <span class="explore__go">Read the answers<svg class="ic" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
        </a></li>
      </ul>
    </div>
  </section>

  <?php partial('site/home-careers'); ?>

  <section class="sec" id="contact">
    <div class="wrap">
      <div class="cta">
        <svg class="cta__mark" viewBox="0 0 402 342" aria-hidden="true"><use href="#mk"/></svg>
        <div class="cta__grid">
          <div class="rv">
            <h2>Tell us what's breaking.</h2>
            <p>Send the messy version: the stalled implementation, the version you're stuck on, the report nobody trusts. We'll tell you what fixing it takes.</p>
            <ul class="cta__list">
              <li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Your message goes straight to our team.</span></li>
              <li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A scoping call comes before any quote.</span></li>
              <li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Quotes arrive in writing and hold unless the scope changes.</span></li>
            </ul>
            <p class="cta__direct">Prefer email?<br><a href="mailto:info@automateltd.com?subject=Enquiry%20from%20automateltd.com"><svg class="ic" aria-hidden="true"><use href="#i-mail"/></svg>info@automateltd.com</a></p>
          </div>
          <div class="cform rv" style="--rd:.12s">
            <?php partial('site/contact-form', ['sent' => $sent]); ?>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php partial('site/footer', ['home' => true]); ?>
</body>
</html>
