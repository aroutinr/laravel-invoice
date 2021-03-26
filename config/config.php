<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Define the table names for the package
    |
    */

    'table_names' => [
        'invoices' => 'invoices',
        'invoice_lines' => 'invoice_lines',
        'invoice_addresses' => 'invoice_addresses',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Define the currency for the package. 
    | You can change it while making a new invoice.
    | Only 3 characters
    |
    */

    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Custom Fields
    |--------------------------------------------------------------------------
    |
    | Define the customs fields that you want to use with your invoice
    |
    */

    'custom_fields' => 4,

];
