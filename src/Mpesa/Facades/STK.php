<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class STK
 * @package DrH\Mpesa\Facades
 */
class STK extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mpesa_stk';
    }
}
