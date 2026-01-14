<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [SettingsController::class, 'get']);
    Route::post('/', [SettingsController::class, 'post']);
});
