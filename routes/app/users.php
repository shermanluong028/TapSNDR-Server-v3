<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [UsersController::class, 'get']);
    Route::get('/stats', [UsersController::class, 'getStats']);
    Route::get('/{id}', [UsersController::class, 'getById']);
    Route::get('/{id}/stats', [UsersController::class, 'getStatsById']);
    Route::get('/{id}/domains', [UsersController::class, 'getDomainsById']);
    Route::get('/{id}/tickets', [UsersController::class, 'getTicketsById']);
    Route::get('/{id}/wallet', [UsersController::class, 'getWalletById']);
    // Route::get('/{id}/clients', [UsersController::class, 'getClientsById']);
    Route::get('/{id}/crypto_addresses', [UsersController::class, 'getCryptoAddressesById']);
    Route::post('/', [UsersController::class, 'post']);
    Route::post('/{id}/domains', [UsersController::class, 'attachDomain']);
    // Route::delete('/', [UsersController::class, 'delete']);
    Route::delete('/{id}/domains', [UsersController::class, 'dettachDomain']);
});
