<?php
namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $currentUser = Auth::user();
            if (! $currentUser) {
                $currentUser       = new \App\Models\User();
                $currentUser->role = 'guest';
            }
            $view->with('currentUser', $currentUser);
        });

        Request::macro('apiKey', function () {
            return $this->attributes->get('api_key');
        });
    }
}
