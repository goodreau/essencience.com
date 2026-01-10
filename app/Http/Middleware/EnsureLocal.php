<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureLocal
{
    public function handle(Request $request, Closure $next)
    {
        if (!app()->environment('local')) {
            abort(403, 'Admin UI available only in local environment.');
        }
        return $next($request);
    }
}
