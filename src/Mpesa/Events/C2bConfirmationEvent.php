<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaC2bCallback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class C2bConfirmationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * C2BConfirmationEvent constructor.
     * @param MpesaC2bCallback $c2bCallback
     * @param array $apiJsonData
     */
    public function __construct(public MpesaC2bCallback $c2bCallback, public array $apiJsonData = [])
    {
    }
}
