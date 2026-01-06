<?php

use App\Http\Controllers\DepositController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/address', [DepositController::class, 'getAddress']);
    Route::post('/', [DepositController::class, 'deposit']);
});
