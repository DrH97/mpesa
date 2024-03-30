<?php

namespace DrH\Mpesa\Library;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use function base64_encode;
use function config;

class IdCheck extends ApiCore
{
    /**
     * @param string $number
     * @param string|null $callback
     * @return mixed
     * @throws Exception
     * @throws GuzzleException
     */
    public function validate(string $number, string $callback = null): mixed
    {
        $number = $this->formatPhoneNumber($number);
        $time = Carbon::now()->format('YmdHis');
        $shortCode = config('drh.mpesa.c2b.short_code');
        $passkey = config('drh.mpesa.c2b.passkey');
        $defaultCallback = config('drh.mpesa.id_validation_callback');
        $initiator = config('drh.mpesa.initiator');
        $password = base64_encode($shortCode . $passkey . $time);
        $body = [
            'Initiator' => $initiator,
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'TransactionType' => 'CheckIdentity',
            'PhoneNumber' => $number,
            'CallBackURL' => $callback ?: $defaultCallback,
            'TransactionDesc' => ' '
        ];
        return $this->sendRequest($body, 'id_check');
    }
}
