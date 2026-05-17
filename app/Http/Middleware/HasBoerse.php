<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasBoerse
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('boerse')) {
            return redirect('/boerse/login');
        }

        return $next($request);
    }
}

