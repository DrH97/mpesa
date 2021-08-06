<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Library\RegisterUrl;

/**
 * Class Register
 * @package DrH\Mpesa\Repositories
 */
class Register
{
    /**
     * @var RegisterUrl
     */
    private $registra;

    /**
     * Register constructor.
     * @param RegisterUrl $registerUrl
     */
    public function __construct(RegisterUrl $registerUrl)
    {
        $this->registra = $registerUrl;
    }

    /**
     * @return mixed
     * @throws \DrH\Mpesa\Exceptions\MpesaException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doRegister()
    {
        return $this->registra->register(\config('drh.mpesa.c2b.short_code'))
            ->onConfirmation(\config('drh.mpesa.c2b.confirmation_url'))
            ->onValidation(\config('drh.mpesa.c2b.validation_url'))
            ->submit();
    }
}
