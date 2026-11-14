<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirstLoginCompleted
{
    /**
     * Redirect users with a temporary/uninitialized password to first-login setup.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('first-login.setup', 'logout')) {
            return $next($request);
        }

        return redirect()->route('first-login.setup');
    }
}
