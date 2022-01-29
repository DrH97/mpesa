<?php

namespace DrH\Mpesa\Events;

use Illuminate\Http\Request;

class QueueTimeoutEvent
{
    /**
     * QueueTimeoutEvent constructor.
     * @param Request $request
     * @param string|null $initiator
     */
    public function __construct(public Request $request, public ?string $initiator = null)
    {
    }
}
