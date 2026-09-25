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
[{"@context": "https://schema.org", "@type": "ProfessionalService", "name": "Automate Limited", "url": "https://automateltd.com/", "description": "Odoo ERP implementation and support, website development, SEO, digital marketing and graphic design for businesses in Pakistan, the Gulf and beyond.", "logo": "https://automateltd.com/logo-automate.svg", "image": "https://automateltd.com/og-automate.png", "email": "info@automateltd.com", "address": {"@type": "PostalAddress", "addressCountry": "PK"}, "areaServed": [{"@type": "Country", "name": "Pakistan"}, {"@type": "Country", "name": "United Arab Emirates"}, {"@type": "Country", "name": "Saudi Arabia"}], "knowsAbout": ["Odoo", "ERP implementation", "Odoo migration", "QWeb reports", "eCommerce", "Search engine optimisation", "Digital marketing", "Brand identity"], "hasOfferCatalog": {"@type": "OfferCatalog", "name": "Services", "itemListElement": [{"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Odoo ERP"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Website development"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "SEO"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Digital marketing"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Graphic design"}}, {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "Custom solutions"}}]}}, {"@context": "https://schema.org", "@type": "WebSite", "name": "Automate Limited", "url": "https://automateltd.com/"}, {"@context": "https://schema.org", "@type": "FAQPage", "mainEntity": [{"@type": "Question", "name": "How long does an Odoo implementation take?", "acceptedAnswer": {"@type": "Answer", "text": "It depends on how much of the business is moving, but you'll have the date before we configure anything. The first two weeks are scoping, and the go-live date goes into the document you sign at the end of them."}}, {"@type": "Question", "name": "We're three versions behind. Can you still move us?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. We migrate from 15, 16 and 17 up to 19 and bring the ledger with you. Master data and opening balances move under a reconciliation you can tie back to your old system."}}, {"@type": "Question", "name": "Do we have to switch everything over in one weekend?", "acceptedAnswer": {"@type": "Answer", "text": "No. Your old system and Odoo run side by side until the numbers agree. You switch when the trial balances match, not when the calendar says so."}}, {"@type": "Question", "name": "Will our team be able to edit the website afterwards?", "acceptedAnswer": {"@type": "Answer", "text": "That's how we build it. Nobody should need to call us to change a price or a photo. Handover includes a written guide and training for whoever will be editing."}}, {"@type": "Question", "name": "Do we own the design files?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. Editable source files are handed over at the end, for the brand work and for the website."}}, {"@type": "Question", "name": "Can you work in Arabic?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. English and Arabic, including right-to-left layouts built properly rather than mirrored as an afterthought."}}, {"@type": "Question", "name": "What happens after go-live?", "acceptedAnswer": {"@type": "Answer", "text": "A support retainer with a named consultant, a response time agreed in writing, and a quarterly review of what your team still does by hand."}}]}]
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
            <a class="btn btn--primary" href="#contact">Get in touch</a>
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
          <img src="https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=2000&amp;h=1125&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=675&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=2000&amp;h=1125&amp;q=72&amp;sat=-12&amp;con=5 2000w, https://images.unsplash.com/photo-1541535881962-3bb380b08458?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=3200&amp;h=1800&amp;q=72&amp;sat=-12&amp;con=5 3200w" sizes="100vw" width="2000" height="1125" alt="A businessman working on a laptop on a balcony above the city" loading="eager" fetchpriority="low" decoding="async">
        </div>
        <div class="panel__copy" id="panelCopy">
          <div class="wrap">
            <h2>Know where the business stands without asking anyone.</h2>
            <p>Stock, sales, invoices and payroll in one system, updated as it happens and readable from your phone.</p>
            <a class="btn btn--light" href="#contact">Get in touch</a>
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
            <div class="svx__copy"><p class="svx__kicker">Odoo 17, 18 and 19, on your server or ours</p><h3>Standard Odoo first, every time.</h3><p class="svx__desc">Customization is only what's left once the default has been proven not to fit. Two weeks of scoping come before any configuration, and your old system runs alongside until the numbers agree.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Chart of accounts and tax set up for UAE, KSA and Pakistan</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Stock flows mapped before a single screen is configured</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>QWeb invoices and statements on your letterhead</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Migrations from 15, 16 and 17 with the ledger intact</span></li></ul></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-web" aria-labelledby="tab-web" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1636247497842-81ee9c80f9df?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="Two phones side by side showing a mobile interface" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Marketing sites, storefronts and landing pages</p><h3>Fast, editable and yours to change.</h3><p class="svx__desc">If your catalogue and stock live in Odoo, the storefront runs on Odoo eCommerce so the two never disagree. If you only need a site that loads fast and ranks, it doesn't need a database behind it.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Design and build, or a rebuild of what you have</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Odoo eCommerce wired to live stock and prices</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>English and Arabic, with right-to-left done properly</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>A written guide for whoever edits it after us</span></li></ul></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-seo" aria-labelledby="tab-seo" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1520333789090-1afc82db536a?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A woman searching on her phone" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Technical, on-page and local search</p><h3>Found by buyers in your market.</h3><p class="svx__desc">A buyer in Sharjah searching for your product is a different problem from a buyer in Lahore. We fix what's broken underneath first, then build the pages and local listings that reach people ready to order.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Technical audit of crawling, indexing, speed and structured data</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Keyword mapping against how your buyers actually search</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Google Business Profile for UAE, KSA and Pakistan</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Monthly reporting on rankings, traffic and enquiries</span></li></ul></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-marketing" aria-labelledby="tab-marketing" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1762525984874-83d6ddf6a069?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="Two large billboards mounted on a concrete wall" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Paid search, paid social and the tracking under both</p><h3>Judged on enquiries, not impressions.</h3><p class="svx__desc">Tracking goes in before any money is spent. We agree what a good month looks like in numbers first, and if a channel isn't returning, we say so instead of asking for more budget.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Google Ads across search, Performance Max and remarketing</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Meta, TikTok and LinkedIn campaigns built by audience</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Landing pages made for the campaign, not the homepage</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>One agreed number, reported every month</span></li></ul></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-design" aria-labelledby="tab-design" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1636247499180-13285c86be9b?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A bound brand guidelines book" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">Brand, print, packaging and signage</p><h3>What people see before they see the system.</h3><p class="svx__desc">The logo, the catalogue your sales team hands over, the label on the box. Editable source files come to you at the end, so you're never tied to us for a colour change.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Logo and brand identity with usage guidelines</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Catalogues, price lists and brochures</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Packaging, labels and retail signage</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Letterheads and invoice layouts that match Odoo</span></li></ul></div>
          </div>
          <div class="svx__panel" role="tabpanel" id="svc-custom" aria-labelledby="tab-custom" tabindex="0">
            <div class="svx__art"><img src="https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=720&amp;h=450&amp;q=72&amp;sat=-12&amp;con=5 720w, https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1200&amp;h=750&amp;q=72&amp;sat=-12&amp;con=5 1200w, https://images.unsplash.com/photo-1630524274689-2950ac0fc91e?auto=format&amp;fit=crop&amp;w=1920&amp;h=1200&amp;q=72&amp;sat=-12&amp;con=5 1920w" sizes="(min-width: 900px) 58vw, 92vw" width="1200" height="750" alt="A monitor full of code on a developer's desk" loading="lazy" decoding="async"></div>
            <div class="svx__copy"><p class="svx__kicker">For the part no product covers</p><h3>Built around the process that makes you money.</h3><p class="svx__desc">Every business has a piece of its operation that no off-the-shelf product handles. We build that piece and document it well enough that another developer could take it over tomorrow.</p><ul class="spec"><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Odoo modules written for your process</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Integrations with banks, couriers and marketplaces</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Data migration out of any system, spreadsheets included</span></li><li><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg><span>Dashboards for the numbers you check every day</span></li></ul></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sec band" id="stack">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>Odoo, module by module.</h2>
        <p>Each module reads from the same records as the others, so nobody copies figures from one screen into another.</p>
      </div>
      <ul class="mods rv" style="--rd:.1s">
        <li class="is-wide is-deep"><svg class="ic" aria-hidden="true"><use href="#i-accounting"/></svg><h3>Accounting</h3><p>Tax, banking, statements and audit-ready books</p></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-inventory"/></svg><h3>Inventory</h3><p>Multi-warehouse stock, lots, landed cost and valuation</p></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-sales"/></svg><h3>Sales</h3><p>Quotations, price lists, commission and delivery</p></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-purchase"/></svg><h3>Purchase</h3><p>Vendor terms, approvals and three-way matching</p></li>
        <li><svg class="ic" aria-hidden="true"><use href="#i-manufacturing"/></svg><h3>Manufacturing</h3><p>Bills of material, routings and work orders</p></li>
        <li class="is-wide is-tint"><svg class="ic" aria-hidden="true"><use href="#i-pos"/></svg><h3>Point of Sale</h3><p>Counters, offline mode, shift close and returns</p></li>
        <li class="is-wide"><svg class="ic" aria-hidden="true"><use href="#i-hr"/></svg><h3>HR and Payroll</h3><p>Attendance, leave, gratuity and salary rules</p></li>
        <li class="is-wide is-deep"><svg class="ic" aria-hidden="true"><use href="#i-ecommerce"/></svg><h3>eCommerce</h3><p>A storefront that sells from live stock</p></li>
      </ul>
    </div>
  </section>

  <section class="sec" id="monday">
    <div class="wrap">
      <div class="sec-head rv">
        <h2>What changes on a Monday morning.</h2>
      </div>
      <div class="mon rv" style="--rd:.1s">
        <figure class="is-tall">
          <div class="ph"><img src="https://images.unsplash.com/photo-1787209516537-a8e66a1b4360?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1000&amp;h=1000&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1787209516537-a8e66a1b4360?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=600&amp;h=600&amp;q=72&amp;sat=-12&amp;con=5 600w, https://images.unsplash.com/photo-1787209516537-a8e66a1b4360?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1000&amp;h=1000&amp;q=72&amp;sat=-12&amp;con=5 1000w, https://images.unsplash.com/photo-1787209516537-a8e66a1b4360?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1600&amp;h=1600&amp;q=72&amp;sat=-12&amp;con=5 1600w" sizes="(min-width: 800px) 56vw, 92vw" width="1000" height="1000" alt="A sale being rung up on a point of sale terminal at a shop counter" loading="lazy" decoding="async"></div>
          <figcaption><b>At the counter</b>The sale rings up and the stock moves with it.</figcaption>
        </figure>
        <div class="mon__col">
          <figure>
            <div class="ph"><img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=900&amp;h=563&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=540&amp;h=338&amp;q=72&amp;sat=-12&amp;con=5 540w, https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=900&amp;h=563&amp;q=72&amp;sat=-12&amp;con=5 900w, https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&amp;fit=crop&amp;crop=faces,edges&amp;w=1440&amp;h=901&amp;q=72&amp;sat=-12&amp;con=5 1440w" sizes="(min-width: 800px) 40vw, 92vw" width="900" height="563" alt="Two colleagues going through the month's figures together" loading="lazy" decoding="async"></div>
            <figcaption><b>At month end</b>The books close without a week of chasing paper.</figcaption>
          </figure>
          <figure>
            <div class="ph"><img src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&amp;fit=crop&amp;w=900&amp;h=563&amp;q=72&amp;sat=-12&amp;con=5" srcset="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&amp;fit=crop&amp;w=540&amp;h=338&amp;q=72&amp;sat=-12&amp;con=5 540w, https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&amp;fit=crop&amp;w=900&amp;h=563&amp;q=72&amp;sat=-12&amp;con=5 900w, https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&amp;fit=crop&amp;w=1440&amp;h=901&amp;q=72&amp;sat=-12&amp;con=5 1440w" sizes="(min-width: 800px) 40vw, 92vw" width="900" height="563" alt="A stocked supermarket aisle" loading="lazy" decoding="async"></div>
            <figcaption><b>On the shelf</b>Every line has a price and a stock figure behind it.</figcaption>
          </figure>
        </div>
      </div>
    </div>
  </section>

  <section class="sec" id="how">
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

  <section class="sec band" id="engage">
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

  <section class="sec" id="faq">
    <div class="wrap faq-grid">
      <div class="sec-head rv">
        <h2>Questions before the first invoice.</h2>
        <p class="faq-more">Something else? Email <a href="mailto:info@automateltd.com">info@automateltd.com</a>.</p>
      </div>
      <div class="faq">
        <details open><summary>How long does an Odoo implementation take?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>It depends on how much of the business is moving, but you'll have the date before we configure anything. The first two weeks are scoping, and the go-live date goes into the document you sign at the end of them.</p></div></details>
        <details><summary>We're three versions behind. Can you still move us?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>Yes. We migrate from 15, 16 and 17 up to 19 and bring the ledger with you. Master data and opening balances move under a reconciliation you can tie back to your old system.</p></div></details>
        <details><summary>Do we have to switch everything over in one weekend?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>No. Your old system and Odoo run side by side until the numbers agree. You switch when the trial balances match, not when the calendar says so.</p></div></details>
        <details><summary>Will our team be able to edit the website afterwards?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>That's how we build it. Nobody should need to call us to change a price or a photo. Handover includes a written guide and training for whoever will be editing.</p></div></details>
        <details><summary>Do we own the design files?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>Yes. Editable source files are handed over at the end, for the brand work and for the website.</p></div></details>
        <details><summary>Can you work in Arabic?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>Yes. English and Arabic, including right-to-left layouts built properly rather than mirrored as an afterthought.</p></div></details>
        <details><summary>What happens after go-live?<svg class="ic" aria-hidden="true"><use href="#i-plus"/></svg></summary><div class="faq__a"><p>A support retainer with a named consultant, a response time agreed in writing, and a quarterly review of what your team still does by hand.</p></div></details>
      </div>
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
