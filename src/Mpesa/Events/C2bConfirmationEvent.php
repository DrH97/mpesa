<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaC2bCallback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class C2BConfirmationEvent
 * @package DrH\Mpesa\Events
 */
class C2bConfirmationEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var MpesaC2bCallback
     */
    public $transaction;
    /**
     * @var array
     */
    public $mpesa_response;

    /**
     * C2BConfirmationEvent constructor.
     * @param MpesaC2bCallback $c2bCallback
     * @param array $response
     */
    public function __construct(MpesaC2bCallback $c2bCallback, array $response = [])
    {
        $this->transaction = $c2bCallback;
        $this->mpesa_response = $response;
    }
}
