<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Library\MpesaAccount;

/**
 * Class EndpointsRepository
 *
 * @package DrH\Mpesa\Repositories
 */
class EndpointsRepository
{

    /**
     * @param string $section
     * @return string
     * @throws \Exception
     * @throws MpesaException
     */
    private static function getEndpoint($section, MpesaAccount $account = null): string
    {
        $list = [
            'auth' => 'oauth/v1/generate?grant_type=client_credentials',
            'id_check' => 'mpesa/checkidentity/v1/query',
            'register' => 'mpesa/c2b/v1/registerurl',
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
        throw new MpesaException('Unknown endpoint');
    }

    /**
     * @param string $suffix
     * @return string
     */
    private static function getUrl($suffix, MpesaAccount $account = null): string
    {
        $baseEndpoint = 'https://api.safaricom.co.ke/';
        if (\config('drh.mpesa.sandbox') || ($account && $account->sandbox)) {
            $baseEndpoint = 'https://sandbox.safaricom.co.ke/';
        }
        return $baseEndpoint . $suffix;
    }

    /**
     * @param $endpoint
     * @return string
     * @throws \Exception
     * @throws MpesaException
     */
    public static function build($endpoint, MpesaAccount $account = null)
    {
        return self::getEndpoint($endpoint, $account);
    }
}
