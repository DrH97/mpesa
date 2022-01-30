<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Database\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Database\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Database\Entities\MpesaC2bCallback;
use DrH\Mpesa\Database\Entities\MpesaStkCallback;
use DrH\Mpesa\Database\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;

class MpesaRepository
{
//    /**
//     * @param string $json
//     * @return MpesaStkCallback
//     */
//    public function processStkPushCallback(string $json): MpesaStkCallback
//    {
//        $object = json_decode($json);
//        $data = $object->stkCallback;
//        $real_data = [
//            'MerchantRequestID' => $data->MerchantRequestID,
//            'CheckoutRequestID' => $data->CheckoutRequestID,
//            'ResultCode' => $data->ResultCode,
//            'ResultDesc' => $data->ResultDesc,
//        ];
//        if ($data->ResultCode == 0) {
//            $_payload = $data->CallbackMetadata->Item;
//            foreach ($_payload as $callback) {
//                $real_data[$callback->Name] = @$callback->Value;
//            }
//            $callback = MpesaStkCallback::create($real_data);
//        } else {
//            $callback = MpesaStkCallback::create($real_data);
//        }
//        $this->fireStkEvent($callback, get_object_vars($data));
//        return $callback;
//    }

    /**
     * @param $response
     * @param array $body
     * @return MpesaBulkPaymentRequest
     */
    public function saveB2cRequest($response, array $body = []): MpesaBulkPaymentRequest
    {
        return MpesaBulkPaymentRequest::create([
            'conversation_id' => $response->ConversationID,
            'originator_conversation_id' => $response->OriginatorConversationID,
            'amount' => $body['Amount'],
            'phone' => $body['PartyB'],
            'remarks' => $body['Remarks'],
            'command_id' => $body['CommandID'],
            'relation_id' => Auth::id(),
        ]);
    }

    /**
     * @param string $json
     * @return MpesaC2bCallback
     */
    public function processConfirmation(string $json): MpesaC2bCallback
    {
        $data = json_decode($json, true);
        $callback = MpesaC2bCallback::create($data);
        event(new C2bConfirmationEvent($callback, $data));
        return $callback;
    }

    /**
     * @return MpesaBulkPaymentResponse
     */
    private function handleB2cResult(): MpesaBulkPaymentResponse
    {
        $data = request('Result');

        //check if data is an array
        if (!is_array($data)) {
            $data->toArray();
        }

        $common = [
            'result_type' => $data['ResultType'],
            'result_code' => $data['ResultCode'],
            'result_desc' => $data['ResultDesc'],
            'conversation_id' => $data['ConversationID'],
            'transaction_id' => $data['TransactionID']
        ];
        $seek = ['originator_conversation_id' => $data['OriginatorConversationID']];

        if ($data['ResultCode'] !== 0) {
            $response = MpesaBulkPaymentResponse::updateOrCreate($seek, $common);
            event(new B2cPaymentFailedEvent($response, $data));
            return $response;
        }
        $resultParameter = $data['ResultParameters'];

        $data['ResultParameters'] = json_encode($resultParameter);
        $response = MpesaBulkPaymentResponse::updateOrCreate($seek, Arr::except($data, ['ReferenceData']));
        $this->saveResultParams($resultParameter, $response);
        event(new B2cPaymentSuccessEvent($response, $data));
        return $response;
    }

    private function saveResultParams(array $params, MpesaBulkPaymentResponse $response): void
    {
        $params_payload = $params['ResultParameter'];
        $new_params = Arr::pluck($params_payload, 'Value', 'Key');
        $response->resultParams()->create($new_params);
    }

    /**
     * @param string|null $initiator
     * @return MpesaBulkPaymentResponse|void
     */
    public function handleResult(string $initiator = null)
    {
        if ($initiator === 'b2c') {
            return $this->handleB2cResult();
        }
        return;
    }

    /**
     * @return array
     */
    #[ArrayShape(shape: ['successful' => "array", 'errors' => "array"])]
    public function queryStkStatus(): array
    {
        /** @var MpesaStkRequest[] $stk */
        $stk = MpesaStkRequest::whereDoesntHave('response')->get();
        $success = $errors = [];
        foreach ($stk as $item) {
            try {
                $status = (object)mpesa_stk_status($item->id);
                if (isset($status->errorMessage)) {
                    $errors[$item->checkout_request_id] = $status->errorMessage;
                    continue;
                }
                $attributes = [
                    'merchant_request_id' => $status->MerchantRequestID,
                    'checkout_request_id' => $status->CheckoutRequestID,
                    'result_code' => $status->ResultCode,
                    'result_desc' => $status->ResultDesc,
                    'amount' => $item->amount,
                ];
                $errors[$item->checkout_request_id] = $status->ResultDesc;
                $callback = MpesaStkCallback::create($attributes);
                $this->fireStkEvent($callback, get_object_vars($status));
            } catch (Exception $e) {
                $errors[$item->checkout_request_id] = $e->getMessage();
            }
        }
        return ['successful' => $success, 'errors' => $errors];
    }

    /**
     * @param MpesaStkCallback $stkCallback
     * @param array $response
     * @return void
     */
    private function fireStkEvent(MpesaStkCallback $stkCallback, array $response): void
    {
        if ($stkCallback->result_code == 0) {
            event(new StkPushPaymentSuccessEvent($stkCallback, $response));
        } else {
            event(new StkPushPaymentFailedEvent($stkCallback, $response));
        }
    }
}
