<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return response()->redirectTo('/legacy/v1/customer');
// });

Route::any('/db_proxy.php', function () {
    ob_start();
    include base_path('legacy/v1/db_proxy.php');
    return response(ob_get_clean());
});

Route::group([
    // 'prefix' => 'customer'
], __DIR__ . '/customer.php');
