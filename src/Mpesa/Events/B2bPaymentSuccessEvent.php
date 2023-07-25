<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Entities\MpesaB2bCallback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class B2bPaymentSuccessEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public MpesaB2bCallback $mpesaB2bCallback, public array $b2bResult)
    {
    }
}
