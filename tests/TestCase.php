<?php

namespace DrH\Tests;

use DrH\Mpesa\Facades\B2C;
use DrH\Mpesa\Facades\Identity;
use DrH\Mpesa\Facades\Registrar;
use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\MpesaServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JetBrains\PhpStorm\ArrayShape;

class TestCase extends \Orchestra\Testbench\TestCase
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

    #[ArrayShape(['B2C' => "string", 'Identity' => "string", 'Registrar' => "string", 'STK' => "string"])]
    protected function getPackageAliases($app): array
    {
        return [
            'B2C' => B2C::class,
            'Identity' => Identity::class,
            'Registrar' => Registrar::class,
            'STK' => STK::class,
        ];
    }
}
