<?php

namespace DrH\Mpesa\Tests\Commands;

use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StkStatusCommandTest extends MockServerTestCase
{
    use RefreshDatabase;

    /** @test */
    function the_command_returns_nothing_text_when_no_pending_stk_requests()
    {
        $this->artisan('mpesa:query_stk_status')
            ->expectsOutput('Nothing to query... all transactions seem to be ok.')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_logs_failed_queries()
    {
        $this->mock->append(
            new Response(
                200,
                ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth']['success'])
            )
        );
        $this->mock->append(
            new Response(
                400,
                ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['stk']['query']['error'])
            )
        );

        MpesaStkRequest::create([
            'phone' => "070000000",
            'amount' => 10,
            'reference' => "Test Ref",
            'description' => "Test Desc",
            'checkout_request_id' => "test_checkout_req_id",
            'merchant_request_id' => "test_merchant_request_id",
        ]);

        $this->artisan('mpesa:query_stk_status')
            ->expectsOutput('Logging failed queries')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_logs_successful_queries()
    {
        $this->mock->append(
            new Response(
                200,
                ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth']['success'])
            )
        );
        $this->mock->append(
            new Response(
                200,
                ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['stk']['query']['success'])
            )
        );

        MpesaStkRequest::create([
            'phone' => "070000000",
            'amount' => 10,
            'reference' => "Test Ref",
            'description' => "Test Desc",
            'checkout_request_id' => "ws_CO_13012021093521236557",
            'merchant_request_id' => "test_merchant_request_id",
        ]);

        $this->artisan('mpesa:query_stk_status')
            ->expectsOutput('Logging successful queries')
            ->assertExitCode(0);

        $this->assertDatabaseHas(MpesaStkCallback::class, ['checkout_request_id' => 'ws_CO_13012021093521236557']);
    }
}
