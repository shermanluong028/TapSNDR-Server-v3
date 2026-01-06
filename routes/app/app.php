<?php

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::group([], __DIR__ . '/auth.php');
    Route::group(['prefix' => 'users'], __DIR__ . '/users.php');
    Route::group(['prefix' => 'roles'], __DIR__ . '/roles.php');
    Route::group(['prefix' => 'wallets'], __DIR__ . '/wallets.php');
    Route::group(['prefix' => 'deposit'], __DIR__ . '/deposit.php');
    Route::group(['prefix' => 'user_activities'], __DIR__ . '/user_activities.php');
    Route::group(['prefix' => 'crypto_addresses'], __DIR__ . '/crypto_addresses.php');
    Route::group(['prefix' => 'domains'], __DIR__ . '/domains.php');
    Route::group(['prefix' => 'games'], __DIR__ . '/games.php');
    Route::group(['prefix' => 'payment_methods'], __DIR__ . '/payment_methods.php');
    Route::group(['prefix' => 'payment_details'], __DIR__ . '/payment_details.php');
    Route::group(['prefix' => 'tickets'], __DIR__ . '/tickets.php');
    Route::group(['prefix' => 'withdrawals'], __DIR__ . '/withdrawals.php');
    Route::group(['prefix' => 'transactions'], __DIR__ . '/transactions.php');
    Route::group(['prefix' => 'settings'], __DIR__ . '/settings.php');
    Route::group(['prefix' => 'crypto_wallets'], __DIR__ . '/crypto_wallets.php');
});
