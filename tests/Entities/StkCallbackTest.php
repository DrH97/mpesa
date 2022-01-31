<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Tests\TestCase;

class StkCallbackTest extends TestCase
{
    /** @test */
    function a_callback_belongs_to_an_stk_request()
    {
        $request = MpesaStkRequest::create([
            'phone' => "0700000000",
            'amount' => 10,
            'reference' => 'test_ref',
            'description' => 'test_desc',
            'checkout_request_id' => 'test_checkout_req_id',
            'merchant_request_id' => 'test_merchant_req_id',
        ]);

        $callback = MpesaStkCallback::create([
            'merchant_request_id' => 'test_merchant_req_id',
            'checkout_request_id' => 'test_checkout_req_id',
            'result_code' => '0',
            'result_desc' => 'Success',
        ]);

        $this->assertEquals($request->refresh()->toArray(), $callback->request->toArray());
    }

}
