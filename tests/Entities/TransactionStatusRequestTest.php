<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaTransactionStatusCallback;
use DrH\Mpesa\Entities\MpesaTransactionStatusRequest;
use DrH\Mpesa\Tests\TestCase;

class TransactionStatusRequestTest extends TestCase
{
    /** @test */
    function a_request_is_created_successfully()
    {
        MpesaTransactionStatusRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'response_code' => '0',
            'response_description' => 'test',
        ]);

        $this->assertDatabaseHas(MpesaTransactionStatusRequest::class, ['conversation_id' => 'test_conv_id']);
    }

    /** @test */
    function a_request_can_have_a_response()
    {
        $request = MpesaTransactionStatusRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'response_code' => '0',
            'response_description' => 'test',
        ]);

        $callback = MpesaTransactionStatusCallback::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'result_originator_conversation_id' => 'test_origin_conv_id',
            'result_conversation_id' => 'test_conv_id',
        ]);

        $this->assertEquals($request->response->toArray(), $callback->refresh()->toArray());
    }
}
