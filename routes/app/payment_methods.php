<?php

use App\Http\Controllers\PaymentMethodsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaymentMethodsController::class, 'get']);

Route::middleware('auth.user')->group(function () {
    Route::post('/', [PaymentMethodsController::class, 'post']);
});
