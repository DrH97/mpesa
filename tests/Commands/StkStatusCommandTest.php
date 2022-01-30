<?php

namespace DrH\Mpesa\Tests\Commands;

use Carbon\Carbon;

class StkStatusCommandTest extends MockServerTestCase
{
    use RefreshDatabase;

    /** @test */
    function the_command_returns_nothing_text_when_no_pending_stk_requests()
    {
        $this->artisan('tanda:query_status')
            ->expectsOutput('Nothing to query... all transactions seem to be ok.')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_echoes_failed_queries()
    {
        TandaRequest::create([
            'request_id' => 'd33d079c-6bf2-430f-a1c9-d3cf45f8671a',
            'status' => 000001,
            'message' => 'Request received successfully.',
            'command_id' => Commands::AIRTIME_COMMAND,
            'provider' => Providers::SAFARICOM,
            'destination' => '234123',
            'amount' => 10,
            'last_modified' => Carbon::now(),
        ]);

        $this->artisan('tanda:query_status')
            ->expectsOutput('Failed queries: ')
            ->assertExitCode(0);
    }
}
