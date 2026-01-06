<?php

use App\Http\Controllers\CryptoWalletsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [CryptoWalletsController::class, 'get']);
});
