<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUser
{
    private $mapRolesToRoute = [
        'GET-/empty'                                              => ['*'],
        'GET-/domains'                                            => ['master_admin', 'distributor'],
        'GET-/tickets'                                            => ['master_admin', 'distributor', 'user', 'player'],
        'GET-/dashboard/wallets'                                  => ['master_admin'],
        'GET-/dashboard/tickets'                                  => ['master_admin'],
        'GET-/accounts'                                           => ['master_admin'],
        '/^GET-\\/accounts\/([^\/]+)$/'                           => ['master_admin'],
        'GET-/transactions'                                       => ['master_admin', 'user'],
        'GET-/withdrawals'                                        => ['master_admin'],
        'GET-/user_activities'                                    => ['master_admin:tapsndr'],
        'GET-/settings/wallet'                                    => ['master_admin'],
        'GET-/settings/ticket/payment_methods'                    => ['master_admin'],
        'GET-/distributor/clients'                                => ['distributor'],
        // 'GET-/distributor/settings'                               => ['distributor'],
        'GET-/player/payment_details'                             => ['player'],
        'GET-/player/vendors'                                     => ['player'],
        'GET-/tapsndr/dashboard/wallets'                          => ['master_admin:tapsndr'],
        'GET-/tapsndr/accounts'                                   => ['master_admin:tapsndr'],
        'GET-/tapsndr/transactions'                               => ['master_admin:tapsndr'],
        'POST-/web/auth/signout'                                  => ['*'],
        'GET-/web/users'                                          => ['master_admin', 'distributor'],
        'GET-/web/users/stats'                                    => ['master_admin'],
        '/^GET-\/web\/users\/([^\/]+)$/'                          => ['master_admin', 'distributor'],
        '/^GET-\/web\/users\/([^\/]+)\/domains$/'                 => ['player'],
        '/^GET-\/web\/users\/([^\/]+)\/tickets$/'                 => ['master_admin'],
        '/^GET-\/web\/users\/([^\/]+)\/stats$/'                   => ['master_admin'],
        '/^GET-\/web\/users\/([^\/]+)\/wallet$/'                  => ['*'],
        '/^GET-\/web\/users\/([^\/]+)\/crypto_addresses$/'        => ['master_admin'],
        '/^POST-\/web\/users\/([^\/]+)\/domains$/'                => ['player'],
        '/^DELETE-\/web\/users\/([^\/]+)\/domains$/'              => ['player'],
        // '/^GET-\/web\/users\/([^\/]+)\/clients$/'                 => ['master_admin', 'distributor'],
        'POST-/web/users'                                         => ['master_admin', 'distributor'],
        // 'DELETE-/web/users'                                       => ['master_admin'],
        'GET-/web/roles'                                          => ['master_admin', 'distributor'],
        'POST-/web/wallets'                                       => ['master_admin'],
        'GET-/api/deposit/address'                                => ['*'],
        'POST-/api/deposit'                                       => ['*'],
        'GET-/web/user_activities'                                => ['master_admin:tapsndr'],
        'DELETE-/web/crypto_addresses'                            => ['master_admin'],
        'GET-/web/domains'                                        => ['master_admin', 'distributor'],
        '/^GET-\/web\/domains\/([^\/]+)$/'                        => ['master_admin', 'distributor'],
        '/^GET-\/web\/domains\/([^\/]+)\/games$/'                 => ['player'],
        '/^GET-\/web\/domains\/([^\/]+)\/commission_percentage$/' => ['player'],
        'POST-/web/domains'                                       => ['master_admin', 'distributor'],
        // 'DELETE-/web/domains'                                     => ['master_admin'],
        'GET-/web/games'                                          => ['master_admin', 'distributor'],
        'GET-/web/payment_methods'                                => ['master_admin', 'player'],
        'POST-/web/payment_methods'                               => ['master_admin'],
        'GET-/web/payment_details'                                => ['player'],
        '/^GET-\/web\/payment_details\/([^\/]+)$/'                => ['player'],
        'POST-/web/payment_details'                               => ['player'],
        'DELETE-/web/payment_details'                             => ['player'],
        'GET-/web/tickets'                                        => ['master_admin', 'distributor', 'user', 'player'],
        '/^GET-\/web\/tickets\/([^\/]+)\/validation_image$/'      => ['master_admin', 'user'],
        'GET-/web/tickets/stats'                                  => ['master_admin'],
        'GET-/web/tickets/stats/daily_total_amount'               => ['master_admin'],
        'POST-/web/tickets'                                       => ['master_admin', 'player'],
        '/^POST-\/web\/tickets\/([^\/]+)\/refund$/'               => ['master_admin'],
        'GET-/web/withdrawals'                                    => ['master_admin'],
        'GET-/web/withdrawals/stats'                              => ['master_admin'],
        'POST-/web/withdrawals'                                   => ['*'],
        'POST-/api/withdrawals'                                   => ['*'],
        'GET-/web/wallets/stats'                                  => ['master_admin'],
        'GET-/web/transactions'                                   => ['master_admin', 'user'],
        'GET-/web/transactions/stats'                             => ['master_admin', 'user'],
        'POST-/web/transactions'                                  => ['master_admin'],
        'GET-/api/settings'                                       => ['*'],
        'POST-/web/settings'                                      => ['*'],
        'POST-/api/settings'                                      => ['*'],
        'GET-/web/crypto_wallets'                                 => ['master_admin'],
    ];

    public function handle(Request $request, Closure $next, string ...$params): Response
    {
        // define auth page urls
        $authPageRoutes = [
            'GET-/auth/signin',
            'GET-/auth/signup',
            'GET-/forgot-password',
            '/^GET-\/reset-password\/([^\/]+)$/',
        ];

        // get method and url of request
        $method = $request->getMethod();
        $uri    = $request->getPathInfo();
        if (key_exists(0, $params)) {
            $uri = $params[0];
        }
        $route = $method . '-' . $uri;

        $isPageRoute = ! str_starts_with($uri, '/api') && ! str_starts_with($uri, '/web');

        if ($request->bearerToken()) {
            Auth::shouldUse('api');
        } else {
            Auth::shouldUse('web');
        }

        if (Auth::check()) {
            $currentUser = Auth::user();
            // if user has already signed in and url requested is auth page url, redirect default page per role
            $mapRedirectUrlToRole = [
                'master_admin'         => '/dashboard/wallets',
                'master_admin:tapsndr' => '/tapsndr/dashboard/wallets',
                'distributor'          => '/distributor/clients',
                'user'                 => '/tickets',
                'player'               => '/player/payment_details',
            ];
            for ($i = 0; $i < count($authPageRoutes); $i++) {
                if (
                    $route === $authPageRoutes[$i] ||
                    @preg_match($authPageRoutes[$i], $route)
                ) {
                    $redirectUrl = $mapRedirectUrlToRole[$currentUser->role . ':' . $currentUser->username] ?? $mapRedirectUrlToRole[$currentUser->role] ?? '/empty';
                    return response()->redirectTo($redirectUrl);
                }
            }

            // check if current user has authority for access to this url
            $routes = array_keys($this->mapRolesToRoute);
            for ($i = 0; $i < count($routes); $i++) {
                if (
                    (
                        $routes[$i] === $route ||
                        @preg_match($routes[$i], $route)
                    ) && (
                        in_array('*', $this->mapRolesToRoute[$routes[$i]]) ||
                        in_array($currentUser->role, $this->mapRolesToRoute[$routes[$i]]) ||
                        in_array($currentUser->role . ':' . $currentUser->username, $this->mapRolesToRoute[$routes[$i]])
                    )
                ) {
                    return $next($request);
                }
            }
            return response('Forbidden', 403);
        } else {
            if ($isPageRoute) {
                for ($i = 0; $i < count($authPageRoutes); $i++) {
                    if (
                        $route === $authPageRoutes[$i] ||
                        @preg_match($authPageRoutes[$i], $route)
                    ) {
                        return $next($request);
                    }
                }
                return response()->redirectTo('/auth/signin');
            } else {
                return response('Unauthorized', 401);
            }
        }
    }
}
