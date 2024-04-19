<?php

namespace DrH\Mpesa\Listeners;

use DrH\Mpesa\Events\TransactionStatusSuccessEvent;

class TransactionStatusSuccessful
{
    /**
     * @param TransactionStatusSuccessEvent $event
     */
    public function handle(TransactionStatusSuccessEvent $event): void
    {
        $status = $event->mpesaStatusCallback;
        $status->request;

        dump($status);
    }
}
