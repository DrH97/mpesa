<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\C2bConfirmationEvent;

class C2bPaymentConfirmation
{
    /**
     * Handle the event.
     *
     * @param C2bConfirmationEvent $event
     * @return void
     */
    public function handle(C2bConfirmationEvent $event)
    {
        $c2b = $event->c2bCallback;
        //Try to check if this was from STK
        $request = MpesaStkRequest::whereReference($c2b->bill_ref_number)->first();
    }
}
