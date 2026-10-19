<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (empty($roles) || $user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki peran yang diizinkan untuk mengakses modul ini.');
    }
}
