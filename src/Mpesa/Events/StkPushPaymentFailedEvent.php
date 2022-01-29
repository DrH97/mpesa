<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaStkCallback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StkPushPaymentFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * StkPushPaymentSuccessEvent constructor.
     * @param MpesaStkCallback $stkCallback
     * @param array $apiCallbackData
     */
    public function __construct(public MpesaStkCallback $stkCallback, public array $apiCallbackData = [])
    {
    }
}
