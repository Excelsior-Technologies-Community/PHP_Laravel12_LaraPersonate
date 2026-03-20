<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Share flag to UI
        if (session()->has('impersonate_original_id')) {
            view()->share('isImpersonating', true);
        }

        return $next($request);
    }
}