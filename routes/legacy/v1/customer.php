<?php

use App\Models\FormDomain;
use Illuminate\Support\Facades\Route;

Route::domain(env('APP_TICKET_FORM_HOST'))->group(function () {
    Route::get('/{path?}', function () {
        ob_start();
        include base_path('legacy/v1/customer/index.php');
        return response(ob_get_clean());
    })->where('path', '.*');

    Route::post('/api/submit.php', function () {
        ob_start();
        include base_path('legacy/v1/customer/api/submit.php');
        return response(ob_get_clean());
    });
});

$vendors = FormDomain::all();
foreach ($vendors as $vendor) {
    Route::domain($vendor->vendor_code . '.' . env('APP_HOST'))->group(function () use ($vendor) {
        Route::get('/', function () use ($vendor) {
            return redirect('https://' . env('APP_TICKET_FORM_HOST') . '/' . strtolower($vendor->vendor_code));
        });
    });
}