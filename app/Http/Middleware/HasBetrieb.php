<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasBetrieb
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('betrieb')) {
            return redirect('/betrieb/login');
        }

        // Sicherheitsnetz: Börse darf hier nicht rein – eigenes Frontend
        $b = session('betrieb');
        if (is_object($b) && !empty($b->is_boerse)) {
            session()->forget('betrieb');
            session()->put('boerse', now()->toDateTimeString());
            return redirect('/boerse');
        }

        return $next($request);
    }
}

