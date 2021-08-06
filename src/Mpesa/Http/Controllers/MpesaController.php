<?php

namespace DrH\Mpesa\Http\Controllers;

use DrH\Mpesa\Events\QueueTimeoutEvent;
use DrH\Mpesa\Repositories\Mpesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MpesaController
 * @package DrH\Http\Controllers
 */
class MpesaController extends Controller
{
    /**
     * @var Mpesa
     */
    private $repository;

    /**
     * MpesaController constructor.
     * @param Mpesa $repository
     */
    public function __construct(Mpesa $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @param string|null $initiator
     * @return JsonResponse
     */
    public function timeout(Request $request, $initiator = null)
    {
        event(new QueueTimeoutEvent($request, $initiator));
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }

    /**
     * @param string|null $initiator
     * @return JsonResponse
     */
    public function result($initiator = null)
    {
        $this->repository->handleResult($initiator);
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }

    /**
     * @param string|null $initiator
     * @return JsonResponse
     */
    public function paymentCallback($initiator)
    {
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmation(Request $request)
    {
        $this->repository->processConfirmation(json_encode($request->all()));
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Confirmation received successfully',
        ];
        return response()->json($resp);
    }

    /**
     * @return JsonResponse
     */
    public function callback()
    {
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Callback received successfully',
        ];
        return response()->json($resp);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stkCallback(Request $request)
    {
        $this->repository->processStkPushCallback(json_encode($request->Body));
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'STK Callback received successfully',
        ];
        return response()->json($resp);
    }

    /**
     * @return JsonResponse
     */
    public function validatePayment()
    {
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Validation passed successfully',
        ];
        return response()->json($resp);
    }
}
