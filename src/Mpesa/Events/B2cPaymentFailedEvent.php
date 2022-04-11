<?php

namespace DrH\Mpesa\Events;

use DrH\Mpesa\Database\Entities\MpesaBulkPaymentResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class B2cPaymentFailedEvent
 * @package DrH\Events
 */
class B2cPaymentFailedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var MpesaBulkPaymentResponse
     */
    public MpesaBulkPaymentResponse $bulkPaymentResponse;
    /**
     * @var array
     */
    public array $response;

    /**
     * B2cPaymentSuccessEvent constructor.
     * @param MpesaBulkPaymentResponse $mpesaBulkPaymentResponse
     * @param array $response
     */
    public function __construct(MpesaBulkPaymentResponse $mpesaBulkPaymentResponse, array $response)
    {
        $this->bulkPaymentResponse = $mpesaBulkPaymentResponse;
        $this->response = $response;
    }
}
