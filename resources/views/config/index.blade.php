@extends('layouts.app')

@section('title', 'Konfigurasi Gateway & Audit Log CEISA 4.0')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">
    <!-- Breadcrumb -->
    <div class="text-xs text-slate-500 font-medium mb-2">
        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
        <span class="text-slate-800">Gateway & Config</span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Konfigurasi Gateway & Audit Log CEISA 4.0</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pengaturan kredensial Host-to-Host DJBC dan pemantauan jejak audit API</p>
        </div>
        <div>
            <form action="{{ route('config.test-auth') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Uji Koneksi Token OAuth DJBC</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Profil Perusahaan & Kredensial (1 col) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3">Profil Entitas Terdaftar</h2>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Nama Perusahaan</span>
                        <span class="font-bold text-slate-800">{{ $config['company']['name'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NIB (Nomor Induk Berusaha)</span>
                        <span class="font-mono font-bold text-slate-800">{{ $config['company']['nib'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NPWP 15/16 Digit</span>
                        <span class="font-mono font-bold text-slate-800">{{ $config['company']['npwp'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">ID Unik Perusahaan (Nomor Aju)</span>
                        <span class="font-mono font-bold text-brand-600">{{ $config['company']['id_perusahaan'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Kantor Pabean Default</span>
                        <span class="font-bold text-slate-800">{{ $config['company']['default_kode_kantor'] }} - {{ $config['kantor_pabean'][$config['company']['default_kode_kantor']] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3">Parameter Gateway CEISA 4.0</h2>
                <div class="space-y-2.5 text-xs font-mono">
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Mode Lingkungan</span>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold {{ $config['api']['mock_mode'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $config['api']['mock_mode'] ? 'SANDBOX / SIMULASI' : 'LIVE GATEWAY DJBC' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Base URL Gateway</span>
                        <span class="text-slate-700 truncate block">{{ $config['api']['base_url'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Username API</span>
                        <span class="text-slate-700">{{ $config['api']['username'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">API Key Header</span>
                        <span class="text-slate-500">beacukai-api-key: {{ substr($config['api']['api_key'], 0, 8) }}***</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan REST API H2H untuk ERP Eksternal (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 mb-2">Panduan Integrasi REST API H2H (Server-to-Server)</h2>
            <p class="text-xs text-slate-500 mb-4">
                Sistem eksternal (seperti ERP COOCA, WMS, atau In-House System lainnya) dapat mengirimkan data pabean secara otomatis ke endpoint service <strong>PATS_CEISA</strong> berikut:
            </p>

            <div class="space-y-4 text-xs font-mono">
                <!-- Endpoint 1: BC 2.0 -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/bc-20/submit</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px] mb-2">Submit dokumen Impor BC 2.0 (PIB), auto-generate 26-digit Nomor Aju, dan teruskan ke CEISA 4.0.</p>
                    <div class="text-[10px] text-slate-400 font-sans">
                        Headers: <code class="text-slate-600">Content-Type: application/json</code>
                    </div>
                </div>

                <!-- Endpoint 2: BC 3.0 -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/bc-30/submit</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px] mb-2">Submit dokumen Ekspor BC 3.0 (PEB), auto-generate 26-digit Nomor Aju, dan teruskan ke CEISA 4.0.</p>
                </div>

                <!-- Endpoint 3: Status & Respon -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">GET</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/dokumen/{nomorAju}/status</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px] mb-2">Tarik status terkini dan respon resmi (Billing MPN G3 / SPPB / NPE) berdasarkan Nomor Aju.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Audit Trail Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-900">Jejak Audit Komunikasi API CEISA 4.0 (Immutable Logs)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh request dan response HTTP ke gateway Bea Cukai</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nomor Aju</th>
                        <th class="py-3 px-4">Method & Endpoint</th>
                        <th class="py-3 px-4">Status Code</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                {{ $log->nomor_aju ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono">
                                <span class="px-1.5 py-0.5 rounded font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="text-slate-700 ml-1">{{ $log->endpoint }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold {{ $log->status_code === 200 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->status_code }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $log->duration_ms }} ms</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->is_success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $log->is_success ? 'SUCCESS' : 'FAILED' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada log transaksi pabean tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
