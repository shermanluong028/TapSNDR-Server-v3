<?php

use App\Http\Controllers\GamesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [GamesController::class, 'get']);
});
