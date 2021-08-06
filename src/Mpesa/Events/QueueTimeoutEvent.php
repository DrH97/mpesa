<?php

namespace DrH\Mpesa\Events;

use Illuminate\Http\Request;

/**
 * Class QueueTimeoutEvent
 * @package DrH\Events
 */
class QueueTimeoutEvent
{
    /**
     * @var Request
     */
    public Request $request;
    /**
     * @var string
     */
    public ?string $initiator;

    /**
     * QueueTimeoutEvent constructor.
     * @param Request $request
     * @param string|null $initiator
     */
    public function __construct(Request $request, string $initiator = null)
    {
        $this->request = $request;
        $this->initiator = $initiator;
    }
}
