<?php

use App\Http\Controllers\{OrderController, PaymentLogController, WebhookController};
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'orders'], function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/', [OrderController::class, 'index']);
});

Route::group(['prefix' => 'payment-logs'], function () {
    Route::post('/', [PaymentLogController::class, 'store']);
    Route::get('/', [PaymentLogController::class, 'index']);
    Route::get('/{PaymentLog}', [PaymentLogController::class, 'show']);
});

Route::group(['prefix' => 'webhook'], function () {
    Route::post('/', [WebhookController::class, 'midtransHandler']);
});