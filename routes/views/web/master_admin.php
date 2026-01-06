<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard/wallets', function () {
    return view('web.pages.master_admin.dashboard.wallets', ['title' => 'Dashboard']);
});

Route::get('/dashboard/tickets', function () {
    return view('web.pages.master_admin.dashboard.tickets', ['title' => 'Dashboard']);
});

Route::get('/accounts', function () {
    return view('web.pages.master_admin.accounts', ['title' => 'Accounts']);
});

Route::get('/accounts/{id}', function () {
    return view('web.pages.master_admin.account', [
        'title' => 'Account Details',
        'data'  => [
            'id' => request()->route('id'),
        ],
    ]);
});

Route::get('/transactions', function () {
    return view('web.pages.master_admin.transactions', ['title' => 'Balance History']);
});

Route::get('/withdrawals', function () {
    return view('web.pages.master_admin.withdrawals', ['title' => 'Withdrawals']);
});

Route::get('/user_activities', function () {
    return view('web.pages.master_admin.user_activities', ['title' => 'activities']);
});

Route::get('/settings/wallet', function () {
    return view('web.pages.master_admin.settings.wallet', ['title' => 'Settings']);
});

Route::get('/settings/ticket/payment_methods', function () {
    return view('web.pages.master_admin.settings.ticket.payment_methods', ['title' => 'Settings']);
});
