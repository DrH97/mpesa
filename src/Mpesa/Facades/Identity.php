<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_identity';
    }
}
