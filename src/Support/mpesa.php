<?php

use DrH\Mpesa\Database\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Database\Entities\MpesaStkRequest;
use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\Library\MpesaAccount;
use DrH\Mpesa\Library\Simulate;
use Illuminate\Support\Facades\Log;

if (!function_exists('mpesa_balance')) {
    function mpesa_balance(): array
    {
        return B2C::balance();
    }
}
if (!function_exists('mpesa_send')) {
    function mpesa_send(string $phone, int $amount, string $remarks): MpesaBulkPaymentRequest
    {
        return B2C::send($phone, $amount, $remarks);
    }
}
if (!function_exists('mpesa_id_check')) {
    function mpesa_id_check(string $phone): array
    {
        return Identity::validate($phone);
    }
}
if (!function_exists('mpesa_stk_status')) {
    function mpesa_stk_status(string $stkRequestId): array
    {
        return STK::status($stkRequestId);
    }
}
if (!function_exists('mpesa_request')) {
    function mpesa_request(
        $phone,
        $amount,
        $reference = null,
        $description = null,
        MpesaAccount $account = null
    ): MpesaStkRequest
    {
        return STK::push($amount, $phone, $reference, $description, $account);
    }
}
if (!function_exists('mpesa_simulate')) {
    function mpesa_simulate($phone, $amount): array
    {
        return app(Simulate::class)->push($phone, $amount);
    }
}

$channel = Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/mpesa.log'),
]);

if (!function_exists('mpesaLog')) {
    function mpesaLog(string|array $level, string $message, array $context = []): void
    {
        Log::log($level, $message, $context);
    }
}

if (!function_exists('mpesaLogError')) {
    function mpesaLogError(string|array $message, array $context = []): void
    {
        Log::log('error', $message, $context);
    }
}

if (!function_exists('mpesaLogInfo')) {
    function mpesaLogInfo(string|array $message, array $context = []): void
    {
        Log::log('info', $message, $context);
    }
}
