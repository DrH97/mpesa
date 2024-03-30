<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaB2cResultParameter;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Tests\TestCase;
use Illuminate\Database\QueryException;

class B2cResultParameterTest extends TestCase
{
    /** @test */
    function a_result_parameter_belongs_to_a_b2c_response()
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

        $this->assertEquals($response->refresh()->toArray(), $parameter->response->toArray());
    }

    /** @test */
    function a_result_parameter_has_unique_transaction_receipt()
    {
        MpesaB2cResultParameter::create([
            'response_id' => 1,
            'transaction_amount' => 10,
            'transaction_receipt' => "NLJ41HAY6Q",
            'b2c_recipient_is_registered_customer' => "Y",
            'b2c_charges_paid_account_available_funds' => -4510.00,
            'receiver_party_public_name' => "254708374149 - John Doe",
            'b2c_utility_account_available_funds' => 10116.00,
            'b2c_working_account_available_funds' => 900000.00,
            'transaction_completed_date_time' => "19.12.2019 11:45:50"
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches(
            "/UNIQUE constraint failed: mpesa_b2c_result_parameters.transaction_receipt/"
        );

        MpesaB2cResultParameter::create([
            'response_id' => 1,
            'transaction_amount' => 10,
            'transaction_receipt' => "NLJ41HAY6Q",
            'b2c_recipient_is_registered_customer' => "Y",
            'b2c_charges_paid_account_available_funds' => -4510.00,
            'receiver_party_public_name' => "254708374149 - John Doe",
            'b2c_utility_account_available_funds' => 10116.00,
            'b2c_working_account_available_funds' => 900000.00,
            'transaction_completed_date_time' => "19.12.2019 11:45:50"
        ]);
    }
}
