<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaBulkPaymentResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class B2cPaymentFailedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    /**
     * B2cPaymentSuccessEvent constructor.
     * @param MpesaBulkPaymentResponse $mpesaBulkPaymentResponse
     * @param array $b2cResult
     */
    public function __construct(public MpesaBulkPaymentResponse $mpesaBulkPaymentResponse, public array $b2cResult)
    {
    }
}
