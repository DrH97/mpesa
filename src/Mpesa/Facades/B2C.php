<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

class B2C extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_b2c';
    }
}
