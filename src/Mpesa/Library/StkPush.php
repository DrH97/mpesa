<?php

namespace DrH\Mpesa\Library;

use Carbon\Carbon;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\StkPushRequestedEvent;
use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Repositories\Generator;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use function base64_encode;
use function config;
use function count;
use function preg_match;

class StkPush extends ApiCore
{
    protected string $number;

    protected int $amount;

    protected string $reference;

    protected string $description;

    /**
     * @param int $amount
     * @return $this
     * @throws Exception
     */
    public function amount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function from(string $number): self
    {
        $this->number = $this->formatPhoneNumber($number);
        return $this;
    }

    /**
     * Set the mpesa reference
     *
     * @param string $reference
     * @param string $description
     * @return $this
     * @throws ClientException
     */
    public function usingReference(string $reference, string $description): self
    {
        preg_match('/[^A-z0-9]/', $reference, $matches);
        if (count($matches)) {
            throw new ClientException('Reference should be alphanumeric.');
        }
        $this->reference = $reference;
        $this->description = $description;
        return $this;
    }

    /**
     * Send a payment request
     *
     * @param int|null $amount
     * @param string|null $number
     * @param string|null $reference
     * @param string|null $description
     * @param MpesaAccount|null $account
     * @return MpesaStkRequest
     * @throws ExternalServiceException|ClientException
     * @throws GuzzleException
     */
    public function push(
        int          $amount = null,
        string       $number = null,
        string       $reference = null,
        string       $description = null,
        MpesaAccount $account = null
    ): MpesaStkRequest
    {
        $time = Carbon::now()->format('YmdHis');

        if (config('drh.mpesa.multi_tenancy', false) && ($account && !$account->sandbox)) {
            if ($account->passkey == null || $account->shortcode == null) {
                throw new ClientException("Multi Tenancy is enabled but Mpesa Account is null.");
            }

            $shortCode = $account->shortcode;
            $passkey = $account->passkey;

            $transactionType = $account->type == MpesaAccount::TILL
                ? "CustomerBuyGoodsOnline" : "CustomerPayBillOnline";
        } else {
            $shortCode = config('drh.mpesa.c2b.short_code');
            $passkey = config('drh.mpesa.c2b.passkey');

            $transactionType = config('drh.mpesa.c2b.transaction_type');
        }

        $callback = config('drh.mpesa.c2b.stk_callback');

        $partyB = config('drh.mpesa.c2b.party_b');

        $password = base64_encode($shortCode . $passkey . $time);
        $good_phone = $this->formatPhoneNumber($number ?: $this->number);

        $amount = $this->getAmount($amount ?: $this->amount);

        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'TransactionType' => $transactionType,
            'Amount' => $amount,
            'PartyA' => $good_phone,
            'PartyB' => $partyB ?? $shortCode,
            'PhoneNumber' => $good_phone,
            'CallBackURL' => $callback,
            'AccountReference' => $reference ?? $this->reference ?? $good_phone,
            'TransactionDesc' => $description ?? $this->description ?? Generator::generateTransactionNumber(),
        ];

        if (config('drh.mpesa.multi_tenancy', false)) {
            $response = $this->sendRequest($body, 'stk_push', $account);
        } else {
            $response = $this->sendRequest($body, 'stk_push');
        }

        return $this->saveStkRequest($body, $response);
    }

    /**
     * @param array $body
     * @param array $response
     * @return MpesaStkRequest
     * @throws Exception
     * @throws ExternalServiceException
     */
    private function saveStkRequest(array $body, array $response): MpesaStkRequest
    {
        if ($response['ResponseCode'] == 0) {
            $incoming = [
                'phone' => $body['PartyA'],
                'amount' => $body['Amount'],
                'reference' => $body['AccountReference'],
                'description' => $body['TransactionDesc'],
                'checkout_request_id' => $response['CheckoutRequestID'],
                'merchant_request_id' => $response['MerchantRequestID'],
                'relation_id' => @(Auth::id() ?: request('user_id')),
            ];
            $stk = MpesaStkRequest::create($incoming);
            event(new StkPushRequestedEvent($stk, request()));
            return $stk;
        }
        throw new ExternalServiceException($response['ResponseDescription']);
    }

    /**
     * Query a transaction.
     *
     * @param int $stkRequestId
     * @return array
     * @throws GuzzleException
     * @throws ExternalServiceException|ClientException
     */
    public function status(int $stkRequestId): array
    {
        $checkoutRequestId = MpesaStkRequest::find($stkRequestId)->checkout_request_id;

        $time = Carbon::now()->format('YmdHis');
        $shortCode = config('drh.mpesa.c2b.short_code');
        $passkey = config('drh.mpesa.c2b.passkey');
        $password = base64_encode($shortCode . $passkey . $time);
        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'CheckoutRequestID' => $checkoutRequestId,
        ];
        try {
            return $this->sendRequest($body, 'stk_status');
        } catch (RequestException $exception) {
            throw new ExternalServiceException($exception->getMessage());
        }
    }
}
