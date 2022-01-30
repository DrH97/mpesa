<?php

use DrH\Mpesa\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'payments/callbacks',
    'middleware' => 'pesa.cors',
    'namespace' => 'DrH\Mpesa\Http\Controllers'
], function () {
    Route::any('c2b-validation', [MpesaController::class, 'c2bValidation']);
    Route::any('c2b-confirmation', [MpesaController::class, 'c2bConfirmation']);

    Route::any('b2c-timeout/{section?}', [MpesaController::class, 'b2cTimeout']);
    Route::any('b2c-result/{section?}', [MpesaController::class, 'b2cResult']);

    Route::any('stk-callback', [MpesaController::class, 'stkCallback']);

//    Potentially open endpoints that could be used to initiate unauthorized stk requests
//    Implement similar in your app if needed, will be removed in future from here but left in controller
//    Route::any('stk_request', 'StkController@initiatePush');
//    Route::get('stk_status/{id}', 'StkController@stkStatus');
});
