<?php

use Illuminate\Support\Facades\Route;

Route::get('/payment_details', function () {
    return view('web.pages.player.payment_details', ['title' => 'Payment Methods']);
});

Route::get('/vendors', function () {
    return view('web.pages.player.vendors', ['title' => 'Vendors']);
});
