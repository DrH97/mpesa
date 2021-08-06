<?php


namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\B2cPaymentSuccessEvent;

/**
 * Class B2CSuccessListener
 * @package DrH\Mpesa\Listeners
 */
class B2cSuccessListener
{
    /**
     * @param B2cPaymentSuccessEvent $event
     */
    public function handle(B2cPaymentSuccessEvent $event)
    {
    }
}
