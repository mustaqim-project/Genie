<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kata Sandi Baru - Genie ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0B1120; color: #F8FAFC; }
        .mk {
            display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            color: #fff; font-weight: 700; font-size: 20px;
        }
        .btn-apple { border-radius: 980px; transition: all .2s cubic-bezier(.25, .1, .25, 1); }
        .btn-apple:hover { filter: brightness(1.08); transform: scale(1.005); }
    </style>
</head>
<body class="h-full min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-3"><div class="mk">G</div></div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Buat Kata Sandi Baru</h1>
            <p class="text-slate-400 text-sm mt-1.5">
                Untuk akun <b class="text-white">{{ $email }}</b>
            </p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl">
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi Baru *</label>
                    <input id="password" type="password" name="password" required autofocus
                        placeholder="Minimal 10 karakter"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Password Strength Meter Visual (Apple Prototype) -->
                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 space-y-2.5">
                    <div class="flex gap-1.5">
                        <div id="bar1" class="h-1.5 flex-1 rounded-full bg-slate-800 transition-colors"></div>
                        <div id="bar2" class="h-1.5 flex-1 rounded-full bg-slate-800 transition-colors"></div>
                        <div id="bar3" class="h-1.5 flex-1 rounded-full bg-slate-800 transition-colors"></div>
                        <div id="bar4" class="h-1.5 flex-1 rounded-full bg-slate-800 transition-colors"></div>
                        <div id="bar5" class="h-1.5 flex-1 rounded-full bg-slate-800 transition-colors"></div>
                    </div>

                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Kekuatan Sandi:</span>
                        <span id="strengthLabel" class="font-bold text-slate-500">Belum diisi</span>
                    </div>

                    <div class="space-y-1 text-[11px] pt-1 border-t border-slate-800">
                        <div id="rule-len" class="text-slate-500 flex items-center gap-1.5">
                            <span class="indicator">&#9675;</span> Minimal 10 karakter
                        </div>
                        <div id="rule-case" class="text-slate-500 flex items-center gap-1.5">
                            <span class="indicator">&#9675;</span> Huruf besar (A-Z) dan huruf kecil (a-z)
                        </div>
                        <div id="rule-num" class="text-slate-500 flex items-center gap-1.5">
                            <span class="indicator">&#9675;</span> Memuat angka (0-9)
                        </div>
                        <div id="rule-sym" class="text-slate-500 flex items-center gap-1.5">
                            <span class="indicator">&#9675;</span> Memuat simbol (@, #, $, %, dll)
                        </div>
                        <div id="rule-email" class="text-slate-500 flex items-center gap-1.5">
                            <span class="indicator">&#9675;</span> Tidak memuat nama email pengguna
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">Ulangi Kata Sandi Baru *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        placeholder="Ketik ulang sandi"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <button type="submit"
                    class="btn-apple w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-sm font-bold shadow-lg shadow-sky-500/25">
                    Simpan Sandi Baru & Masuk &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Password Strength Meter Real-Time Script -->
    <script>
        const pwd = document.getElementById('password');
        const userEmail = @json($email);
        const emailPrefix = (userEmail.split('@')[0] || '').toLowerCase();

        const ruleLen = document.getElementById('rule-len');
        const ruleCase = document.getElementById('rule-case');
        const ruleNum = document.getElementById('rule-num');
        const ruleSym = document.getElementById('rule-sym');
        const ruleEmail = document.getElementById('rule-email');

        const bars = [
            document.getElementById('bar1'),
            document.getElementById('bar2'),
            document.getElementById('bar3'),
            document.getElementById('bar4'),
            document.getElementById('bar5')
        ];
        const strengthLabel = document.getElementById('strengthLabel');

        function updateRule(elem, passed) {
            if (passed) {
                elem.className = 'text-emerald-400 flex items-center gap-1.5';
                elem.querySelector('.indicator').innerHTML = '&#10003;';
            } else {
                elem.className = 'text-slate-500 flex items-center gap-1.5';
                elem.querySelector('.indicator').innerHTML = '&#9675;';
            }
        }

        pwd.addEventListener('input', () => {
            const val = pwd.value;
            const p1 = val.length >= 10;
            const p2 = /[A-Z]/.test(val) && /[a-z]/.test(val);
            const p3 = /\d/.test(val);
            const p4 = /[^A-Za-z0-9]/.test(val);
            const p5 = val.length > 0 && !val.toLowerCase().includes(emailPrefix);

            updateRule(ruleLen, p1);
            updateRule(ruleCase, p2);
            updateRule(ruleNum, p3);
            updateRule(ruleSym, p4);
            updateRule(ruleEmail, p5);

            const score = [p1, p2, p3, p4, p5].filter(Boolean).length;

            bars.forEach((b, idx) => {
                b.className = 'h-1.5 flex-1 rounded-full transition-colors ' +
                    (idx < score ? (score <= 2 ? 'bg-rose-500' : (score <= 4 ? 'bg-amber-400' : 'bg-emerald-400')) : 'bg-slate-800');
            });

            if (!val) {
                strengthLabel.textContent = 'Belum diisi';
                strengthLabel.className = 'font-bold text-slate-500';
            } else if (score <= 2) {
                strengthLabel.textContent = 'Lemah';
                strengthLabel.className = 'font-bold text-rose-400';
            } else if (score <= 4) {
                strengthLabel.textContent = 'Sedang';
                strengthLabel.className = 'font-bold text-amber-400';
            } else {
                strengthLabel.textContent = 'Sangat Kuat';
                strengthLabel.className = 'font-bold text-emerald-400';
            }
        });
    </script>
</body>
</html>
