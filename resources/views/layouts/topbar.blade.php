<!-- ============================================================== -->
<!-- PATS_CEISA ERP - APPLE DESIGN TOPBAR COMPONENT                 -->
<!-- Inspired by macOS Toolbar & iOS Glassmorphic Navigation Bar    -->
<!-- ============================================================== -->

<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200/70 shadow-[0_1px_2px_rgba(0,0,0,0.02)] transition-all">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-3">
            
            <!-- LEFT: HAMBURGER & APPLE-STYLE BREADCRUMBS -->
            <div class="flex items-center space-x-3 min-w-0">
                <!-- Mobile Drawer Button (44px tap target) -->
                <button @click="sidebarOpen = true" 
                        type="button"
                        class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors active:scale-95"
                        aria-label="Buka Navigasi Bilah Samping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Breadcrumb Path Indicator -->
                <div class="flex items-center space-x-2 text-xs truncate">
                    <a href="{{ route('dashboard') }}" class="font-bold text-slate-900 hover:text-sky-600 flex items-center space-x-1.5 transition-colors">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="tracking-tight font-bold">GENIE</span>
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-600 font-medium truncate">@yield('title', 'Admin Panel')</span>
                </div>
            </div>

            <!-- CENTER: SPOTLIGHT SEARCH (Apple macOS Spotlight Style) -->
            <div class="hidden md:flex items-center flex-1 max-w-sm mx-2">
                <button @click="quickSearchOpen = true" 
                        type="button"
                        class="w-full flex items-center justify-between px-3.5 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200/60 text-slate-500 text-xs transition-all group active:scale-[0.99]">
                    <div class="flex items-center space-x-2 truncate">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-sky-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="truncate">Cari Job, B/L, Kontainer, Invoice...</span>
                    </div>
                    <kbd class="hidden lg:inline-flex items-center px-1.5 py-0.5 text-[9px] font-mono font-bold bg-white text-slate-500 border border-slate-200 rounded-md shadow-2xs">
                        ⌘K
                    </kbd>
                </button>
            </div>

            <!-- RIGHT: CEISA STATUS, SPOTLIGHT MOBILE, ROLE SWITCHER & USER AVATAR -->
            <div class="flex items-center space-x-2 sm:space-x-2.5 flex-shrink-0">

                <!-- Mobile Spotlight Trigger (44px target) -->
                <button @click="quickSearchOpen = true"
                        type="button"
                        class="md:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors active:scale-95"
                        aria-label="Cari Cepat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- CEISA 4.0 Live Status Indicator -->
                <div class="hidden xl:flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-100/90 border border-slate-200/70 text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-semibold text-slate-700">CEISA 4.0:</span>
                    <span class="font-mono text-emerald-700 font-bold">Online</span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-slate-400 font-mono">{{ now()->format('H:i') }} WIB</span>
                </div>

                <!-- Quick Role Switcher (Apple Segmented Pill) -->
                <div class="relative" x-data="{ openRole: false }">
                    <button @click="openRole = !openRole" 
                            type="button"
                            class="min-h-[44px] flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-100/90 hover:bg-slate-200/80 text-slate-700 text-xs font-semibold border border-slate-200/70 transition-all active:scale-95"
                            title="Beralih Peran RBAC Cepat">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span class="hidden sm:inline text-slate-500 font-medium">Role:</span>
                        <span class="font-mono font-bold text-sky-700">[{{ auth()->user()->roles->first()?->code ?? 'USER' }}]</span>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openRole }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Role Simulation -->
                    <div x-show="openRole" 
                         x-cloak 
                         @click.outside="openRole = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 rounded-2xl bg-white/98 backdrop-blur-2xl border border-slate-200/80 shadow-2xl py-2 z-50 text-xs max-h-96 overflow-y-auto">
                        <div class="px-3.5 py-2 border-b border-slate-100">
                            <div class="font-bold text-slate-900">Simulasi Wewenang RBAC</div>
                            <p class="text-[10px] text-slate-400">Pilih peran untuk menguji hak akses modul.</p>
                        </div>
                        <div class="divide-y divide-slate-50 py-1">
                            <a href="{{ route('quick-login', 'R-ADM') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-slate-900">[R-ADM]</span> Administrator Sistem
                            </a>
                            <a href="{{ route('quick-login', 'R-OWN') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-slate-900">[R-OWN]</span> Direktur Utama / Owner
                            </a>
                            <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-sky-700">[R-DOC-SPV]</span> Supervisor Dokumen
                            </a>
                            <a href="{{ route('quick-login', 'R-DOC') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-sky-700">[R-DOC]</span> Staff Dokumen (PIB/PEB)
                            </a>
                            <a href="{{ route('quick-login', 'R-DO') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-amber-700">[R-DO]</span> Staff Delivery Order
                            </a>
                            <a href="{{ route('quick-login', 'R-TRF') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-emerald-700">[R-TRF]</span> Staff Traffic & Truk
                            </a>
                            <a href="{{ route('quick-login', 'R-FLD') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-emerald-700">[R-FLD]</span> Staff Lapangan (Behandle)
                            </a>
                            <a href="{{ route('quick-login', 'R-FIN') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-green-700">[R-FIN]</span> Finance & Tagihan
                            </a>
                            <a href="{{ route('quick-login', 'R-HR') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-pink-700">[R-HR]</span> Human Resources (HRM)
                            </a>
                            <a href="{{ route('quick-login', 'R-CS') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-teal-700">[R-CS]</span> Customer Service (CRM)
                            </a>
                            <a href="{{ route('quick-login', 'R-MGR') }}" class="block px-3.5 py-2 hover:bg-sky-50 text-slate-700 font-medium transition-colors">
                                <span class="font-mono font-bold text-purple-700">[R-MGR]</span> Operations Manager
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notifications Bell Popover -->
                <div class="relative" x-data="{ openNotif: false }">
                    <button @click="openNotif = !openNotif" 
                            type="button" 
                            class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors relative active:scale-95"
                            title="Pemberitahuan Sistem">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>

                    <!-- Dropdown Notifications Menu -->
                    <div x-show="openNotif" 
                         x-cloak 
                         @click.outside="openNotif = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 rounded-2xl bg-white/98 backdrop-blur-2xl border border-slate-200/80 shadow-2xl py-2 z-50 text-xs">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-bold text-slate-900">Pemberitahuan</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 font-bold font-mono">3 Baru</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <div class="p-3 hover:bg-slate-50 transition-colors">
                                <div class="text-amber-700 font-bold text-[11px]">⚠️ Peringatan Demurrage</div>
                                <p class="text-[11px] text-slate-600 mt-0.5">Kontainer BL MAEU-982182910 tersisa 2 hari free time.</p>
                                <span class="text-[9px] text-slate-400">10 menit yang lalu</span>
                            </div>
                            <div class="p-3 hover:bg-slate-50 transition-colors">
                                <div class="text-sky-700 font-bold text-[11px]">📄 Billing MPN G3 Terbit</div>
                                <p class="text-[11px] text-slate-600 mt-0.5">Kode billing 820260918237192 siap dibayar via Bank Persepsi.</p>
                                <span class="text-[9px] text-slate-400">45 menit yang lalu</span>
                            </div>
                            <div class="p-3 hover:bg-slate-50 transition-colors">
                                <div class="text-emerald-700 font-bold text-[11px]">✅ SPPB Rilis (Jalur Hijau)</div>
                                <p class="text-[11px] text-slate-600 mt-0.5">Shipment SHP-202610-0001 siap proses Gate Out TPS.</p>
                                <span class="text-[9px] text-slate-400">2 jam yang lalu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown (macOS Control Center Style) -->
                @if(auth()->check())
                <div class="relative" x-data="{ openProfile: false }">
                    <button @click="openProfile = !openProfile" 
                            type="button"
                            class="min-h-[44px] flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors active:scale-95"
                            aria-label="Menu Pengguna">
                        <div class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden xl:block text-left text-xs">
                            <div class="font-bold text-slate-900 truncate max-w-[110px]">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ auth()->user()->job_title ?? 'Staff' }}</div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openProfile }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openProfile" 
                         x-cloak 
                         @click.outside="openProfile = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white/98 backdrop-blur-2xl border border-slate-200/80 shadow-2xl py-2 z-50 text-xs">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <div class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono truncate">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="{{ route('profile') }}" class="flex items-center space-x-2 px-4 py-2.5 text-slate-700 hover:bg-slate-50 font-medium transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profil & Keamanan Akun</span>
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center space-x-2 px-4 py-2.5 text-rose-600 hover:bg-rose-50 font-bold transition-colors">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>
</header>
