<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;

/**
 * Class StkPaymentSuccessful
 * @package DrH\Listeners
 */
class StkPaymentSuccessful
{
    /**
     * @param StkPushPaymentSuccessEvent $event
     */
    public function handle(StkPushPaymentSuccessEvent $event)
    {
        /** @var \DrH\Mpesa\Database\Entities\MpesaStkCallback $stk */
        $stk = $event->stk_callback;
        $stk->request()->update(['status' => 'Paid']);
    }
}
