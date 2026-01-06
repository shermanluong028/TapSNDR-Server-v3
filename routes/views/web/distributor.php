<?php

use Illuminate\Support\Facades\Route;

Route::get('/clients', function () {
    return view('web.pages.distributor.clients', ['title' => 'Clients']);
});

// Route::get('/settings', function () {
//     return view('web.pages.distributor.settings', ['title' => 'Settings']);
// });
