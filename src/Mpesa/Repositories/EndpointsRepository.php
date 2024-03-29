<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Library\MpesaAccount;

class EndpointsRepository
{
    /**
     * @param string $section
     * @param MpesaAccount|null $account
     * @return string
     * @throws ClientException
     */
    private static function getEndpoint(string $section, MpesaAccount $account = null): string
    {
        $list = [
            'auth' => 'oauth/v1/generate?grant_type=client_credentials',
            'id_check' => 'mpesa/checkidentity/v1/query',
            'c2b_register_urls' => 'mpesa/c2b/v1/registerurl',
            'stk_push' => 'mpesa/stkpush/v1/processrequest',
            'stk_status' => 'mpesa/stkpushquery/v1/query',
            'b2c' => 'mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'mpesa/transactionstatus/v1/query',
            'account_balance' => 'mpesa/accountbalance/v1/query',
            'b2b' => 'mpesa/b2b/v1/paymentrequest',
            'simulate' => 'mpesa/c2b/v1/simulate',
        ];
        if ($item = $list[$section]) {
            return self::getUrl($item, $account);
        }
        throw new ClientException('Unknown endpoint');
    }

    /**
     * @param string $suffix
     * @param MpesaAccount|null $account
     * @return string
     */
    private static function getUrl(string $suffix, MpesaAccount $account = null): string
    {
        $baseEndpoint = 'https://api.safaricom.co.ke/';
        if (config('drh.mpesa.sandbox') || ($account && $account->sandbox)) {
            $baseEndpoint = 'https://sandbox.safaricom.co.ke/';
        }
        return $baseEndpoint . $suffix;
    }

    /**
     * @param string $endpoint
     * @param MpesaAccount|null $account
     * @return string
     * @throws ClientException
     */
    public static function build(string $endpoint, MpesaAccount $account = null): string
    {
        return self::getEndpoint($endpoint, $account);
    }
}
