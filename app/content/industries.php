<?php
/*
 * Industries on the homepage. 'img' is an Unsplash photo id; 'service' pre-fills
 * the enquiry form. 'size' shapes the bento: wide tiles span two columns.
 */
declare(strict_types=1);

return [
    ['name' => 'Retail and multi-branch shops', 'text' => 'Counters, stock and accounts across every branch, closing the day in one place.',
        'img' => 'photo-1556740772-1a741367b93e', 'alt' => 'A customer paying by card at a shop counter', 'size' => 'wide', 'module' => 'Point of Sale'],
    ['name' => 'Wholesale and distribution', 'text' => 'Warehouses, price lists by customer and deliveries that match the invoice.',
        'img' => 'photo-1587293852726-70cdb56c2866', 'alt' => 'Cardboard boxes stacked on warehouse racks', 'size' => 'tall', 'module' => 'Inventory'],
    ['name' => 'Manufacturing', 'text' => 'Bills of material, work orders and real costing, from raw stock to finished goods.',
        'img' => 'photo-1610891015188-5369212db097', 'alt' => 'Machinery on a textile factory floor', 'size' => '', 'module' => 'Manufacturing'],
    ['name' => 'Online brands', 'text' => 'A storefront selling from live stock, with orders flowing to the warehouse.',
        'img' => 'photo-1449247666642-264389f5f5b1', 'alt' => 'A parcel being packed for shipping', 'size' => '', 'module' => 'eCommerce'],
    ['name' => 'Professional services', 'text' => 'Projects, timesheets and invoicing, plus a website that brings in enquiries.',
        'img' => 'photo-1630487656049-6db93a53a7e9', 'alt' => 'A team reviewing documents at a table', 'size' => 'wide', 'module' => 'Project'],
];
