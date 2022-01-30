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
            $stk = STK::request($request->amount)
                ->from($request->phone)
                ->usingReference($request->reference, $request->description)
                ->push();
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
