<?php

namespace DrH\Mpesa\Facades;

use DrH\Mpesa\Entities\MpesaB2bRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method static MpesaB2bRequest pay(string $type, string $shortcode, int $amount, string $reference, string $phone)
 * @method static MpesaB2bRequest status(MpesaB2bRequest $request)
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
