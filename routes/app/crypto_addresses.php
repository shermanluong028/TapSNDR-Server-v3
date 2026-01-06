<?php

use App\Http\Controllers\CryptoAddressesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::delete('/', [CryptoAddressesController::class, 'delete']);
});
