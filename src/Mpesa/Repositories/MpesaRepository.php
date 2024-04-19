<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Entities\MpesaB2bCallback;
use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaC2bCallback;
use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Entities\MpesaTransactionStatusCallback;
use DrH\Mpesa\Events\B2bPaymentFailedEvent;
use DrH\Mpesa\Events\B2bPaymentSuccessEvent;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use DrH\Mpesa\Events\TransactionStatusFailedEvent;
use DrH\Mpesa\Events\TransactionStatusSuccessEvent;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
            'result_desc' => $data->ResultDesc ?? $data->ResultCode,
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
        } elseif (!is_numeric($data->ResultCode)) {
            // TODO: make this a more general rule for string codes
            $real_data['result_code'] = -1;
            $real_data['result_desc'] .= ' - ' . $data->ResultCode;
        }
        $callback = MpesaStkCallback::create($real_data);
        $this->fireStkEvent($callback, get_object_vars($data));
        return $callback;
    }

    /**
     * @param object $response
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
    public function processC2bConfirmation(array $data): MpesaC2bCallback
    {
        $data = collect($data)->mapWithKeys(function ($value, $key) {
            $key = strtolower(preg_replace(
                '/(?<!^)[A-Z]/',
                '_$0',
                preg_replace('/ID/', 'Id', $key)
            ));

            return [$key => $value];
        })->toArray();

        $data['msisdn'] = $data['m_s_i_s_d_n'];
        unset($data['m_s_i_s_d_n']);

        mpesaLogInfo(' C2B record ', $data);

        $callback = MpesaC2bCallback::create($data);
        event(new C2bConfirmationEvent($callback, $data));

        return $callback;
    }

    /**
     * @return MpesaBulkPaymentResponse
     */
    public function handleB2cResult(): MpesaBulkPaymentResponse
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

//        $data['ResultParameters'] = json_encode($resultParameter);
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

        $response->result()->create($new_params);
    }

    public function handleB2bResult($data): MpesaB2bCallback
    {
//        $data = request('Result');

        //check if data is an array
        if (!is_array($data)) {
            $data->toArray();
        }

        $common = [
            'result_type' => $data['ResultType'],
            'result_code' => $data['ResultCode'],
            'result_desc' => $data['ResultDesc'],
            'originator_conversation_id' => $data['OriginatorConversationID'],
            'transaction_id' => $data['TransactionID']
        ];
        $seek = ['conversation_id' => $data['ConversationID']];

        if ($common['result_code'] !== 0) {
            $response = MpesaB2bCallback::updateOrCreate($seek, $common);
            event(new B2bPaymentFailedEvent($response, [...$common, ...$seek]));
            return $response;
        }
        $resultParameters = $data['ResultParameters'];

        $response = MpesaB2bCallback::updateOrCreate($seek, $common);
        $this->saveB2bResultParams($resultParameters, $response);
        event(new B2bPaymentSuccessEvent($response, [...$common, ...$seek]));
        return $response;
    }

    private function saveB2bResultParams(array $params, MpesaB2bCallback $response): void
    {
        $params_payload = $params['ResultParameter'];
        $new_params = Arr::pluck($params_payload, 'Value', 'Key');

        $toSnakeCase = fn(string $k, ?string $v): array => [
            strtolower(preg_replace(
                '/(?<!^)[A-Z]/',
                '_$0',
                $k
            )) => $v
        ];
        $new_params = array_merge(...array_map($toSnakeCase, array_keys($new_params), array_values($new_params)));

        $response->update($new_params);
    }


    /**
     * @return array
     */
    #[ArrayShape(shape: ['successful' => "array", 'errors' => "array"])]
    public function queryStkStatus(): array
    {
        // TODO: Change this to use WhereStatus = REQUESTED instead
        /** @var MpesaStkRequest[] $stk */
        $stk = MpesaStkRequest::whereDoesntHave('response')
            ->whereStatus('REQUESTED')
            ->whereDate('created_at', '>', Carbon::today()->subMonths(3))
            ->get();
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

                if (!is_numeric($status->ResultCode)) {
                    $attributes['result_code'] = -1;
                    $attributes['result_desc'] .= ' - ' . $status->ResultCode;
                }

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


    public function queryB2cStatus(): array
    {
        $bulk = MpesaBulkPaymentResponse::whereDoesntHave('result')->get();
        $transactions = [];
        foreach ($bulk as $item) {
            try {
                $status = (object)mpesa_b2c_status($item->transaction_id);
                $transactions[$item->transaction_id] = $status->ResponseDescription;
            } catch (Exception $e) {
                $transactions[$item->transaction_id] = $e->getMessage();
            }
        }
        return $transactions;
    }


    public function queryB2bStatus(): array
    {
        $b2b = MpesaB2bRequest::whereDoesntHave('response')->get();
        $transactions = [];
        foreach ($b2b as $item) {
            try {
                $status = mpesa_b2b_status($item);
                $transactions[$item->conversation_id] = $status->response_description;
            } catch (Exception $e) {
                $transactions[$item->conversation_id] = $e->getMessage();
            }
        }
        return $transactions;
    }

    public function processStatusCallback(): MpesaTransactionStatusCallback
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
            'originator_conversation_id' => $data['OriginatorConversationID'],
            'transaction_id' => $data['TransactionID']
        ];
        $seek = ['conversation_id' => $data['ConversationID']];

        if ($common['result_code'] !== 0) {
            $response = MpesaTransactionStatusCallback::updateOrCreate($seek, $common);
            event(new TransactionStatusFailedEvent($response, $data));
            return $response;
        }
        $resultParameters = $data['ResultParameters'];

        $callback = MpesaTransactionStatusCallback::updateOrCreate($seek, $common);

        $this->saveStatusResultParams($resultParameters, $callback);
        event(new TransactionStatusSuccessEvent($callback, $data));

        $this->processForB2b($callback);

        return $callback;
    }

    private function saveStatusResultParams(array $params, MpesaTransactionStatusCallback $response): void
    {
        $params_payload = $params['ResultParameter'];
        $new_params = Arr::pluck($params_payload, 'Value', 'Key');

        $toSnakeCase = fn(string $k, ?string $v): array => [
            strtolower(preg_replace(
                '/(?<!^)[A-Z]/',
                '_$0',
                $k
            )) => $v
        ];
        $new_params = array_merge(...array_map($toSnakeCase, array_keys($new_params), array_values($new_params)));

        $new_params['result_originator_conversation_id'] = $new_params['originator_conversation_i_d'];
        $new_params['result_conversation_id'] = $new_params['conversation_i_d'];
        unset($new_params['originator_conversation_i_d'], $new_params['conversation_i_d']);

        $response->update($new_params);
    }

    private function processForB2b(MpesaTransactionStatusCallback $callback): void
    {
        $b2bData = [
            "ResultType" => 0,
            "ResultCode" => 0,
            "ResultDesc" => "The service request is processed successfully.",
            "OriginatorConversationID" => $callback->result_originator_conversation_id,
            "ConversationID" => $callback->result_conversation_id,
            "TransactionID" => $callback->receipt_no,
            "ResultParameters" => [
                "ResultParameter" => [
                    [
                        "Key" => "DebitAccountCurrentBalance",
                        "Value" => "{Amount={CurrencyCode=KES,MinimumAmount=0,BasicAmount=0.0}}"
                    ],
                    [
                        "Key" => "Amount",
                        "Value" => $callback->amount
                    ],
                    [
                        "Key" => "DebitAccountBalance",
                        "Value" => "Working Account|KES|0.0|0.0|0.00|0.00"
                    ],
                    [
                        "Key" => "TransCompletedTime",
                        "Value" => $callback->finalised_time
                    ],
                    [
                        "Key" => "DebitPartyCharges",
                        "Value" => ""
                    ],
                    [
                        "Key" => "ReceiverPartyPublicName",
                        "Value" => $callback->credit_party_name
                    ],
                    [
                        "Key" => "Currency",
                        "Value" => "KES"
                    ],
                    [
                        "Key" => "InitiatorAccountCurrentBalance",
                        "Value" => "{Amount={CurrencyCode=KES,MinimumAmount=0,BasicAmount=0.0}}"
                    ]
                ]
            ],
        ];

        $this->handleB2bResult($b2bData);
    }
}
