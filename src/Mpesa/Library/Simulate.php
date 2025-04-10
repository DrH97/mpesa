<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ExternalServiceException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

use function config;
use function json_decode;

class Simulate extends ApiCore
{
    private int $amount;

    private string $number;

    private string $command;

    private string $reference = 'Testing';

    /**
     * Set the request amount to be deducted.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function request(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Set the Mobile Subscriber Number to deduct the amount from.
     * Must be in format 2547XXXXXXXX.
     *
     * @param string $number
     *
     * @return $this
     * @throws \DrH\Mpesa\Exceptions\ClientException
     */
    public function from(string $number): self
    {
        if (!str_starts_with($number, '2547')) {
            throw new \DrH\Mpesa\Exceptions\ClientException('The subscriber number must start with 2547');
        }
        $this->number = $number;
        return $this;
    }

    /**
     * Set the product reference number to bill the account.
     *
     * @param string $reference
     *
     * @return $this
     */
    public function usingReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Set the unique command for this transaction type.
     *
     * @param string|null $command
     *
     * @return $this
     */
    public function setCommand(?string $command): self
    {
        $this->command = $command ?? config('drh.mpesa.c2b.transaction_type') ?? "CustomerPayBillOnline";
        return $this;
    }

    /**
     * Prepare the transaction simulation request
     *
     * @param string|null $number
     * @param int|null $amount
     * @param string|null $reference
     * @param string|null $command
     * @return mixed
     * @throws ExternalServiceException
     * @throws GuzzleException|\DrH\Mpesa\Exceptions\ClientException
     */
    public function push(
        string $number = null,
        int $amount = null,
        string $reference = null,
        string $command = null
    ): array {
        if (!config('drh.mpesa.sandbox', true)) {
            throw new \DrH\Mpesa\Exceptions\ClientException('Cannot simulate a transaction in the live environment.');
        }
        $shortCode = config('drh.mpesa.c2b.short_code');
        $good_phone = $this->formatPhoneNumber($number ?: $this->number);

        $amount = $this->getAmount($amount ?: $this->amount);

        $body = [
            'CommandID' => $command ?? $this->command ?? config('drh.mpesa.c2b.transaction_type'),
            'Amount' => $amount,
            'Msisdn' => $good_phone,
            'ShortCode' => $shortCode,
            'BillRefNumber' => $reference ?: $this->reference,
        ];
        try {
            return $this->sendRequest($body, 'simulate');
        } catch (ClientException $exception) {
            return json_decode($exception->getResponse()->getBody(), true);
        }
    }
}
