<?php

use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Facades\B2B;
use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\Library\MpesaAccount;
use DrH\Mpesa\Library\Simulate;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

use function DeepCopy\deep_copy;

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
if (!function_exists('mpesa_b2c_status')) {
    function mpesa_b2c_status(string $stkRequestId): array
    {
        return B2C::status($stkRequestId);
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
    ): MpesaStkRequest {
        return STK::push($amount, $phone, $reference, $description, $account);
    }
}
if (!function_exists('mpesa_simulate')) {
    function mpesa_simulate($phone, $amount): array
    {
        return app(Simulate::class)->push($phone, $amount);
    }
}

if (!function_exists('mpesa_b2b')) {
    function mpesa_b2b(
        string $type,
        string $shortcode,
        int $amount,
        string $reference,
        string $phone
    ): MpesaB2bRequest {
        return B2B::pay($type, $shortcode, $amount, $reference, $phone);
    }
}

if (!function_exists('mpesa_b2b_status')) {
    function mpesa_b2b_status(MpesaB2bRequest $request): MpesaB2bRequest
    {
        return B2B::status($request);
    }
}

if (!function_exists('shouldMpesaLog')) {
    function shouldMpesaLog(): bool
    {
        return config('drh.mpesa.logging.enabled') == true;
    }
}

if (!function_exists('getMpesaLogger')) {
    function getMpesaLogger(): LoggerInterface
    {
        if (shouldMpesaLog()) {
            $channels = [];

            foreach (config('drh.mpesa.logging.channels') as $rawChannel) {
                if (is_string($rawChannel)) {
                    $channels[] = $rawChannel;
                } elseif (is_array($rawChannel)) {
                    $channels[] = Log::build($rawChannel);
                }
            }

            return Log::stack($channels);
        }

        return Log::build([
            'driver' => 'single',
            'path' => '/dev/null',
        ]);
    }
}

if (!function_exists('mpesaLog')) {
    function mpesaLog(string $level, string|array $message, array $context = []): void
    {
        $message = '[LIB - MPESA]: ' . $message;
        getMpesaLogger()->log($level, $message, $context);
    }
}

if (!function_exists('mpesaLogError')) {
    function mpesaLogError(string|array $message, array $context = []): void
    {
        $message = '[LIB - MPESA]: ' . $message;
        getMpesaLogger()->error($message, $context);
    }
}

if (!function_exists('mpesaLogInfo')) {
    function mpesaLogInfo(string|array $message, array $context = []): void
    {
        $message = '[LIB - MPESA]: ' . $message;
        getMpesaLogger()->info($message, $context);
    }
}

//TODO: make this available outside library
if (!function_exists('getSanitizedArray')) {
    function getSanitizedArray(array $data): array
    {
        $dataCopy = deep_copy($data);
        $sensitiveKeys = ['password'];

        foreach ($dataCopy as $key => $value) {
            if (in_array(mb_strtolower($key), $sensitiveKeys)) {
                unset($dataCopy[$key]);
            }
        }

        return $dataCopy;
    }
}
