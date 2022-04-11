<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaStkRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

/**
 * Class StkPushRequestedEvent
 * @package DrH\Mpesa\Events
 */
class StkPushRequestedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var MpesaStkRequest
     */
    public $stk;
    /**
     * @var
     */
    public $request;

    /**
     * StkPushRequestedEvent constructor.
     * @param MpesaStkRequest $mpesaStkRequest
     * @param Request $request
     */
    public function __construct(MpesaStkRequest $mpesaStkRequest, Request $request)
    {
        $this->stk = $mpesaStkRequest;
        $this->request = $request;
    }
}
