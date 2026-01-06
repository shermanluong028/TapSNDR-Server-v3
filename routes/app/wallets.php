<?php

use App\Http\Controllers\WalletsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::post('/', [WalletsController::class, 'post']);
    Route::get('/stats', [WalletsController::class, 'getStats']);
});
