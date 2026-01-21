<?php

namespace App\Http\Middleware;

use Closure;
use App\Features;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureAuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (!$request->user()?->hasFeature($feature)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
