<?php

namespace DrH\Mpesa\Facades;

use DrH\Mpesa\Database\Entities\MpesaBulkPaymentRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array balance()
 * @method static MpesaBulkPaymentRequest send(string $number, int $amount, string $remarks)
 *
 * @see \DrH\Mpesa\Library\BulkSender
 */
class B2C extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_b2c';
    }
}
