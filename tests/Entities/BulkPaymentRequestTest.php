<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Tests\TestCase;

class BulkPaymentRequestTest extends TestCase
{
    /** @test */
    function a_request_has_default_command_id()
    {
        MpesaBulkPaymentRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'phone' => '0700000000',
            'remarks' => 'test_remarks',
        ]);

        $this->assertDatabaseHas(MpesaBulkPaymentRequest::class, ['command_id' => 'BusinessPayment']);
    }

    /** @test */
    function a_request_can_have_a_response()
    {
        $request = MpesaBulkPaymentRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'phone' => '0700000000',
            'remarks' => 'test_remarks',
        ]);

        $response = MpesaBulkPaymentResponse::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
        ]);

        $this->assertEquals($request->response->toArray(), $response->refresh()->toArray());
    }
}
