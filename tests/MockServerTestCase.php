<?php

namespace DrH\Tests;

use DrH\Mpesa\Library\Core;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class MockServerTestCase extends TestCase
{
    use RefreshDatabase;

    protected Core $client;

    protected MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

//        Config::set('tanda.client_id', 'somethinggoeshere');
//        Config::set('tanda.client_secret', 'somethinggoeshere');
//        Config::set('tanda.organization_id', 'somethinggoeshere');

        $this->mock = new MockHandler();

        $handlerStack = HandlerStack::create($this->mock);
        $this->client = new Core(new Client(['handler' => $handlerStack]));
    }
}
