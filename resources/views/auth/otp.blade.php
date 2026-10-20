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
            <!-- Alert & Erros -->
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

                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-slate-400 space-y-1 text-center font-mono">
                    <p>Kode simulasi resmi: <strong class="text-sky-300">{{ $twoFaData['code'] ?? '123456' }}</strong></p>
                    <p class="text-[11px] text-slate-500">Bypass prototipe: <code class="text-amber-400">123456</code> (Maks 3x percobaan)</p>
                </div>

                <button type="submit"
                    class="btn-apple w-full py-3.5 px-6 bg-white hover:bg-slate-100 text-slate-950 text-sm font-bold shadow-xs active:scale-95 transition-all cursor-pointer">
                    Verifikasi Kode & Masuk &rarr;
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
