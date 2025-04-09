<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Library\C2bRegister;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

use function config;

class C2bRepository
{
    /**
     * Register constructor.
     * @param C2bRegister $c2bRegister
     */
    public function __construct(private C2bRegister $c2bRegister)
    {
    }

    /**
     * @return mixed
     * @throws ExternalServiceException
     * @throws Exception
     * @throws GuzzleException
     */
    public function doRegister(): mixed
    {
        return $this->c2bRegister
            ->shortcode(config('drh.mpesa.c2b.short_code'))
            ->onConfirmation(config('drh.mpesa.c2b.confirmation_url'))
            ->onValidation(config('drh.mpesa.c2b.validation_url'))
            ->submit();
    }
}
