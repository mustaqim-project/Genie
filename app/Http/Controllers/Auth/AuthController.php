<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $roles = Role::with('users')->get();

        return view('auth.login', compact('roles'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan oleh Administrator Sistem. Hubungi IT Lead untuk aktivasi.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name);
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Beralih peran / Quick Login simulasi untuk testing RBAC
     */
    public function quickLogin(string $roleCode)
    {
        $role = Role::where('code', $roleCode)->first();
        if (!$role) {
            return back()->with('error', 'Peran tidak ditemukan.');
        }

        $user = $role->users()->where('is_active', true)->first() ?? $role->users()->first();
        if (!$user) {
            return back()->with('error', 'Belum ada pengguna yang memiliki peran ini.');
        }

        if (!$user->is_active) {
            return back()->with('error', "Pengguna untuk peran [{$role->code}] sedang dinonaktifkan.");
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Beralih peran aktif sebagai: [{$role->code}] {$role->name} ({$user->name})");
    }
}
