<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWeeklyMondaySessionReset
{
    /**
     * Handle an incoming request.
     * Memastikan sesi pengguna di-reset setiap 1x dalam 7 hari, tepat pada pergantian hari Senin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Cek apakah fitur reset sesi mingguan aktif di pengaturan sistem (default: true)
            $isWeeklyResetActive = filter_var(
                SystemSetting::get('session_weekly_monday_reset', true),
                FILTER_VALIDATE_BOOLEAN
            );

            if ($isWeeklyResetActive) {
                // Hari Senin awal minggu berjalan (Pukul 00:00:00)
                $currentWeekMonday = now()->startOfWeek(Carbon::MONDAY)->toDateString();
                $sessionMonday = session('auth_monday_cycle');

                // Jika session belum memiliki penanda siklus Senin, inisialisasikan
                if ($sessionMonday === null) {
                    session(['auth_monday_cycle' => $currentWeekMonday]);
                } elseif ($sessionMonday !== $currentWeekMonday) {
                    // Sesi dibuat pada minggu sebelumnya (telah melewati hari Senin baru)
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'Sesi masuk Anda telah di-reset secara otomatis sesuai kebijakan keamanan rutin setiap hari Senin (1x per 7 hari). Silakan masuk kembali.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}
