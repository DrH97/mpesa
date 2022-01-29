<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaStkRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class StkPushRequestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * StkPushRequestedEvent constructor.
     * @param MpesaStkRequest $mpesaStkRequest
     * @param Request $request
     */
    public function __construct(public MpesaStkRequest $mpesaStkRequest, public Request $request)
    {
    }
}
