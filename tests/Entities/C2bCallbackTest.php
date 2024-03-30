<?php

namespace DrH\Mpesa\Tests\Entities;

use DrH\Mpesa\Entities\MpesaC2bCallback;
use DrH\Mpesa\Tests\TestCase;
use Illuminate\Database\QueryException;

class C2bCallbackTest extends TestCase
{
    /** @test */
    function a_callback_has_a_unique_trans_id()
    {
        MpesaC2bCallback::create([
            "transaction_type" => "Pay Bill",
            "trans_id" => "RKTQDM7W6S",
            "trans_time" => "20191122063845",
            "trans_amount" => "10",
            "business_short_code" => "600638",
            "bill_ref_number" => "",
            "invoice_number" => "",
            "org_account_balance" => "49197.00",
            "third_party_trans_id" => "",
            "msisdn" => "254708374149",
            "first_name" => "John",
            "middle_name" => "",
            "last_name" => "Doe"
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches("/UNIQUE constraint failed: mpesa_c2b_callbacks.trans_id/");

        MpesaC2bCallback::create([
            "transaction_type" => "Pay Bill",
            "trans_id" => "RKTQDM7W6S",
            "trans_time" => "20191122063845",
            "trans_amount" => "10",
            "business_short_code" => "600638",
            "org_account_balance" => "49197.00",
            "msisdn" => "254708374149",
        ]);
    }

    /** @test */
    function a_callback_concatenates_names()
    {
        $callback = MpesaC2bCallback::create([
            "transaction_type" => "Pay Bill",
            "trans_id" => "RKTQDM7W6S",
            "trans_time" => "20191122063845",
            "trans_amount" => "10",
            "business_short_code" => "600638",
            "bill_ref_number" => "",
            "invoice_number" => "",
            "org_account_balance" => "49197.00",
            "third_party_trans_id" => "",
            "msisdn" => "254708374149",
            "first_name" => "John",
            "middle_name" => "",
            "last_name" => "Doe"
        ]);

        $this->assertEquals("John Doe", $callback->name);

        $callback->middle_name = "Midname";
        $callback->save();

        $this->assertEquals("John Midname Doe", $callback->name);
    }
}
