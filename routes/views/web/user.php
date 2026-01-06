<?php

use Illuminate\Support\Facades\Route;

Route::get('/transactions', function () {
    return view('web.pages.user.transactions', ['title' => 'Balance History']);
});
