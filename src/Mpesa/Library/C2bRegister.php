<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ClientException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class C2bRegister extends ApiCore
{
    protected int $shortCode;

    protected string $validationURL;

    protected string $confirmationURL;

    protected string $onTimeout = 'Cancelled';

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
     * @throws ClientException
     */
    public function onTimeout(string $onTimeout = 'Cancelled'): self
    {
        if ($onTimeout !== 'Completed' && $onTimeout !== 'Cancelled') {
            throw new ClientException('Invalid timeout argument. Use Completed or Cancelled');
        }
        $this->onTimeout = $onTimeout;
        return $this;
    }

    /**
     * @param string|null $shortCode
     * @param string|null $confirmationURL
     * @param string|null $validationURL
     * @param string|null $onTimeout
     * @return array
     * @throws ClientException
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
        if ($onTimeout && $onTimeout !== 'Completed' && $onTimeout !== 'Cancelled') {
            throw new ClientException('Invalid timeout argument. Use Completed or Cancelled');
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
