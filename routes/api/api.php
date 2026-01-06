<?php

use Illuminate\Support\Facades\Route;

Route::domain(env('APP_API_HOST', 'api.tapsndr.com'))->group(function () {
    Route::group(['prefix' => 'payouts'], __DIR__ . '/payouts.php');
});
