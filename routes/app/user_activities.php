<?php

use App\Http\Controllers\UserActivitiesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/', [UserActivitiesController::class, 'get']);
});
