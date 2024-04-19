<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Exceptions\ExternalServiceException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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
     * @throws ExternalServiceException
     * @throws GuzzleException|ClientException
     */
    public function send(string $number = null, int $amount = null, string $remarks = null): MpesaBulkPaymentRequest
    {
        $amount = $this->getAmount($amount ?: $this->amount);

        $body = [
            'CommandID' => 'BusinessPayment', //SalaryPayment,BusinessPayment,PromotionPayment
            'InitiatorName' => config('drh.mpesa.b2c.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2c.security_credential'),
            'PartyA' => config('drh.mpesa.b2c.short_code'),
            'PartyB' => $this->formatPhoneNumber($number ?: $this->number),
            'Amount' => $amount,
            'Remarks' => $remarks ?: $this->remarks,
            'QueueTimeOutURL' => config('drh.mpesa.b2c.timeout_url') . 'b2c',
            'ResultURL' => config('drh.mpesa.b2c.result_url') . 'b2c',
            'Occasion' => ' '
        ];
        $this->service = 'b2c';

        $response = $this->sendRequest($body, 'b2c');
        return $this->mpesaRepository->saveB2cRequest((object)$response, $body);
    }

    /**
     * @return array
     * @throws ExternalServiceException
     * @throws Exception
     * @throws GuzzleException
     */
    public function balance(): array
    {
        $body = [
            'CommandID' => 'AccountBalance',
            'Initiator' => config('drh.mpesa.b2c.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2c.security_credential'),
            'PartyA' => config('drh.mpesa.b2c.short_code'),
            'IdentifierType' => 4,
            'Remarks' => 'Checking Balance',
            'QueueTimeOutURL' => config('drh.mpesa.b2c.timeout_url') . 'b2c/balance',
            'ResultURL' => config('drh.mpesa.b2c.result_url') . 'b2c/balance',
        ];
        $this->bulk = true;
        return $this->sendRequest($body, 'account_balance');
    }


    /**
     * Query a transaction.
     *
     * @param string $transactionId
     * @return array
     * @throws ClientException
     * @throws ExternalServiceException
     * @throws GuzzleException
     */
    public function status(string $transactionId): array
    {
        // TODO: Should add index on transaction id, and since mpesa claim it is unique, a unique index?
        // TODO: Add a check to ensure only pending are fetched
        $transactionId = MpesaBulkPaymentResponse::whereTransactionId($transactionId)->first()->transaction_id;

        $body = [
            'CommandID' => 'TransactionStatusQuery',
            'Initiator' => config('drh.mpesa.b2c.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2c.security_credential'),
            'PartyA' => config('drh.mpesa.b2c.short_code'),
            'IdentifierType' => 1,
            'TransactionID' => $transactionId,
            'ResultURL' => config('drh.mpesa.b2c.result_url') . 'b2c/status',
            'QueueTimeOutURL' => config('drh.mpesa.b2c.timeout_url') . 'b2c/status',
            'Remarks' => 'ok',
        ];
        try {
            return $this->sendRequest($body, 'transaction_status');
        } catch (RequestException $exception) {
            throw new ExternalServiceException($exception->getMessage());
        }
    }
}
