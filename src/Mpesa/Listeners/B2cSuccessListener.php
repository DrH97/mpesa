<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\B2cPaymentSuccessEvent;

class B2cSuccessListener
{
    /**
     * @param B2cPaymentSuccessEvent $event
     */
    public function handle(B2cPaymentSuccessEvent $event)
    {
    }
}
