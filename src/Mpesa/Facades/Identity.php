<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Identity
 * @package DrH\Mpesa\Facades
 */
class Identity extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpesa_identity';
    }
}
