<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Registrar
 * @package DrH\Mpesa\Facades
 */
class Registrar extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpesa_registrar';
    }
}
