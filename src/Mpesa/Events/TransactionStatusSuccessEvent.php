<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Entities\MpesaTransactionStatusCallback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusSuccessEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public MpesaTransactionStatusCallback $mpesaStatusCallback, public array $statusResult)
    {
    }
}
