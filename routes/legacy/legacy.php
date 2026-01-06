<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return response()->redirectTo('/legacy/v1');
// });

Route::group([
    // 'prefix' => 'v1'
], __DIR__ . '/v1/v1.php');
