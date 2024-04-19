<?php

namespace DrH\Mpesa\Tests\Commands;

use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Tests\MockServerTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class B2bTransactionStatusCommandTest extends MockServerTestCase
{
    use RefreshDatabase;

    /** @test */
    function the_command_returns_nothing_text_when_no_pending_b2b_requests()
    {
        $this->artisan('mpesa:b2b_transaction_status')
            ->expectsOutput('Nothing to query... all transactions seem to be ok.')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_logs_queries()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['b2b']['response']);

        MpesaB2bRequest::create([
            'party_a' => '12345',
            'party_b' => '12345',
            'amount' => 12345,
            'account_reference' => '',
            'conversation_id' => 'AG_20230420_2010759fd5662ef6d054',
            'originator_conversation_id' => '5118-111210482-1',
            'response_code' => 0,
            'response_description' => "Accept the service request successfully.",
        ]);

        $this->artisan('mpesa:b2b_transaction_status')
            ->expectsOutput('Logging status queries')
            ->assertExitCode(0);
    }
}
