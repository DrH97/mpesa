<?php

namespace DrH\Mpesa\Http\Controllers;

use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\Http\Requests\StkRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class StkController extends Controller
{
    /**
     * @param StkRequest $request
     * @return JsonResponse
     */
    public function initiatePush(StkRequest $request): JsonResponse
    {
        try {
            $stk = STK::push($request->amount, $request->phone, $request->reference, $request->description);
        } catch (Exception $exception) {
            $stk = ['ResponseCode' => 900,
                'ResponseDescription' => 'Invalid request', 'extra' => $exception->getMessage()];
        }
        return response()->json($stk);
    }

    /**
     * @param $reference
     * @return JsonResponse
     */
    public function stkStatus($reference): JsonResponse
    {
        return response()->json(STK::validate($reference));
    }
}
