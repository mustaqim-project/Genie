<!-- ============================================================== -->
<!-- GENIE ERP - APPLE macOS TOOLBAR (TOPBAR COMPONENT)             -->
<!-- Diadopsi secara lengkap dari Cooca Core (Apple HIG Standards)   -->
<!-- Spotlight Search, Live Status, Role Switcher, & Profile        -->
<!-- ============================================================== -->

<header class="sticky top-0 z-30 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border-b border-black/[0.08] dark:border-white/[0.08] transition-all">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 gap-3">
            
            <!-- LEFT: HAMBURGER & APPLE-STYLE CLEAN BREADCRUMBS -->
            <div class="flex items-center space-x-3 min-w-0">
                <!-- Mobile Drawer Button (44px tap target) -->
                <button @click="sidebarOpen = true" 
                        type="button"
                        class="lg:hidden min-w-[40px] min-h-[40px] flex items-center justify-center rounded-[8px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors active:scale-95 cursor-pointer"
                        aria-label="Buka Bilah Samping">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Clean Navigational Breadcrumb -->
                <nav class="flex items-center space-x-2 text-[12px] truncate" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="font-bold text-black dark:text-white hover:text-[#007AFF] transition-colors truncate">
                        GENIE
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-30 shrink-0"></i>
                    <span class="text-black/60 dark:text-white/60 font-medium truncate">@yield('title', 'Admin Panel')</span>
                </nav>
            </div>

            <!-- CENTER: SPOTLIGHT SEARCH (Apple macOS Spotlight Style) -->
            <div class="hidden md:flex items-center flex-1 max-w-sm mx-3">
                <button @click="quickSearchOpen = true" 
                        type="button"
                        class="w-full flex items-center justify-between h-9 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[12px] transition-all group active:scale-[0.99] cursor-pointer">
                    <div class="flex items-center space-x-2 truncate">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 group-hover:text-[#007AFF] transition-colors flex-shrink-0"></i>
                        <span class="truncate">Cari nomor aju, B/L, kontainer...</span>
                    </div>
                    <kbd class="hidden lg:inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono font-medium bg-white dark:bg-[#2C2C2E] text-black/50 dark:text-white/50 border border-black/10 dark:border-white/10 rounded-[5px] shadow-2xs leading-none">
                        ⌘K
                    </kbd>
                </button>
            </div>

            <!-- RIGHT: CEISA STATUS, SPOTLIGHT MOBILE, ROLE SWITCHER & USER AVATAR -->
            <div class="flex items-center space-x-2 sm:space-x-2.5 flex-shrink-0">

                <!-- Mobile Spotlight Trigger (44px target) -->
                <button @click="quickSearchOpen = true"
                        type="button"
                        class="md:hidden min-w-[40px] min-h-[40px] flex items-center justify-center rounded-[8px] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors active:scale-95"
                        aria-label="Cari Cepat">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>

                <!-- CEISA 4.0 Live Status Pill (Functional & Focused) -->
                <a href="{{ route('config.index') }}" 
                   class="hidden xl:inline-flex items-center space-x-2 px-2.5 py-1 rounded-full bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[11px] hover:border-black/15 dark:hover:border-white/15 transition-colors"
                   title="Status Gateway CEISA 4.0 DJBC (Klik untuk Diagnostik)">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    <span class="font-medium text-black/50 dark:text-white/50">CEISA 4.0</span>
                    <span class="font-semibold text-[#248A3D] dark:text-[#30D158]">Online</span>
                </a>

                <!-- Quick Role Switcher (Apple Segmented Pill) -->
                <div class="relative" x-data="{ openRole: false }">
                    <button @click="openRole = !openRole" 
                            type="button"
                            class="h-8 flex items-center space-x-1.5 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black/80 dark:text-white/80 text-[12px] font-medium border border-black/[0.06] dark:border-white/[0.08] transition-all cursor-pointer"
                            title="Beralih Peran RBAC Cepat">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span class="font-mono font-semibold text-[#007AFF] dark:text-[#0A84FF] text-[11px]">{{ auth()->user()->roles->first()?->code ?? 'USER' }}</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-black/40 dark:text-white/40 transition-transform duration-200" :class="{ 'rotate-180': openRole }"></i>
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
                         class="absolute right-0 mt-2 w-64 rounded-[12px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-xl py-1.5 z-50 text-[12px] max-h-96 overflow-y-auto">
                        <div class="px-3 py-1.5 border-b border-black/5 dark:border-white/5">
                            <div class="font-semibold text-black dark:text-white text-[11px]">Simulasi Hak Akses RBAC</div>
                            <p class="text-[10px] text-black/40 dark:text-white/40">Ganti peran untuk pengujian modul.</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('quick-login', 'R-ADM') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Administrator</span>
                                <span class="font-mono text-[10px] font-semibold text-black/40 dark:text-white/40">R-ADM</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-OWN') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Pimpinan (Owner)</span>
                                <span class="font-mono text-[10px] font-semibold text-black/40 dark:text-white/40">R-OWN</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Supervisor Dokumen</span>
                                <span class="font-mono text-[10px] font-semibold text-[#007AFF]">R-DOC-SPV</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-DOC') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Staff Dokumen</span>
                                <span class="font-mono text-[10px] font-semibold text-[#007AFF]">R-DOC</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-DO') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Staff Delivery Order</span>
                                <span class="font-mono text-[10px] font-semibold text-[#FF9500]">R-DO</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-TRF') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Staff Traffic</span>
                                <span class="font-mono text-[10px] font-semibold text-[#34C759]">R-TRF</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-FIN') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Finance & Pajak</span>
                                <span class="font-mono text-[10px] font-semibold text-[#34C759]">R-FIN</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-HR') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">HR & Payroll</span>
                                <span class="font-mono text-[10px] font-semibold text-[#AF52DE]">R-HR</span>
                            </a>
                            <a href="{{ route('quick-login', 'R-CS') }}" class="flex items-center justify-between px-3 py-1.5 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="font-medium text-black/80 dark:text-white/80">Customer Service</span>
                                <span class="font-mono text-[10px] font-semibold text-[#30B0C7]">R-CS</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notifications Bell Popover -->
                <div class="relative" x-data="{ openNotif: false }">
                    <button @click="openNotif = !openNotif" 
                            type="button" 
                            class="w-8 h-8 flex items-center justify-center rounded-[8px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors relative cursor-pointer"
                            title="Pemberitahuan Sistem">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
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
                         class="absolute right-0 mt-2 w-72 rounded-[12px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-xl py-1.5 z-50 text-[12px]">
                        <div class="px-3 py-2 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                            <span class="font-semibold text-black dark:text-white text-[11px]">Pemberitahuan Operasional</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-[#007AFF]/10 text-[#007AFF] font-medium font-mono">3 Baru</span>
                        </div>
                        <div class="divide-y divide-black/5 dark:divide-white/5">
                            <div class="p-2.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <div class="text-[#FF9500] font-semibold text-[11px] flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                    <span>Peringatan Demurrage</span>
                                </div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 leading-snug">Kontainer BL MAEU-982182910 tersisa 2 hari free time.</p>
                                <span class="text-[10px] text-black/35 dark:text-white/35 mt-1 block">10 menit yang lalu</span>
                            </div>
                            <div class="p-2.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <div class="text-[#007AFF] font-semibold text-[11px] flex items-center gap-1">
                                    <i data-lucide="receipt" class="w-3 h-3"></i>
                                    <span>Billing MPN G3 Terbit</span>
                                </div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 leading-snug">Kode billing 820260918237192 siap disetor.</p>
                                <span class="text-[10px] text-black/35 dark:text-white/35 mt-1 block">45 menit yang lalu</span>
                            </div>
                            <div class="p-2.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <div class="text-[#34C759] font-semibold text-[11px] flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    <span>SPPB Rilis (Jalur Hijau)</span>
                                </div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 leading-snug">Shipment SHP-202610-0001 siap Gate Out TPS.</p>
                                <span class="text-[10px] text-black/35 dark:text-white/35 mt-1 block">2 jam yang lalu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown (macOS Control Center Style) -->
                @if(auth()->check())
                <div class="relative" x-data="{ openProfile: false }">
                    <button @click="openProfile = !openProfile" 
                            type="button"
                            class="h-8 flex items-center space-x-2 px-1.5 rounded-[8px] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer"
                            aria-label="Menu Pengguna">
                        <div class="w-7 h-7 rounded-[7px] bg-black dark:bg-white text-white dark:text-black font-semibold text-[11px] flex items-center justify-center">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden xl:block text-left text-[12px] leading-tight pr-1">
                            <div class="font-semibold text-black dark:text-white truncate max-w-[100px]">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-black/40 dark:text-white/40 truncate">{{ auth()->user()->job_title ?? 'Staff' }}</div>
                        </div>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-black/40 dark:text-white/40 transition-transform duration-200" :class="{ 'rotate-180': openProfile }"></i>
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
                         class="absolute right-0 mt-2 w-52 rounded-[12px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-xl py-1 z-50 text-[12px]">
                        <div class="px-3 py-2 border-b border-black/5 dark:border-white/5">
                            <div class="font-semibold text-black dark:text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 font-mono truncate">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="{{ route('profile') }}" class="flex items-center space-x-2 px-3 py-1.5 text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.04] font-medium transition-colors">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                            <span>Profil & Akun</span>
                        </a>
                        <div class="my-0.5 border-t border-black/5 dark:border-white/5"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center space-x-2 px-3 py-1.5 text-[#FF3B30] hover:bg-[#FF3B30]/10 font-medium transition-colors cursor-pointer">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>
</header>
