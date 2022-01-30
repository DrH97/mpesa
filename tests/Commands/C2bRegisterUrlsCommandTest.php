<?php

namespace DrH\Mpesa\Tests\Commands;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;

class C2bRegisterUrlsCommandTest extends MockServerTestCase
{
    /** @test */
    function the_command_registers_url_successfully()
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
                json_encode($this->mockResponses['c2b']['register']['success'])
            )
        );

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->expectsOutput('Logging response')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_throws_on_failure_()
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
                json_encode($this->mockResponses['c2b']['register']['error'])
            )
        );

        $this->expectException(MpesaException::class);

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->assertFailed();
    }

    /** @test */
    function the_command_logs_response_on_successful_registration()
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
                json_encode($this->mockResponses['c2b']['register']['success'])
            )
        );

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->expectsOutput('Logging response')
            ->assertExitCode(0);
    }

}
