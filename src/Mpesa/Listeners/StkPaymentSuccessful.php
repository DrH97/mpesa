<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;

class StkPaymentSuccessful
{
    /**
     * @param StkPushPaymentSuccessEvent $event
     */
    public function handle(StkPushPaymentSuccessEvent $event)
    {
        $stk = $event->stkCallback;
        $stk->request()->update(['status' => 'PAID']);
    }
}
