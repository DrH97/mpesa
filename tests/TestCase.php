<?php

namespace DrH\Tests;

use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\Registrar;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\MpesaServiceProvider;

/**
 * Class TestCase
 * @package DrH\Tests
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [MpesaServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'B2C' => B2C::class,
            'Identity' => Identity::class,
            'Registrar' => Registrar::class,
            'STK' => STK::class,
        ];
    }
}
