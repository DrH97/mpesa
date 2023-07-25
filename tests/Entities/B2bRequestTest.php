<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaB2bCallback;
use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Tests\TestCase;

class B2bRequestTest extends TestCase
{
    /** @test */
    function a_request_has_default_command_id()
    {
        MpesaB2bRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'party_a' => 000000,
            'party_b' => 000000,
            'account_reference' => 'test_ref',
            'response_code' => '0',
            'response_description' => 'test',
        ]);

        $this->assertDatabaseHas(MpesaB2bRequest::class, ['command_id' => 'BusinessPayBill']);
    }

    /** @test */
    function a_request_can_have_a_response()
    {
        $request = MpesaB2bRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'party_a' => 000000,
            'party_b' => 000000,
            'account_reference' => 'test_ref',
            'response_code' => '0',
            'response_description' => 'test',
        ]);

        $callback = MpesaB2bCallback::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'receiver_party_public_name' => '0000 - test',
        ]);

        $this->assertEquals($request->response->toArray(), $callback->refresh()->toArray());
    }
}
