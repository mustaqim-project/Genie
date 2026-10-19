<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            // Support comma-separated permissions as well
            $subPerms = explode('|', $permission);
            foreach ($subPerms as $p) {
                if ($user->hasPermission(trim($p))) {
                    return $next($request);
                }
            }
        }

        abort(403, "Akses Ditolak: Anda membutuhkan hak akses izin: " . implode(', ', $permissions) . ".");
    }
}
