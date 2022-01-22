<?php

namespace DrH\Tests\Unit;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Library\MpesaAccount;
use DrH\Tests\TestCase;

class StkPushTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('drh.mpesa.sandbox', 'true');
        $app['config']->set('drh.mpesa.multi_tenancy', 'false');
    }

    /** @test */
    public function it_throws_exception_for_invalid_credentials_with_local_sandbox_and_invalid_data()
    {
        $this->expectException(MpesaException::class);

        $acc1 = new MpesaAccount('21212', 'aksldalsd', 'asdasdasf', 'asdasdas', true);

        mpesa_request('0799123456', 1, 'test', 'tests', $acc1);
    }
}
