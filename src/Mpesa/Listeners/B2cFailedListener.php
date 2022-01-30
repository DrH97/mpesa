<?php


namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\B2cPaymentFailedEvent;

class B2cFailedListener
{
    /**
     * @param B2cPaymentFailedEvent $event
     */
    public function handle(B2cPaymentFailedEvent $event)
    {
    }
}
