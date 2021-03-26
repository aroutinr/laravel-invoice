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
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Define the default currency for the package
    | Only 3 characters
    |
    */

    'default_currency' => 'USD',

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
