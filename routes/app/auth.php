<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/signin', [AuthController::class, 'signIn']);
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::domain(env('APP_REDEEM_HOST', 'redeem.tapsndr.com'))->group(function () {
    Route::post('/auth/signup', [AuthController::class, 'signUp']);
});

Route::middleware('auth.user')->group(function () {
    Route::post('/auth/signout', [AuthController::class, 'signOut']);
});
