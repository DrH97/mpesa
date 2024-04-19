<?php

namespace DrH\Mpesa\Http\Controllers;

use DrH\Mpesa\Events\QueueTimeoutEvent;
use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    /**
     * MpesaController constructor.
     * @param MpesaRepository $repository
     */
    public function __construct(private MpesaRepository $repository)
    {
    }

    ######################################################################################
    #   B2B Callbacks

    /**
     * @param Request $request
     * @param string|null $initiator
     * @return JsonResponse
     */
    public function b2bTimeout(Request $request, string $initiator = null): JsonResponse
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
     * @param Request $request
     * @return JsonResponse
     */
    public function b2bResult(Request $request): JsonResponse
    {
        mpesaLogInfo('B2B CB: ', $request->all());

        $this->repository->handleB2bResult($request->Result);
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }

    #   B2C Callbacks
    ######################################################################################

    ######################################################################################
    #   B2C Callbacks

    /**
     * @param Request $request
     * @param string|null $initiator
     * @return JsonResponse
     */
    public function b2cTimeout(Request $request, string $initiator = null): JsonResponse
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
     * @param Request $request
     * @return JsonResponse
     */
    public function b2cResult(Request $request): JsonResponse
    {
        mpesaLogInfo('B2C CB: ', $request->all());

        $this->repository->handleB2cResult();
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }

    #   B2C Callbacks
    ######################################################################################


    ######################################################################################
    #   STK Callbacks

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stkCallback(Request $request): JsonResponse
    {
        mpesaLogInfo('STK CB: ', $request->all());

        // TODO: Add to try catch and always return success response - do for all entry points
        $this->repository->processStkPushCallback(json_encode($request->Body));
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'STK Callback received successfully',
        ];
        return response()->json($resp);
    }

    #   STK Callbacks
    ######################################################################################


    ######################################################################################
    #   C2B Callbacks

    /**
     * @return JsonResponse
     */
    public function c2bValidation(): JsonResponse
    {
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Validation passed successfully',
        ];
        return response()->json($resp);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function c2bConfirmation(Request $request): JsonResponse
    {
        mpesaLogInfo('C2B CB: ', $request->all());

        $this->repository->processC2bConfirmation($request->all());
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Confirmation received successfully',
        ];
        return response()->json($resp);
    }

    #   C2B Callbacks
    ######################################################################################

    ######################################################################################
    #   Status Callbacks

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function statusCallback(Request $request): JsonResponse
    {
        mpesaLogInfo('Status CB: ', $request->all());

        $this->repository->processStatusCallback();
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Status Callback received successfully',
        ];
        return response()->json($resp);
    }

    #   Status Callbacks
    ######################################################################################
}
