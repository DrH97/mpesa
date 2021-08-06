<?php


namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\B2cPaymentFailedEvent;

/**
 * Class B2CFailedListener
 * @package DrH\Mpesa\Listeners
 */
class B2cFailedListener
{
    /**
     * @param B2cPaymentFailedEvent $event
     */
    public function handle(B2cPaymentFailedEvent $event)
    {
    }
}
