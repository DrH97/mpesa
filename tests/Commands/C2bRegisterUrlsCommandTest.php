<?php

namespace DrH\Mpesa\Tests\Commands;

use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Tests\MockServerTestCase;

class C2bRegisterUrlsCommandTest extends MockServerTestCase
{
    /** @test */
    function the_command_registers_url_successfully()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['c2b']['register']['success']);

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->expectsOutput('Logging response')
            ->assertExitCode(0);
    }

    /** @test */
    function the_command_throws_on_failure()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['c2b']['register']['error'], 400);

        $this->expectException(ClientException::class);

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->assertFailed();
    }

    /** @test */
    function the_command_logs_response_on_successful_registration()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['c2b']['register']['success']);

        $this->artisan('mpesa:register_c2b_urls')
            ->expectsQuestion('What is your shortcode?', 600995)
            ->expectsQuestion('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'))
            ->expectsQuestion('Validation Url', config('drh.mpesa.c2b.validation_url'))
            ->expectsOutput('Logging response')
            ->assertExitCode(0);
    }

}
