<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Entities\MpesaTransactionStatusRequest;
use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Exceptions\ExternalServiceException;
use GuzzleHttp\Exception\GuzzleException;

class B2BPayment extends ApiCore
{
    public const PAYBILL = 'BusinessPayBill';
    public const TILL = 'BusinessBuyGoods';
    public const STORE = 'BusinessToBusinessTransfer';

    /**
     * @throws GuzzleException
     * @throws ClientException
     * @throws ExternalServiceException
     */
    public function pay(string $type, string $shortcode, int $amount, string $reference, string $phone): MpesaB2bRequest
    {
        $body = [
            'Initiator' => config('drh.mpesa.b2b.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2b.security_credential'),
            'CommandID' => $type,
            "SenderIdentifierType" => 4,
            "RecieverIdentifierType" => $type === self::TILL ? 2 : 4,
            'Amount' => $amount,
            'PartyA' => config('drh.mpesa.b2b.short_code'),
            'PartyB' => $shortcode,
            'AccountReference' => $reference,
            'Requester' => $phone,
            'Remarks' => 'ok',
            'QueueTimeOutURL' => config('drh.mpesa.b2b.timeout_url') . 'b2b',
            'ResultURL' => config('drh.mpesa.b2b.result_url') . 'b2b',
        ];

        $this->service = 'b2b';

        $response = $this->sendRequest($body, 'b2b');
        return $this->saveB2bRequest((object)$response, $body);
    }


    /**
     * @param object $response
     * @param array $body
     * @return MpesaB2bRequest
     */
    private function saveB2bRequest(object $response, array $body = []): MpesaB2bRequest
    {
        return MpesaB2bRequest::create([
            'command_id' => $body['CommandID'],
            'party_a' => $body['PartyA'],
            'party_b' => $body['PartyB'],
            'requester' => $body['Requester'],
            'amount' => $body['Amount'],
            'account_reference' => $body['AccountReference'],
//            'remarks'                    => $body['Remarks'],
            'conversation_id' => $response->ConversationID,
            'originator_conversation_id' => $response->OriginatorConversationID,
            'response_code' => $response->ResponseCode,
            'response_description' => $response->ResponseDescription,
        ]);
    }


    /**
     * @throws GuzzleException
     * @throws ClientException
     * @throws ExternalServiceException
     */
    public function status(MpesaB2bRequest $request): MpesaB2bRequest
    {
        $body = [
            'CommandID' => 'TransactionStatusQuery',
            'Initiator' => config('drh.mpesa.b2b.initiator'),
            'SecurityCredential' => config('drh.mpesa.b2b.security_credential'),
            'PartyA' => config('drh.mpesa.b2b.short_code'),
            "IdentifierType" => 4,
            'ConversationID' => $request->conversation_id,
            'OriginalConversationID' => $request->originator_conversation_id,
            'Remarks' => 'ok',
            'QueueTimeOutURL' => config('drh.mpesa.b2b.timeout_url') . 'b2b/status',
            'ResultURL' => config('drh.mpesa.b2b.result_url') . 'b2b/status',
        ];

        $this->service = 'b2b';

        $response = $this->sendRequest($body, 'transaction_status');
        $this->saveTransactionStatusRequest((object)$response, $request);
        return $request;
    }

    /**
     * @param object $response
     * @param MpesaB2bRequest $request
     * @return MpesaTransactionStatusRequest
     */
    private function saveTransactionStatusRequest(
        object $response,
        MpesaB2bRequest $request
    ): MpesaTransactionStatusRequest {
        return MpesaTransactionStatusRequest::create([
            'conversation_id' => $response->ConversationID,
            'originator_conversation_id' => $response->OriginatorConversationID,
            'response_code' => $response->ResponseCode,
            'response_description' => $response->ResponseDescription,
            'relation_type' => $this->service,
            'relation_id' => $request->id,
        ]);
    }
}
