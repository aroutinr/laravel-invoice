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
        'payments' => 'payments',
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

    /*
    |--------------------------------------------------------------------------
    | Invoice info
    |--------------------------------------------------------------------------
    |
    | Set your Invoice info for the invoice receipt
    |
    */

    'info' => [
        'name' => env('APP_NAME', 'Laravel Invoice'),
        'address' => 'Laravel Invoice Address',
        'contact' => 'Phone: +1(234)567-8900 / email: sales@laravel-invoice.test',
        'url' => env('APP_URL', 'http://laravel-invoice.test'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice format
    |--------------------------------------------------------------------------
    |
    | Define the number format for the invoice 
    | Decimals quantity, decimal separator and thousand seperator
    |
    */

    'format' => [
        'decimals' => 2,
        'decimal_separator' => '.',
        'thousand_seperator' => ',',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment methods
    |--------------------------------------------------------------------------
    |
    | Define the payment methods for your invoices
    |
    */

    'payment_methods' => [
        'Cash', 'Check', 'Transfer'
    ],
];
