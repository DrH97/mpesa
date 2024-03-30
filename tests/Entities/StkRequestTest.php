<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Tests\TestCase;
use Illuminate\Database\QueryException;

class StkRequestTest extends TestCase
{
    /** @test */
    function a_request_has_unique_request_ids()
    {
        MpesaStkRequest::create([
            'phone' => "0700000000",
            'amount' => 10,
            'reference' => 'test_ref',
            'description' => 'test_desc',
            'checkout_request_id' => 'test_checkout_req_id',
            'merchant_request_id' => 'test_merchant_req_id',
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches("/UNIQUE constraint failed: mpesa_stk_requests.checkout_request_id/");

        MpesaStkRequest::create([
            'phone' => "0700000000",
            'amount' => 10,
            'reference' => 'test_ref',
            'description' => 'test_desc',
            'checkout_request_id' => 'test_checkout_req_id',
            'merchant_request_id' => 'test_merchant_req_id_2',
        ]);


        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches("/UNIQUE constraint failed: mpesa_stk_requests.merchant_request_id/");

        MpesaStkRequest::create([
            'phone' => "0700000000",
            'amount' => 10,
            'reference' => 'test_ref',
            'description' => 'test_desc',
            'checkout_request_id' => 'test_checkout_req_id_2',
            'merchant_request_id' => 'test_merchant_req_id',
        ]);
    }


    /** @test */
    function a_request_has_default_status()
    {
        $request = MpesaStkRequest::create([
            'phone' => "0700000000",
            'amount' => 10,
            'reference' => 'test_ref',
            'description' => 'test_desc',
            'checkout_request_id' => 'test_checkout_req_id',
            'merchant_request_id' => 'test_merchant_req_id',
        ]);

        $this->assertDatabaseHas(MpesaStkRequest::class, ['status' => 'REQUESTED']);
    }

    /** @test */
    function a_request_can_have_a_callback()
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

        $this->assertEquals($request->response->toArray(), $callback->refresh()->toArray());
    }
}
