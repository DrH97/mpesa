<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaB2cResultParameter;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Tests\TestCase;

class BulkPaymentResponseTest extends TestCase
{
    /** @test */
    function a_response_belongs_to_a_b2c_request()
    {
        $response = MpesaBulkPaymentResponse::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
        ]);

        $request = MpesaBulkPaymentRequest::create([
            'conversation_id' => 'test_conv_id',
            'originator_conversation_id' => 'test_origin_conv_id',
            'amount' => 10,
            'phone' => '0700000000',
            'remarks' => 'test_remarks',
        ]);

        $this->assertEquals($request->refresh()->toArray(), $response->request->toArray());
    }

    /** @test */
    function a_response_can_have_a_result_parameter()
    {
        $response = MpesaBulkPaymentResponse::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
        ]);

        $parameter = MpesaB2cResultParameter::create([
            'response_id' => $response->id,
            'transaction_amount' => 10,
            'transaction_receipt' => "NLJ41HAY6Q",
            'b2c_recipient_is_registered_customer' => "Y",
            'b2c_charges_paid_account_available_funds' => -4510.00,
            'receiver_party_public_name' => "254708374149 - John Doe",
            'b2c_utility_account_available_funds' => 10116.00,
            'b2c_working_account_available_funds' => 900000.00,
            'transaction_completed_date_time' => "19.12.2019 11:45:50"
        ]);

        $this->assertEquals($response->resultParameter->toArray(), $parameter->refresh()->toArray());
    }

}
