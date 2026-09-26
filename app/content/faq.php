<?php
/*
 * Frequently asked questions, grouped for /faq/. Each entry is
 * [question, answer, topics]; service pages show the entries tagged with
 * their topic ('odoo', 'web', 'seo', 'marketing', 'design', 'custom').
 */
declare(strict_types=1);

return [
    'Odoo ERP' => [
        ['How long does an Odoo implementation take?', 'It depends on how much of the business is moving, but you\'ll have the date before we configure anything. The first two weeks are scoping, and the go-live date goes into the document you sign at the end of them.', ['odoo']],
        ['We\'re three versions behind. Can you still move us?', 'Yes. We migrate from 15, 16 and 17 up to 19 and bring the ledger with you. Master data and opening balances move under a reconciliation you can tie back to your old system.', ['odoo', 'custom']],
        ['Do we have to switch everything over in one weekend?', 'No. Your old system and Odoo run side by side until the numbers agree. You switch when the trial balances match, not when the calendar says so.', ['odoo']],
        ['Community or Enterprise?', 'Either can be right. During scoping we list which of the modules you need are Enterprise only and what the licences cost, so you decide before anything is configured.', ['odoo']],
        ['Can you take over an implementation another partner started?', 'Yes. We start with an audit of what has been configured and customised, then tell you plainly what to keep, what to fix and what to remove.', ['odoo', 'custom']],
        ['Where will Odoo be hosted?', 'On your own server, on ours or on Odoo\'s cloud. We recommend one during scoping, based on your customisations, your team and your budget.', ['odoo']],
    ],
    'Websites, search and design' => [
        ['Will our team be able to edit the website afterwards?', 'That\'s how we build it. Nobody should need to call us to change a price or a photo. Handover includes a written guide and training for whoever will be editing.', ['web']],
        ['Do we own the design files?', 'Yes. Editable source files are handed over at the end, for the brand work and for the website.', ['design', 'web']],
        ['Can you work in Arabic?', 'Yes. English and Arabic, including right-to-left layouts built properly rather than mirrored as an afterthought.', ['web', 'design', 'seo']],
        ['How soon does SEO show results?', 'Technical fixes often show within weeks. Rankings for competitive searches take months. We agree the numbers to watch at the start and report on them every month.', ['seo']],
        ['Is there a minimum ad budget?', 'No fixed minimum. Before you spend anything, we tell you what budget a channel needs to produce results you can actually read.', ['marketing']],
    ],
    'Working with us' => [
        ['How do quotes work?', 'There\'s no menu price. Every quote follows a scoping call, arrives in writing and holds unless the scope changes.', ['odoo', 'web', 'seo', 'marketing', 'design', 'custom']],
        ['What happens after go-live?', 'A support retainer with a named consultant, a response time agreed in writing, and a quarterly review of what your team still does by hand.', ['odoo', 'custom']],
        ['Do you work with businesses outside Pakistan?', 'Yes. We work with businesses in Pakistan, the UAE and Saudi Arabia, and with teams further afield. Calls are scheduled around your time zone.', ['marketing', 'seo']],
        ['Who will we actually work with?', 'A named consultant who knows your setup, backed by the developers, designers and marketers on our team. You won\'t be passed around a ticket queue.', ['custom', 'design']],
    ],
];
