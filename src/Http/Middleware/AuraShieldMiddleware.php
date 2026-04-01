<?php

namespace Aura\Http\Middleware;

use Aura\Aura;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuraShieldMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Aura::check($request)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
