<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class C2bRegister extends ApiCore
{
    protected int $shortCode;

    protected string $validationURL;

    protected string $confirmationURL;

    protected string $onTimeout = 'Completed';

    public function shortcode(int $shortCode): self
    {
        $this->shortCode = $shortCode;
        return $this;
    }

    public function onValidation(string $validationURL): self
    {
        $this->validationURL = $validationURL;
        return $this;
    }


    public function onConfirmation(string $confirmationURL): self
    {
        $this->confirmationURL = $confirmationURL;
        return $this;
    }

    /**
     * @param string $onTimeout
     * @return $this
     * @throws Exception
     * @throws MpesaException
     */
    public function onTimeout(string $onTimeout = 'Cancelled'): self
    {
        if ($onTimeout !== 'Completed' && $onTimeout !== 'Cancelled') {
            throw new MpesaException('Invalid timeout argument. Use Completed or Cancelled');
        }
        $this->onTimeout = $onTimeout;
        return $this;
    }

    /**
     * @param string|null $shortCode
     * @param string|null $confirmationURL
     * @param string|null $validationURL
     * @param string|null $onTimeout
     * @return mixed
     * @throws MpesaException
     * @throws Exception
     * @throws GuzzleException
     */
    public function submit(
        string $shortCode = null,
        string $confirmationURL = null,
        string $validationURL = null,
        string $onTimeout = null
    ): array
    {
        if ($onTimeout && $onTimeout !== 'Completed' && $onTimeout = 'Cancelled') {
            throw new MpesaException('Invalid timeout argument. Use Completed or Cancelled');
        }
        $body = [
            'ShortCode' => $shortCode ?: $this->shortCode,
            'ResponseType' => $onTimeout ?: $this->onTimeout,
            'ConfirmationURL' => $confirmationURL ?: $this->confirmationURL,
            'ValidationURL' => $validationURL ?: $this->validationURL
        ];
        return $this->sendRequest($body, 'c2b_register_urls');
    }
}
