<?php

use App\Http\Controllers\TicketsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [TicketsController::class, 'get']);
    Route::get('/stats', [TicketsController::class, 'getStats']);
    Route::get('/stats/daily_total_amount', [TicketsController::class, 'getDailyTotalAmount']);
    Route::get('/{id}/validation_image', [TicketsController::class, 'getValidationImage']);
    Route::post('/', [TicketsController::class, 'post']);
    Route::post('/{id}/refund', [TicketsController::class, 'refund']);
});
