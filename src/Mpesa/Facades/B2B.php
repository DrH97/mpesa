<?php

namespace DrH\Mpesa\Facades;

use DrH\Mpesa\Entities\MpesaB2bRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method static MpesaB2bRequest pay(string $type, int $shortcode, int $amount, string $reference, string $phone)
 *
 * @see \DrH\Mpesa\Library\B2BPayment
 */
class B2B extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_b2b';
    }
}
