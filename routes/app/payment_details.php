<?php

use App\Http\Controllers\PaymentDetailsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [PaymentDetailsController::class, 'get']);
    Route::get('/{id}', [PaymentDetailsController::class, 'getById']);
    Route::post('/', [PaymentDetailsController::class, 'post']);
    Route::delete('/', [PaymentDetailsController::class, 'delete']);
});
