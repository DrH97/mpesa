<?php

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\Library\MpesaAccount;
use DrH\Mpesa\Library\Simulate;
use GuzzleHttp\Exception\GuzzleException;

if (!function_exists('mpesa_balance')) {
    /**
     * @return mixed
     */
    function mpesa_balance()
    {
        return B2C::balance();
    }
}
if (!function_exists('mpesa_send')) {
    /**
     * @param string $phone
     * @param int $amount
     * @param $remarks
     * @return mixed
     */
    function mpesa_send($phone, $amount, $remarks = null)
    {
        return B2C::send($phone, $amount, $remarks);
    }
}
if (!function_exists('mpesa_id_check')) {
    /**
     * @param string $phone
     * @return mixed
     */
    function mpesa_id_check($phone)
    {
        return Identity::validate($phone);
    }
}
if (!function_exists('mpesa_stk_status')) {
    /**
     * @param int $id
     * @return mixed
     */
    function mpesa_stk_status($id)
    {
        return STK::validate($id);
    }
}
if (!function_exists('mpesa_request')) {
    /**
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @param string|null $description
     * @param MpesaAccount|null $account
     * @return mixed
     */
    function mpesa_request($phone, $amount, $reference = null, $description = null, MpesaAccount $account = null)
    {
        return STK::push($amount, $phone, $reference, $description, $account);
    }
}
if (!function_exists('mpesa_validate')) {
    /**
     * @param string|int $id
     * @return mixed
     */
    function mpesa_validate($id)
    {
        return STK::validate($id);
    }
}
if (!function_exists('mpesa_simulate')) {
    /**
     * @param int $phone
     * @param string $amount
     * @return mixed
     * @throws MpesaException
     * @throws GuzzleException
     */
    function mpesa_simulate($phone, $amount)
    {
        return app(Simulate::class)->push($phone, $amount);
    }
}
