<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $timeout = (int) config('auth_custom.idle_timeout_seconds', 900);
        $lastActivity = (int) ($request->session()->get('last_activity') ?? 0);
        $now = now()->timestamp;

        if ($lastActivity && ($now - $lastActivity) > $timeout) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['general' => 'Session expired. Please sign in again.']);
        }

        $request->session()->put('last_activity', $now);

        return $next($request);
    }
}

