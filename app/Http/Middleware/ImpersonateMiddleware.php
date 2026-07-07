<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonate_original_id')) {
            $startedAt = session('impersonation_started_at', 0);
            $timeout = 15 * 60;

            if ($startedAt > 0 && (time() - $startedAt) > $timeout) {
                session()->forget(['impersonate_original_id', 'impersonated_user_id', 'impersonation_started_at']);
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Impersonation session timeout occurred.']);
            }

            if ($request->is('profile') && ($request->isMethod('patch') || $request->isMethod('delete') || $request->isMethod('put'))) {
                return back()->with('error', 'Action denied. Profile mutations are restricted during active impersonation sessions.');
            }
        }

        return $next($request);
    }
}