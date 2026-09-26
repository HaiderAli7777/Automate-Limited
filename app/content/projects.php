<?php
/*
 * The kinds of project we take on, shown on the homepage. These describe
 * typical engagements, not named clients. Swap in real case studies (with the
 * client's permission) as they become available.
 */
declare(strict_types=1);

return [
    [
        'title' => 'Three shops and a warehouse, one set of numbers',
        'kind' => 'Odoo ERP',
        'before' => ['Each branch counts stock in its own spreadsheet', 'Sales reach the accounts a week late', 'Month end takes a person the whole week'],
        'after' => ['Point of Sale in every branch, stock updated as it sells', 'Invoices and payments post to the ledger the same day', 'Managers check sales on their phone'],
        'modules' => ['Point of Sale', 'Inventory', 'Accounting'],
    ],
    [
        'title' => 'Moving a distributor from Odoo 15 to 19',
        'kind' => 'Migration',
        'before' => ['Custom modules nobody can upgrade', 'Reports rebuilt by hand in Excel', 'Support ended on the old version'],
        'after' => ['Standard features replace most custom code', 'Opening balances reconciled against the old system', 'Both systems run side by side until they agree'],
        'modules' => ['Inventory', 'Purchase', 'Accounting'],
    ],
    [
        'title' => 'A bilingual storefront that sells from live stock',
        'kind' => 'Website and SEO',
        'before' => ['Online prices and stock drift from the shop', 'Arabic pages mirrored as an afterthought', 'No idea which ads bring in orders'],
        'after' => ['Odoo eCommerce reading the same stock as the warehouse', 'English and Arabic built properly, right-to-left included', 'Tracking in place before any budget is spent'],
        'modules' => ['eCommerce', 'Website'],
    ],
];
