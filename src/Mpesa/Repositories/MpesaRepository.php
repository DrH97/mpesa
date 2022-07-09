<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaC2bCallback;
use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MpesaRepository
{
    /**
     * @param string $json
     * @return MpesaStkCallback
     */
    public function processStkPushCallback(string $json): MpesaStkCallback
    {
        $object = json_decode($json);
        $data = $object->stkCallback;
        $real_data = [
            'merchant_request_id' => $data->MerchantRequestID,
            'checkout_request_id' => $data->CheckoutRequestID,
            'result_code' => $data->ResultCode,
            'result_desc' => $data->ResultDesc,
        ];
        if ($data->ResultCode == 0) {
            $_payload = $data->CallbackMetadata->Item;
            foreach ($_payload as $callback) {
                if ($callback->Name == "PhoneNumber") {
                    $real_data['phone'] = @$callback->Value;
                } else {
                    $real_data[Str::snake($callback->Name)] = @$callback->Value;
                }
            }
            $callback = MpesaStkCallback::create($real_data);
        } else {
            $callback = MpesaStkCallback::create($real_data);
        }
        $this->fireStkEvent($callback, get_object_vars($data));
        return $callback;
    }

    /**
     * @param $response
     * @param array $body
     * @return MpesaBulkPaymentRequest
     */
    public function saveB2cRequest(object $response, array $body = []): MpesaBulkPaymentRequest
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
    public function processC2bConfirmation(string $json): MpesaC2bCallback
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

        if ($common['result_code'] !== 0) {
            $response = MpesaBulkPaymentResponse::updateOrCreate($seek, $common);
            event(new B2cPaymentFailedEvent($response, [...$common, ...$seek]));
            return $response;
        }
        $resultParameter = $data['ResultParameters'];

        $data['ResultParameters'] = json_encode($resultParameter);
        $response = MpesaBulkPaymentResponse::updateOrCreate($seek, $common);
        $this->saveResultParams($resultParameter, $response);
        event(new B2cPaymentSuccessEvent($response, [...$common, ...$seek]));
        return $response;
    }

    private function saveResultParams(array $params, MpesaBulkPaymentResponse $response): void
    {
        $params_payload = $params['ResultParameter'];
        $new_params = Arr::pluck($params_payload, 'Value', 'Key');

        $toSnakeCase = fn(string $k, string $v): array => [
            strtolower(preg_replace(
                '/(?<!^)[A-Z]/',
                '_$0',
                preg_replace('/^B2C/', 'b2c', $k)
            )) => $v
        ];
        $new_params = array_merge(...array_map($toSnakeCase, array_keys($new_params), array_values($new_params)));

        $response->resultParameter()->create($new_params);
    }

    /**
     * @param string|null $initiator
     * @return MpesaBulkPaymentResponse
     */
    public function handleResult(): MpesaBulkPaymentResponse
    {
        return $this->handleB2cResult();
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
                $success[$item->checkout_request_id] = $status->ResultDesc;
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
