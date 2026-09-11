<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Allow password change routes
            if ($request->routeIs('password.change', 'password.change.update', 'logout')) {
                return $next($request);
            }
            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengganti password sebelum melanjutkan.');
        }

        return $next($request);
    }
}
