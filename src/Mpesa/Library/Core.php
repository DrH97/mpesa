<?php

namespace DrH\Mpesa\Library;

use GuzzleHttp\ClientInterface;

/**
 * Class Core
 *
 * @package DrH\Mpesa\Library
 */
class Core
{
    /**
     * @var ClientInterface
     */
    public $client;
    /**
     * @var Authenticator
     */
    public $auth;

    /**
     * Core constructor.
     *
     * @param  ClientInterface $client
     * @throws \DrH\Mpesa\Exceptions\MpesaException
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->auth = new Authenticator($this);
    }
}
