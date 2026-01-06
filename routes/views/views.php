<?php

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return response()->redirectTo('/views/web');
//});

Route::group([], function () {
    Route::group([], __DIR__ . '/web/web.php');
});
