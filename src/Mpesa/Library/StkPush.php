<?php

namespace DrH\Mpesa\Library;

use Carbon\Carbon;
use DrH\Mpesa\Database\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\StkPushRequestedEvent;
use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Repositories\Generator;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;

/**
 * Class StkPush
 * @package DrH\Mpesa\Library
 */
class StkPush extends ApiCore
{
    /**
     * @var string
     */
    protected $number;
    /**
     * @var int
     */
    protected $amount;
    /**
     * @var string
     */
    protected $reference;
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $amount
     * @return $this
     * @throws \Exception
     * @throws MpesaException
     */
    public function request($amount): self
    {
        if (!\is_numeric($amount)) {
            throw new MpesaException('The amount must be numeric, got ' . $amount);
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function from($number): self
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
     * @throws \Exception
     * @throws MpesaException
     */
    public function usingReference($reference, $description): self
    {
        \preg_match('/[^A-Za-z0-9]/', $reference, $matches);
        if (\count($matches)) {
            throw new MpesaException('Reference should be alphanumeric.');
        }
        $this->reference = $reference;
        $this->description = $description;
        return $this;
    }

    /**
     * Send a payment request
     *
     * @param null|int $amount
     * @param null|string $number
     * @param null|string $reference
     * @param null|string $description
     * @param MpesaAccount|null $account
     * @return mixed
     * @throws MpesaException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function push($amount = null, $number = null, $reference = null, $description = null, MpesaAccount $account = null)
    {
        $time = Carbon::now()->format('YmdHis');

        if (\config('drh.mpesa.multi_tenancy', false) && ($account && !$account->sandbox)) {
            if ($account == null || $account->passkey == null || $account->shortcode == null) {
                throw new MpesaException("Multi Tenancy is enabled but Mpesa Account is null.");
            }

            $shortCode = $account->shortcode;
            $passkey = $account->passkey;

            $transactionType = $account->type == "TILL" ? "CustomerBuyGoodsOnline" : "CustomerPayBillOnline";
        } else {
            $shortCode = \config('drh.mpesa.c2b.short_code');
            $passkey = \config('drh.mpesa.c2b.passkey');

            $transactionType = config('drh.mpesa.c2b.transaction_type');
        }

        $callback = \config('drh.mpesa.c2b.stk_callback');

        $partyB = \config('drh.mpesa.c2b.party_b');

        $password = \base64_encode($shortCode . $passkey . $time);
        $good_phone = $this->formatPhoneNumber($number ?: $this->number);
        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'TransactionType' => $transactionType,
            'Amount' => $amount ?: $this->amount,
            'PartyA' => $good_phone,
            'PartyB' => $partyB ?? $shortCode,
            'PhoneNumber' => $good_phone,
            'CallBackURL' => $callback,
            'AccountReference' => $reference ?? $this->reference ?? $good_phone,
            'TransactionDesc' => $description ?? $this->description ?? Generator::generateTransactionNumber(),
        ];

        if (\config('drh.mpesa.multi_tenancy', false)) {
            $response = $this->sendRequest($body, 'stk_push', $account);
        } else {
            $response = $this->sendRequest($body, 'stk_push');
        }

        return $this->saveStkRequest($body, (array)$response);
    }

    /**
     * @param array $body
     * @param array $response
     * @return MpesaStkRequest|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     * @throws MpesaException
     */
    private function saveStkRequest($body, $response)
    {
        if ($response['ResponseCode'] == 0) {
            $incoming = [
                'phone' => $body['PartyA'],
                'amount' => $body['Amount'],
                'reference' => $body['AccountReference'],
                'description' => $body['TransactionDesc'],
                'CheckoutRequestID' => $response['CheckoutRequestID'],
                'MerchantRequestID' => $response['MerchantRequestID'],
                'user_id' => @(Auth::id() ?: request('user_id')),
            ];
            $stk = MpesaStkRequest::create($incoming);
            event(new StkPushRequestedEvent($stk, request()));
            return $stk;
        }
        throw new MpesaException($response['ResponseDescription']);
    }

    /**
     * Validate an initialized transaction.
     *
     * @param string|int $checkoutRequestID
     *
     * @return mixed
     * @throws MpesaException
     * @throws \Exception
     * @throws GuzzleException
     */
    public function validate($checkoutRequestID)
    {
        if ((int)$checkoutRequestID) {
            $checkoutRequestID = MpesaStkRequest::find($checkoutRequestID)->CheckoutRequestID;
        }
        $time = Carbon::now()->format('YmdHis');
        $shortCode = \config('drh.mpesa.c2b.short_code');
        $passkey = \config('drh.mpesa.c2b.passkey');
        $password = \base64_encode($shortCode . $passkey . $time);
        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'CheckoutRequestID' => $checkoutRequestID,
        ];
        try {
            return $this->sendRequest($body, 'stk_status');
        } catch (RequestException $exception) {
            throw new MpesaException($exception->getMessage());
        }
    }
}
