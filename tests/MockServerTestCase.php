<?php

namespace DrH\Mpesa\Tests;

use DrH\Mpesa\Library\ApiCore;
use DrH\Mpesa\Library\Core;
use DrH\Mpesa\Repositories\MpesaRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

abstract class MockServerTestCase extends TestCase
{
    protected Core $client;

    protected ApiCore $core;

    protected MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();

        $handlerStack = HandlerStack::create($this->mock);
        $this->client = new Core(new Client(['handler' => $handlerStack]));

        $this->app->bind(Core::class, function () {
            return $this->client;
        });

        $this->core = new ApiCore($this->client, new MpesaRepository());
    }

    protected function addMock(array $expected, int $status = 200)
    {
        $this->mock->append(
            new Response(
                $status,
                ['Content_type' => 'application/json'],
                json_encode($expected)
            )
        );
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
                'errorMessage' => 'Bad Request - invalid credentials'
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
            ],
            'callback' => [
                "transaction_type" => "Pay Bill",
                "trans_id" => "RKTQDM7W6S",
                "trans_time" => "20191122063845",
                "trans_amount" => "10",
                "business_short_code" => "600638",
                "bill_ref_number" => "",
                "invoice_number" => "",
                "org_account_balance" => "49197.00",
                "third_party_trans_id" => "",
                "msisdn" => "254708374149",
                "first_name" => "John",
                "middle_name" => "",
                "last_name" => "Doe"
            ]
        ],
        'b2c' => [
            'request' => [
                "conversation_id" => "AG_20191219_00005797af5d7d75f652",
                "originatorConversation_id" => "16740-34861180-1",
                "response_code" => "0",
                "response_description" => "Accept the service request successfully."
            ],
            'result' => [
                'success' => [
                    "Result" => [
                        "ResultType" => 0,
                        "ResultCode" => 0,
                        "ResultDesc" => "The service request is processed successfully.",
                        "OriginatorConversationID" => "10571-7910404-1",
                        "ConversationID" => "AG_20191219_00004e48cf7e3533f581",
                        "TransactionID" => "NLJ41HAY6Q",
                        "ResultParameters" => [
                            "ResultParameter" => [
                                [
                                    "Key" => "TransactionAmount",
                                    "Value" => 10
                                ],
                                [
                                    "Key" => "TransactionReceipt",
                                    "Value" => "NLJ41HAY6Q"
                                ],
                                [
                                    "Key" => "B2CRecipientIsRegisteredCustomer",
                                    "Value" => "Y"
                                ],
                                [
                                    "Key" => "B2CChargesPaidAccountAvailableFunds",
                                    "Value" => -4510.00
                                ],
                                [
                                    "Key" => "ReceiverPartyPublicName",
                                    "Value" => "254708374149 - John Doe"
                                ],
                                [
                                    "Key" => "TransactionCompletedDateTime",
                                    "Value" => "19.12.2019 11:45:50"
                                ],
                                [
                                    "Key" => "B2CUtilityAccountAvailableFunds",
                                    "Value" => 10116.00
                                ],
                                [
                                    "Key" => "B2CWorkingAccountAvailableFunds",
                                    "Value" => 900000.00
                                ]
                            ]
                        ],
                        "ReferenceData" => [
                            "ReferenceItem" => [
                                "Key" => "QueueTimeoutURL",
                                "Value" => "https:\/\/internalsandbox.safaricom.co.ke\/mpesa\/b2cresults\/v1\/submit"
                            ]
                        ]
                    ]
                ],
                'error' => [
                    "Result" => [
                        "ResultType" => 0,
                        "ResultCode" => 2001,
                        "ResultDesc" => "The initiator information is invalid.",
                        "OriginatorConversationID" => "29112-34801843-1",
                        "ConversationID" => "AG_20191219_00006c6fddb15123addf",
                        "TransactionID" => "NLJ0000000",
                        "ReferenceData" => [
                            "ReferenceItem" => [
                                "Key" => "QueueTimeoutURL",
                                "Value" => "https:\/\/internalsandbox.safaricom.co.ke\/mpesa\/b2cresults\/v1\/submit"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
}
