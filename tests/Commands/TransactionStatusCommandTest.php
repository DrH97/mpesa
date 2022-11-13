<?php

namespace DrH\Mpesa\Tests\Commands;

use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Tests\MockServerTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionStatusCommandTest extends MockServerTestCase
{
    use RefreshDatabase;

    /** @test */
    function the_command_returns_nothing_text_when_no_pending_bulk_requests()
    {
        $this->artisan('mpesa:transaction_status')
            ->expectsOutput('Nothing to query... all transactions seem to be ok.')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_logs_queries()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['b2c']['request']);


        MpesaBulkPaymentResponse::create([
            'result_type' => 0,
            'result_code' => 0,
            'result_desc' => "Success",
            'conversation_id' => 'test_conv_id',
            'transaction_id' => 'NLJ41HAY6Q',
            'originator_conversation_id' => 'test_origin_conv_id',
        ]);

        $this->artisan('mpesa:transaction_status')
            ->expectsOutput('Logging status queries')
            ->assertExitCode(0);
    }
}
