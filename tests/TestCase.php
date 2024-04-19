<?php

namespace DrH\Mpesa\Tests;

use DrH\Mpesa\Facades\B2B;
use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\Registrar;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\MpesaServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [MpesaServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'B2B' => B2B::class,
            'B2C' => B2C::class,
            'Identity' => Identity::class,
            'Registrar' => Registrar::class,
            'STK' => STK::class,
        ];
    }
}
