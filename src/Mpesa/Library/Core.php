<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ClientException;
use GuzzleHttp\ClientInterface;

class Core
{
    public Authenticator $auth;

    /**
     * Core constructor.
     *
     * @param ClientInterface $client
     * @throws ClientException
     */
    public function __construct(public ClientInterface $client)
    {
        $this->auth = new Authenticator($this);
    }
}
