<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use function config;

class BulkSender extends ApiCore
{
    private string $number;

    private int $amount;

    private string $remarks = 'Some remarks';

    /**
     * Set number to receive the funds
     *
     * @param string $number
     * @return $this
     */
    public function to(string $number): self
    {
        $this->number = $this->formatPhoneNumber($number);
        return $this;
    }

    public function withRemarks($remarks): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * The amount to transact
     *
     * @param int $amount
     * @return $this
     */
    public function amount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string|null $number
     * @param int|null $amount
     * @param string|null $remarks
     * @return MpesaBulkPaymentRequest
     * @throws MpesaException
     * @throws GuzzleException
     */
    public function send(string $number = null, int $amount = null, string $remarks = null): MpesaBulkPaymentRequest
    {
        $body = [
            'InitiatorName' => config('drh.mpesa.b2c.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2c.security_credential'),
            'CommandID' => 'BusinessPayment', //SalaryPayment,BusinessPayment,PromotionPayment
            'Amount' => $amount ?: $this->amount,
            'PartyA' => config('drh.mpesa.b2c.short_code'),
            'PartyB' => $this->formatPhoneNumber($number ?: $this->number),
            'Remarks' => $remarks ?: $this->remarks,
            'QueueTimeOutURL' => config('drh.mpesa.b2c.timeout_url') . 'b2c',
            'ResultURL' => config('drh.mpesa.b2c.result_url') . 'b2c',
            'Occasion' => ' '
        ];
        $this->bulk = true;

        $response = $this->sendRequest($body, 'b2c');
        return $this->mpesaRepository->saveB2cRequest((object)$response, $body);
    }

    /**
     * @return mixed
     * @throws MpesaException
     * @throws Exception
     * @throws GuzzleException
     */
    public function balance(): mixed
    {
        $body = [
            'CommandID' => 'AccountBalance',
            'Initiator' => config('drh.mpesa.bulk.initiator'),
            'SecurityCredential' => config('drh.mpesa.bulk.security_credential'),
            'PartyA' => config('drh.mpesa.bulk.short_code'),
            'IdentifierType' => 4,
            'Remarks' => 'Checking Balance',
            'QueueTimeOutURL' => config('drh.mpesa.bulk.timeout_url') . 'bulk_balance',
            'ResultURL' => config('drh.mpesa.bulk.result_url') . 'bulk_balance',
        ];
        $this->bulk = true;
        return $this->sendRequest($body, 'account_balance');
    }
}
