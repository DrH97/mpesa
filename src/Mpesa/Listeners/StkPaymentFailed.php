<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\StkPushPaymentFailedEvent;

/**
 * Class StkPaymentFailed
 * @package DrH\Listeners
 */
class StkPaymentFailed
{
    /**
     * @param StkPushPaymentFailedEvent $event
     */
    public function handle(StkPushPaymentFailedEvent $event)
    {
        /** @var \DrH\Mpesa\Database\Entities\MpesaStkCallback $stk */
        $stk = $event->stk_callback;
        $stk->request()->update(['status' => 'Failed']);
    }
}
