<?php

use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [TransactionsController::class, 'get']);
    Route::get('/stats', [TransactionsController::class, 'getStats']);
    Route::post('/', [TransactionsController::class, 'post']);
});
