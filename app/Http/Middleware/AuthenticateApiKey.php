<?php
namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $query = $request->query();

        if (! isset($query['api_key'])) {
            return response('Unauthorized', 401);
        }

        $apiKey = ApiKey::where('api_key', $query['api_key'])->first();

        if (! $apiKey) {
            return response('Unauthorized', 401);
        }

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
