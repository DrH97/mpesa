<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

class STK extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_stk';
    }
}
