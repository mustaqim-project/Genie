<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Genie ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --f: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --fd: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --ease: cubic-bezier(.25, .1, .25, 1);
        }
        body { font-family: var(--f); background-color: #0B1120; color: #F8FAFC; }
        .mk {
            display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            box-shadow: inset 0 0 0 .5px rgba(255, 255, 255, .35), 0 2px 6px rgba(0, 113, 227, .4);
            color: #fff; font-family: var(--fd); font-weight: 700; font-size: 20px;
        }
        .btn-apple { border-radius: 980px; transition: all .2s var(--ease); }
        .btn-apple:hover { filter: brightness(1.08); transform: scale(1.005); }
    </style>
</head>
<body class="h-full min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-3"><div class="mk">G</div></div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Lupa Kata Sandi</h1>
            <p class="text-slate-400 text-sm mt-1.5">
                Masukkan email akun perusahaan Anda untuk menerima tautan pemulihan sandi.
            </p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl">
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Terdaftar</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="nama@agungtrans.co.id"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <button type="submit"
                    class="btn-apple w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-sm font-bold shadow-lg shadow-sky-500/25">
                    Kirim Tautan Reset &rarr;
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
