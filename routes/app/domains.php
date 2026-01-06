<?php

use App\Http\Controllers\DomainsController;
use Illuminate\Support\Facades\Route;

Route::get('/{vendor_code}', [DomainsController::class, 'getByVendorCode']);
Route::get('/{id}/games', [DomainsController::class, 'getGamesById']);

Route::middleware('auth.user')->group(function () {
    Route::get('/', [DomainsController::class, 'get']);
    Route::get('/{id}', [DomainsController::class, 'getById'])->where('id', '[0-9]+');
    Route::get('/{id}/commission_percentage', [DomainsController::class, 'getCommissionPercentageById']);
    Route::post('/', [DomainsController::class, 'post']);
    // Route::delete('/', [DomainsController::class, 'delete']);
});
