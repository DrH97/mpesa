<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\StkPushPaymentFailedEvent;

class StkPaymentFailed
{
    /**
     * @param StkPushPaymentFailedEvent $event
     */
    public function handle(StkPushPaymentFailedEvent $event)
    {
        $stk = $event->stkCallback;
        $stk->request()->update(['status' => 'FAILED']);
    }
}
