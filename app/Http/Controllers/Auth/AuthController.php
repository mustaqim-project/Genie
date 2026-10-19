<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Role sensitif yang mewajibkan Two-Factor Authentication (2FA) sesuai prototipe Genie
     * R-OWN: Pimpinan / Direktur Utama
     * R-FIN: Finance / Bendahara Pengeluaran & Piutang
     */
    protected const ROLES_REQUIRING_2FA = ['R-OWN', 'R-FIN'];

    /**
     * Tampilkan halaman login utama Genie (Apple HIG Split Layout)
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $roles = Role::with('users')->get();

        return view('auth.login', compact('roles'));
    }

    /**
     * Proses autentikasi login dengan proteksi Brute-Force dan routing 2FA
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();

        // 1. Proteksi Brute-Force Lockout (5x salah = kunci 15 menit)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'email' => "Akun dikunci sementara karena terlalu banyak percobaan masuk yang salah. Silakan coba lagi dalam {$minutes} menit atau hubungi administrator.",
            ])->onlyInput('email');
        }

        // 2. Cek apakah akun terdaftar dan aktif
        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan oleh Administrator Sistem. Hubungi HR / IT Lead untuk aktivasi.',
            ])->onlyInput('email');
        }

        // 3. Verifikasi kredensial
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 900); // Kunci selama 900 detik (15 menit)
            $remaining = RateLimiter::remaining($throttleKey, 5);

            $msg = $remaining > 0
                ? "Email atau kata sandi tidak cocok. Sisa percobaan: {$remaining} kali sebelum akun dikunci."
                : "Terlalu banyak percobaan. Akun dikunci sementara selama 15 menit.";

            return back()->withErrors(['email' => $msg])->onlyInput('email');
        }

        // Kredensial valid, bersihkan counter throttle
        RateLimiter::clear($throttleKey);

        // 4. Periksa apakah peran pengguna mewajibkan 2FA (Pimpinan & Finance)
        $userRoleCodes = $user->roles()->pluck('code')->toArray();
        $needs2Fa = count(array_intersect(self::ROLES_REQUIRING_2FA, $userRoleCodes)) > 0;

        if ($needs2Fa) {
            // Generate OTP 6 digit
            $otpCode = (string) rand(100000, 999999);

            Session::put('2fa', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $userRoleCodes[0] ?? 'PENGGUNA',
                'code' => $otpCode,
                'demo_code' => '123456', // Demo bypass sesuai prototipe
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('login.otp')
                ->with('info', 'Kode verifikasi 2-Langkah telah disiapkan untuk akun ' . $user->email);
        }

        // 5. Login normal jika bukan role berisiko tinggi
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali di Genie, ' . $user->name);
    }

    /**
     * Tampilkan halaman input OTP 6-Digit (2FA)
     */
    public function showOtpForm()
    {
        $twoFaData = Session::get('2fa');
        if (!$twoFaData) {
            return redirect()->route('login');
        }

        return view('auth.otp', compact('twoFaData'));
    }

    /**
     * Verifikasi kode OTP 6-Digit
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $twoFaData = Session::get('2fa');
        if (!$twoFaData) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi 2FA Anda telah kedaluwarsa. Silakan masuk kembali.']);
        }

        // Periksa batas percobaan (maksimal 3x)
        if (($twoFaData['attempts'] ?? 0) >= 3) {
            Session::forget('2fa');
            return redirect()->route('login')->withErrors([
                'email' => 'Kode verifikasi 2FA salah 3 kali. Sesi ditutup demi keamanan.',
            ]);
        }

        $inputOtp = trim($request->input('otp'));
        $validCodes = [$twoFaData['code'], $twoFaData['demo_code']];

        if (!in_array($inputOtp, $validCodes)) {
            $twoFaData['attempts'] = ($twoFaData['attempts'] ?? 0) + 1;
            Session::put('2fa', $twoFaData);

            $sisa = 3 - $twoFaData['attempts'];
            if ($sisa <= 0) {
                Session::forget('2fa');
                return redirect()->route('login')->withErrors([
                    'email' => 'Kode salah 3 kali. Silakan masuk kembali.',
                ]);
            }

            return back()->withErrors([
                'otp' => "Kode verifikasi tidak sesuai. Sisa kesempatan: {$sisa} kali.",
            ]);
        }

        // Sukses verifikasi OTP
        $user = User::find($twoFaData['user_id']);
        if (!$user || !$user->is_active) {
            Session::forget('2fa');
            return redirect()->route('login')->withErrors(['email' => 'Akun tidak valid atau dinonaktifkan.']);
        }

        Auth::login($user, $twoFaData['remember'] ?? false);
        Session::forget('2fa');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Verifikasi 2-Langkah berhasil. Selamat datang, ' . $user->name);
    }

    /**
     * Tampilkan formulir lupa password
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim simulasi tautan reset password
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        // Buat token reset simulasi yang berlaku 30 menit
        $resetToken = Str::random(40);
        Session::put('reset_password_' . $request->email, [
            'token' => $resetToken,
            'expires_at' => now()->addMinutes(30),
        ]);

        return view('auth.reset-sent', [
            'email' => $request->email,
            'token' => $resetToken,
            'userExists' => (bool)$user,
        ]);
    }

    /**
     * Tampilkan formulir ganti password baru (dengan Password Strength Meter)
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->query('email');
        $sessionData = Session::get('reset_password_' . $email);

        if (!$sessionData || $sessionData['token'] !== $token || now()->isAfter($sessionData['expires_at'])) {
            return view('auth.reset-expired');
        }

        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Simpan kata sandi baru dengan 5 aturan keamanan
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:10|confirmed',
        ]);

        $email = $request->email;
        $sessionData = Session::get('reset_password_' . $email);

        if (!$sessionData || $sessionData['token'] !== $request->token || now()->isAfter($sessionData['expires_at'])) {
            return view('auth.reset-expired');
        }

        $password = $request->password;

        // 5 Aturan Password Strength prototipe Genie
        $rules = [
            'Panjang minimal 10 karakter' => strlen($password) >= 10,
            'Memuat huruf besar dan huruf kecil' => preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password),
            'Memuat angka' => preg_match('/\d/', $password),
            'Memuat simbol/karakter khusus' => preg_match('/[^A-Za-z0-9]/', $password),
            'Tidak memuat bagian email' => !str_contains(strtolower($password), strtolower(explode('@', $email)[0])),
        ];

        $failed = array_keys(array_filter($rules, fn($passed) => !$passed));
        if (!empty($failed)) {
            return back()->withErrors([
                'password' => 'Kata sandi belum memenuhi kriteria: ' . implode(', ', $failed),
            ]);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
        }

        Session::forget('reset_password_' . $email);

        return redirect()->route('login')
            ->with('success', 'Kata sandi Anda berhasil diperbarui. Silakan masuk menggunakan sandi baru.');
    }

    /**
     * Logout pengguna dari sesi Genie
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem Genie.');
    }

    /**
     * Beralih peran / Quick Login simulasi untuk testing RBAC prototipe
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
