<?php

use Illuminate\Support\Facades\Route;

foreach ([
    env('APP_ADMIN_HOST', 'admin.tapsndr.com'),
    env('APP_CLIENT_HOST', 'client.tapsndr.com'),
    env('APP_REDEEM_HOST', 'redeem.tapsndr.com'),
] as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return response()->redirectTo('/auth/signin');
        });
    });
}

Route::domain(env('APP_REDEEM_HOST', 'redeem.tapsndr.com'))->group(function () {
    Route::get('/redeem/{vendor_code?}', function ($vendorCode) {
        return view('web.pages.redeem', [
            'title' => 'Submit a ticket',
            'data'  => [
                'vendor_code' => $vendorCode,
            ],
        ]);
    })->where('path', '.*');
});

Route::middleware('auth.user')->group(function () {
    foreach ([
        env('APP_ADMIN_HOST', 'admin.tapsndr.com'),
        env('APP_CLIENT_HOST', 'client.tapsndr.com'),
        env('APP_REDEEM_HOST', 'redeem.tapsndr.com'),
    ] as $domain) {
        Route::domain($domain)->group(function () {
            Route::get('/reset-password/{token}', function () {
                return view('web.pages.master_admin.reset-password', [
                    'title' => 'Reset password',
                ]);
            })->name('password.reset');
            Route::get('/empty', function () {
                return view('web.pages.empty');
            });
            Route::get('/tickets', function () {
                return view('web.pages.tickets', ['title' => 'Tickets']);
            });
        });
    }

    foreach ([
        env('APP_ADMIN_HOST', 'admin.tapsndr.com'),
        env('APP_CLIENT_HOST', 'client.tapsndr.com'),
    ] as $domain) {
        Route::domain($domain)->group(function () {
            Route::get('/auth/signin', function () {
                return view('web.pages.master_admin.signin', ['title' => 'Sign In']);
            });
            Route::get('/forgot-password', function () {
                return view('web.pages.master_admin.forgot-password', ['title' => 'Forgot Password']);
            });
        });
    }

    Route::domain(env('APP_ADMIN_HOST', 'admin.tapsndr.com'))->group(function () {
        Route::get('/auth/signin', function () {
            return view('web.pages.master_admin.signin', ['title' => 'Sign In']);
        });
        Route::get('/forgot-password', function () {
            return view('web.pages.master_admin.forgot-password', ['title' => 'Forgot Password']);
        });
        Route::get('/domains', function () {
            return view('web.pages.domains', ['title' => 'Domains']);
        });
        Route::group([], __DIR__ . '/master_admin.php');
        Route::group(['prefix' => 'distributor'], __DIR__ . '/distributor.php');
    });

    Route::domain(env('APP_CLIENT_HOST', 'client.tapsndr.com'))->group(function () {
        Route::group([], __DIR__ . '/user.php');
    });

    Route::domain(env('APP_REDEEM_HOST', 'redeem.tapsndr.com'))->group(function () {
        Route::get('/auth/signin', function () {
            return view('web.pages.player.signin', ['title' => 'Sign In']);
        });
        Route::get('/auth/signup', function () {
            return view('web.pages.signup', ['title' => 'Sign Up']);
        });
        Route::get('/forgot-password', function () {
            return view('web.pages.player.forgot-password', ['title' => 'Forgot Password']);
        });
        Route::group(['prefix' => 'player'], __DIR__ . '/player.php');
    });
});
