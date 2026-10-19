@extends('layouts.app')

@section('title', 'CRM & WhatsApp Business')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">CRM</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Customer Relationship & WhatsApp Omnichannel</h1>
            <p class="text-xs text-slate-500 mt-0.5">Inbox percakapan terpadu WhatsApp Business (Meta WABA), sales pipeline, dan portal klien via link token OTP</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                Meta WABA Session Active (24h)
            </span>
        </div>
    </div>

    <!-- 3-Column CRM Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1: Sales Pipeline -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <h2 class="font-bold text-xs text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                Sales Pipeline (Lead & Deal)
            </h2>
            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">1. Lead Masuk (Inbound Inquiry)</span>
                    <span class="font-bold text-slate-800">3 Kontak Perusahaan</span>
                    <p class="text-[10px] text-slate-500 mt-0.5">Tanya tarif forwarding impor FCL Shanghai-Jkt</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100">
                    <span class="text-[10px] font-bold text-blue-500 block uppercase">2. Kualifikasi & Cek Lartas</span>
                    <span class="font-bold text-blue-900">1 Dokumen</span>
                    <p class="text-[10px] text-blue-700 mt-0.5">Komoditas kosmetik / izin BPOM</p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <span class="text-[10px] font-bold text-emerald-500 block uppercase">3. Surat Kuasa PPJK Terbit</span>
                    <span class="font-bold text-emerald-900">PT Maju Bersama Logistik</span>
                    <p class="text-[10px] text-emerald-700 mt-0.5">Siap order shipment perdana</p>
                </div>
            </div>
        </div>

        <!-- Col 2: Inbox WhatsApp Terpadu -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h2 class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                    Inbox WhatsApp & Riwayat Percakapan
                </h2>
                <span class="text-xs text-slate-400">Portal Klien via Link Aktif</span>
            </div>

            <div class="space-y-3 flex-1">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 text-xs transition-colors flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-bold text-slate-800">SHENZHEN TECH INNOVATION CO., LTD.</span>
                            <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold">WHATSAPP</span>
                        </div>
                        <p class="text-slate-600 text-[11px]">"Halo PT Agung Trans, apakah SPPB untuk no aju 04032001234520261012000001 sudah keluar dari Bea Cukai?"</p>
                        <div class="text-[10px] text-slate-400 mt-1">Status Shipment Tertaut: <strong class="text-emerald-600">SPPB DITERBITKAN (Jalur Hijau)</strong></div>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">10m lalu</span>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 text-xs transition-colors flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-bold text-slate-800">PACIFIC DISTRIBUTORS & COFFEE ROASTERS</span>
                            <span class="px-1.5 py-0.2 rounded bg-blue-100 text-blue-800 text-[9px] font-bold">PORTAL LINK</span>
                        </div>
                        <p class="text-slate-600 text-[11px]">Klien mengunggah dokumen susulan: Certificate of Origin (Form AK / Form D) via link token OTP.</p>
                        <div class="text-[10px] text-slate-400 mt-1">Status Shipment Tertaut: <strong class="text-indigo-600">NPE TERBIT (Ekspor Siap Muat)</strong></div>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">1j lalu</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex items-center space-x-2">
                    <input type="text" placeholder="Tulis balasan pesan WhatsApp resmi (Meta Template)..." class="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm">
                        Kirim Balasan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
