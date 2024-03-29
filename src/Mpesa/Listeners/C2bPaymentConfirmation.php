<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\Mpesa\Events\C2bConfirmationEvent;

class C2bPaymentConfirmation
{
    /**
     * @param C2bConfirmationEvent $event
     */
    public function handle(C2bConfirmationEvent $event): void
    {
        $c2b = $event->c2bCallback;

        //Try to check if this was from STK, won't work for Till Numbers beware!!!
        $request = MpesaStkRequest::whereReference($c2b->bill_ref_number)->first();

        mpesaLogInfo('C2B Listener: ', [$c2b, $event->apiJsonData, $request]);
    }
}
