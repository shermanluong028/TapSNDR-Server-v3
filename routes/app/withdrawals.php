<?php

use App\Http\Controllers\WithdrawalsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [WithdrawalsController::class, 'get']);
    Route::get('/stats', [WithdrawalsController::class, 'getStats']);
    Route::post('/', [WithdrawalsController::class, 'post']);
});
