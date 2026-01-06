<?php
namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
//    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')->group(function () {
                Route::group(['prefix' => 'api'], base_path('routes/app/app.php'));
                Route::group([], base_path('routes/api/api.php'));
            });

            Route::middleware('web')->group(function () {
                // Route::get('/', function () {
                //     return redirect('/views');
                // });
                Route::group([
                    // 'prefix' => 'views'
                ], base_path('routes/views/views.php'));
                Route::group(['prefix' => 'web'], base_path('routes/app/app.php'));
            });

            Route::group([
                // 'prefix' => 'legacy'
            ], base_path('routes/legacy/legacy.php'));
        });
    }
}
