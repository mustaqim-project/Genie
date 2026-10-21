<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2-Langkah (2FA) - Genie ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --f: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --fd: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --r-ctl: 10px;
            --ease: cubic-bezier(.25, .1, .25, 1);
        }
        body {
            font-family: var(--f);
            background-color: #0B1120;
            color: #F8FAFC;
        }
        .mk {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            box-shadow: inset 0 0 0 .5px rgba(255, 255, 255, .35), 0 2px 6px rgba(0, 113, 227, .4);
            color: #fff;
            font-family: var(--fd);
            font-weight: 700;
            font-size: 20px;
            line-height: 1;
        }
        .btn-apple {
            border-radius: 980px;
            transition: all .2s var(--ease);
        }
        .btn-apple:hover {
            filter: brightness(1.08);
            transform: scale(1.005);
        }
    </style>
</head>
<body class="h-full min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950">

    <div class="max-w-md w-full">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-3">
                <div class="mk">G</div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Verifikasi 2-Langkah</h1>
            <p class="text-slate-400 text-sm mt-1.5">
                Keamanan tingkat tinggi untuk akun <b class="text-white">{{ $twoFaData['email'] ?? 'Pengguna' }}</b> ({{ $twoFaData['role'] ?? 'Role' }}).
            </p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl">
            <!-- Alert & Messages -->
            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-sky-950/80 border border-sky-500/40 text-sky-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->has('otp'))
                <div class="mb-6 p-4 rounded-xl bg-rose-950/90 border border-rose-500/50 text-rose-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $errors->first('otp') }}</span>
                </div>
            @endif

            <form action="{{ route('login.otp.post') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="otpInput" class="block text-xs font-semibold text-slate-300 mb-2 text-center uppercase tracking-wider">
                        Masukkan Kode 6-Digit Otentikator
                    </label>
                    <input id="otpInput" type="text" name="otp" inputmode="numeric" maxlength="6" autofocus required
                        placeholder="••••••"
                        class="w-full py-4 text-center text-3xl font-mono tracking-[0.4em] rounded-2xl bg-slate-950 border-2 border-slate-700 text-sky-400 placeholder-slate-700 focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/20 transition-all">
                </div>

                @if(app()->isProduction())
                    <!-- Mode Production: Kode hanya dikirim ke email resmi -->
                    <div class="p-3.5 rounded-xl bg-sky-950/40 border border-sky-500/30 text-xs text-slate-300 space-y-1 text-center">
                        <div class="flex items-center justify-center gap-1.5 font-semibold text-sky-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Kode OTP Terkirim ke Email</span>
                        </div>
                        <p class="text-[11px] text-slate-400">
                            Kode keamanan telah dikirimkan ke <b class="text-white">{{ $twoFaData['email'] }}</b>. Berlaku 10 menit.
                        </p>
                    </div>
                @else
                    <!-- Mode Testing / Development: Simulasi & Demo Code -->
                    <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-slate-400 text-center font-mono">
                        <span>Kode OTP simulasi: <strong class="text-sky-300 bg-slate-800/80 px-2 py-0.5 rounded">{{ $twoFaData['code'] ?? '123456' }}</strong></span>
                    </div>
                @endif

                <button type="submit"
                    class="btn-apple w-full py-3.5 px-6 bg-white hover:bg-slate-100 text-slate-950 text-sm font-bold shadow-xs active:scale-95 transition-all cursor-pointer">
                    Verifikasi Kode & Masuk &rarr;
                </button>
            </form>

            <!-- Kirim Ulang OTP -->
            <form action="{{ route('login.otp.resend') }}" method="POST" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-xs text-sky-400 hover:text-sky-300 font-medium inline-flex items-center gap-1 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Kirim Ulang Kode OTP ke Email</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    &larr; Batalkan dan kembali ke login
                </a>
            </div>
        </div>
    </div>

</body>
</html>
