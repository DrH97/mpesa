<?php

namespace DrH\Mpesa\Facades;

use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array balance()
 * @method static array status(string $transactionId)
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
