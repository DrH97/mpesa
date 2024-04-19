<?php

use DrH\Mpesa\Entities\MpesaB2bCallback;
use DrH\Mpesa\Entities\MpesaTransactionStatusCallback;
use DrH\Mpesa\Events\B2bPaymentSuccessEvent;
use DrH\Mpesa\Events\TransactionStatusSuccessEvent;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

$statusCallbackUrl = 'payments/callbacks/result/b2b/status';


it('handles successful b2b status callback', function () use ($statusCallbackUrl) {

    postJson($statusCallbackUrl, $this->mockResponses['b2b']['status'])
        ->assertSuccessful()
        ->assertJson(['ResultCode' => 0]);

    assertDatabaseCount((new MpesaTransactionStatusCallback())->getTable(), 1);
    assertDatabaseHas((new MpesaTransactionStatusCallback())->getTable(), [
        'conversation_id' => 'AG_20191219_00004e48cf7e3533f581',
    ]);

    assertDatabaseCount((new MpesaB2bCallback())->getTable(), 1);
    assertDatabaseHas((new MpesaB2bCallback())->getTable(), [
        'conversation_id' => 'AG_20230420_2010759fd5662ef6d054',
    ]);

    Event::assertDispatched(TransactionStatusSuccessEvent::class, 1);
    Event::assertDispatched(B2bPaymentSuccessEvent::class, 1);
});
