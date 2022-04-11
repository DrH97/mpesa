<?php

namespace DrH\Mpesa\Tests\Library;

use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Tests\MockServerTestCase;

class ApiCoreTest extends MockServerTestCase
{
    /** @test */
    function formats_phone_numbers_correctly()
    {
        $testArr = [
            "+254700000000" => "254700000000",
            "254750000000" => "254750000000",
            "0730000000" => "254730000000",
            "762000000" => "254762000000",
            "+254100000000" => "254100000000",
            "0130000000" => "254130000000",
            "162000000" => "254162000000",
        ];

        foreach ($testArr as $key => $value) {
            $no = $this->core->formatPhoneNumber($key);

            $this->assertEquals($value, $no);
        }
    }

    /** @test */
    function send_request_successfully()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['stk']['query']['success']);

        $req = $this->core->sendRequest([], 'stk_status');

        $this->assertIsArray($req);
        $this->assertEquals(0, $req['ResponseCode']);
    }

    /** @test */
    function throws_on_authentication_failure()
    {
        $this->addMock($this->mockResponses['auth']['error'], 400);

        $this->expectException(ClientException::class);

        $this->core->sendRequest([], 'stk_status');
    }

    /** @test */
    function throws_on_request_failure()
    {
        $this->addMock($this->mockResponses['auth']['success']);
        $this->addMock($this->mockResponses['stk']['query']['error'], 400);

        $this->expectException(ClientException::class);

        $this->core->sendRequest([], 'stk_status');
    }

//    TODO: Add test for trials (connect exception)
//    TODO: Add tests for multi_tenancy
}
