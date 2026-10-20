<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Email Anda - Genie ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --f: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Plus Jakarta Sans", sans-serif; }
        body { font-family: var(--f); background-color: #0B1120; color: #F8FAFC; }
        .mk {
            display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            color: #fff; font-weight: 700; font-size: 20px;
        }
        .btn-apple { border-radius: 980px; }
    </style>
</head>
<body class="h-full min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-3"><div class="mk">G</div></div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Cek Email Anda</h1>
            <p class="text-slate-400 text-sm mt-1.5">
                Tautan pemulihan kata sandi telah disiapkan untuk <b class="text-white">{{ $email }}</b>.
            </p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-5">
            <div class="p-4 rounded-xl bg-sky-950/70 border border-sky-500/30 text-xs text-sky-300 leading-relaxed">
                <b>Mode Prototipe / Demo:</b> Tautan berlaku selama 30 menit dan hanya dapat digunakan 1 kali. Klik tombol di bawah untuk membuka simulasi tautan di email.
            </div>

            <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}"
                class="btn-apple block w-full text-center py-3.5 px-6 bg-white hover:bg-slate-100 text-slate-950 text-sm font-bold shadow-xs active:scale-95 transition-all">
                Buka Tautan Reset Sandi (Simulasi) &rarr;
            </a>

            <div class="pt-4 border-t border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
