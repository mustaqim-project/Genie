<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Tidak Berlaku - Genie ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0B1120; color: #F8FAFC; }
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
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Tautan Kedaluwarsa</h1>
            <p class="text-slate-400 text-sm mt-1.5">
                Tautan pemulihan sandi sudah tidak berlaku atau sudah pernah digunakan.
            </p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-5">
            <p class="text-xs text-slate-300 leading-relaxed text-center">
                Demi keamanan akun perusahaan, tautan reset hanya berlaku 30 menit. Silakan ajukan tautan baru untuk melanjutkan.
            </p>

            <a href="{{ route('password.request') }}"
                class="btn-apple block w-full text-center py-3.5 px-6 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-sm font-bold shadow-lg shadow-sky-500/25">
                Minta Tautan Baru &rarr;
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
