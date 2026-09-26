<?php
/*
 * Odoo modules shown on the homepage bento and on /odoo-modules/. Each tile
 * links to the enquiry page with the module filled in, so the lead lands in
 * the CRM tagged with it. 'home' tiles appear on the homepage; 'size' is the
 * bento treatment there (wide, deep, tint).
 */
declare(strict_types=1);

return [
    ['name' => 'Accounting', 'icon' => 'accounting', 'blurb' => 'Tax, banking, statements and audit-ready books', 'home' => 'is-wide is-deep',
        'points' => ['VAT and sales tax for UAE, KSA and Pakistan', 'Bank feeds and reconciliation rules', 'Multi-company and multi-currency closing']],
    ['name' => 'Inventory', 'icon' => 'inventory', 'blurb' => 'Multi-warehouse stock, lots, landed cost and valuation', 'home' => '',
        'points' => ['Warehouses, bins and routes', 'Lots, serial numbers and expiry dates', 'Landed costs and stock valuation']],
    ['name' => 'Sales', 'icon' => 'sales', 'blurb' => 'Quotations, price lists, commission and delivery', 'home' => '',
        'points' => ['Quotation templates and e-signature', 'Price lists by customer and currency', 'Commission and sales targets']],
    ['name' => 'Purchase', 'icon' => 'purchase', 'blurb' => 'Vendor terms, approvals and three-way matching', 'home' => '',
        'points' => ['Requests for quotation and approvals', 'Vendor price lists and lead times', 'Bills matched to orders and receipts']],
    ['name' => 'Manufacturing', 'icon' => 'manufacturing', 'blurb' => 'Bills of material, routings and work orders', 'home' => '',
        'points' => ['Multi-level bills of material', 'Work centres, routings and costing', 'Quality checks and maintenance']],
    ['name' => 'Point of Sale', 'icon' => 'pos', 'blurb' => 'Counters, offline mode, shift close and returns', 'home' => 'is-wide is-tint',
        'points' => ['Works offline and syncs when back', 'Shift opening, closing and cash control', 'Loyalty, returns and receipts']],
    ['name' => 'HR and Payroll', 'icon' => 'hr', 'blurb' => 'Attendance, leave, gratuity and salary rules', 'home' => 'is-wide',
        'points' => ['Attendance, leave and approvals', 'Salary rules, gratuity and WPS files', 'Employee self-service']],
    ['name' => 'eCommerce', 'icon' => 'ecommerce', 'blurb' => 'A storefront that sells from live stock', 'home' => 'is-wide is-deep',
        'points' => ['Products, prices and stock from Odoo', 'Online payments and delivery rules', 'Orders flow straight to the warehouse']],
    ['name' => 'CRM', 'icon' => 'crm', 'blurb' => 'Leads, pipeline stages and follow-ups', 'home' => false,
        'points' => ['Website enquiries captured as leads', 'Pipeline stages and win rates', 'Follow-up reminders and activities']],
    ['name' => 'Project', 'icon' => 'project', 'blurb' => 'Tasks, timesheets and billable hours', 'home' => false,
        'points' => ['Kanban boards and milestones', 'Timesheets linked to tasks', 'Invoicing from time and materials']],
    ['name' => 'Helpdesk', 'icon' => 'helpdesk', 'blurb' => 'Tickets, SLAs and customer replies', 'home' => false,
        'points' => ['Tickets from email and the website', 'Response targets and escalation', 'Customer portal and ratings']],
    ['name' => 'Website', 'icon' => 'web', 'blurb' => 'Pages and forms that feed Odoo directly', 'home' => false,
        'points' => ['Drag-and-drop page editing', 'Forms that create leads and tickets', 'Blog, events and SEO settings']],
];
