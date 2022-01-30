<?php

namespace DrH\Mpesa\Tests;

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

        $this->app->bind(Core::class, function () {
            return $this->client;
        });
    }

    protected array $mockResponses = [
        'auth' => [
            'success' => [
                'expiry_in' => 3600,
                'access_token' => 'test_access_token'
            ],
            'error' => [
                'requestId' => '93975-16241949-2',
                'errorCode' => '400.008.01',
                'errorMessage' => 'URLs are already registered'
            ]
        ],
        'stk' => [
            'query' => [
                'success' => [
                    "ResponseCode" => "0",
                    "ResponseDescription" => "The service request has been accepted successsfully",
                    "MerchantRequestID" => "22205-34066-1",
                    "CheckoutRequestID" => "ws_CO_13012021093521236557",
                    "ResultCode" => "0",
                    "ResultDesc" => "The service request is processed successfully."
                ],
                'error' => [
                    "errorCode" => "01",
                    "errorMessage" => "No transaction found"
                ]
            ]
        ],
        'c2b' => [
            'register' => [
                'success' => [
                    "OriginatorCoversationID" => "61349-13392239-2",
                    "ResponseCode" => "0",
                    "ResponseDescription" => "Success"
                ],
                'error' => [
                    'requestId' => '93975-16241949-2',
                    'errorCode' => '400.003.02',
                    'errorMessage' => 'Bad Request - Kindly use your own ShortCode'
                ]
            ]
        ]
    ];
}
