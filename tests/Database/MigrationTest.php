<?php

namespace DrH\Mpesa\Tests\Database;

use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\MpesaServiceProvider;
use Illuminate\Foundation\Application;

class MigrationTest extends \Orchestra\Testbench\TestCase
{
    /** @test */
    public function it_runs_the_migrations()
    {
        MpesaStkRequest::create([
            'phone' => '254722000000',
            'amount' => 70000,
            'reference' => 'Test Case',
            'description' => 'My tests are running',
            'checkout_request_id' => 'ws_CO_02052018230213621',
            'merchant_request_id' => '10054-2753415-2'
        ]);

        $request = MpesaStkRequest::first();

        $this->assertEquals(70000, $request->amount);
        $this->assertEquals('ws_CO_02052018230213621', $request->checkout_request_id);
        $this->assertNotNull($request->created_at);
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(/*['--database' => 'testbench']*/);
        $this->artisan('migrate'/*, ['--database' => 'testbench']*/)->run();
//        Model::unguard();
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [MpesaServiceProvider::class];
    }
}