<?php

namespace AroutinR\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

class Invoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'invoice';
    }
}
