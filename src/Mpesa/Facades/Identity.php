<?php

namespace DrH\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array validate(string $number, string $callback = null)
 *
 * @see \DrH\Mpesa\Library\IdCheck
 */
class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_identity';
    }
}
