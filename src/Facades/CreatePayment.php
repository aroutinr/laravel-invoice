<?php

namespace AroutinR\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class CreatePayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'create-payment';
    }
}
