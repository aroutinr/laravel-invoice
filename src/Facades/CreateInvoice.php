<?php

namespace AroutinR\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class CreateInvoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'create-invoice';
    }
}
