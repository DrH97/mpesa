<?php

namespace DrH\Mpesa\Http\Controllers;

use DrH\Mpesa\Facades\STK;
use DrH\Mpesa\Http\Requests\StkRequest;

/**
 * Class StkController
 * @package DrH\Http\Controllers
 */
class StkController extends Controller
{
    /**
     * @param StkRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePush(StkRequest $request)
    {
        try {
            $stk = STK::request($request->amount)
                ->from($request->phone)
                ->usingReference($request->reference, $request->description)
                ->push();
        } catch (\Exception $exception) {
            $stk = ['ResponseCode' => 900, 'ResponseDescription' => 'Invalid request', 'extra' => $exception->getMessage()];
        }
        return response()->json($stk);
    }

    /**
     * @param $reference
     * @return \Illuminate\Http\JsonResponse
     */
    public function stkStatus($reference)
    {
        return response()->json(STK::validate($reference));
    }
}
