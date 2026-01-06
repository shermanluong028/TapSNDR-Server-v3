<?php

use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\TicketsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.api_key')->group(function () {
    Route::get('/payment_methods', [PaymentMethodsController::class, 'get']);
    Route::post('/tickets', [TicketsController::class, 'post']);
});
