<?php

namespace DrH\Mpesa\Facades;

use DrH\Mpesa\Entities\MpesaStkRequest as MSR;
use DrH\Mpesa\Library\MpesaAccount;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array status(string $checkoutRequestId)
 * @method static MSR push(int $amount, string $phone, string $ref, string $description, MpesaAccount $account = null)
 *
 * @see \DrH\Mpesa\Library\StkPush
 */
class STK extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa_stk';
    }
}
