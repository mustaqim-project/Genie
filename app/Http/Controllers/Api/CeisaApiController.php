<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CeisaDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC20Service;
use App\Services\Ceisa\DocumentBC30Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CeisaApiController extends Controller
{
    protected CeisaDocumentService $documentService;
    protected \App\Services\Ceisa\CeisaKursService $kursService;
    protected \App\Services\Ceisa\CeisaHsCodeService $hsCodeService;

    public function __construct(
        CeisaDocumentService $documentService,
        \App\Services\Ceisa\CeisaKursService $kursService,
        \App\Services\Ceisa\CeisaHsCodeService $hsCodeService
    ) {
        $this->documentService = $documentService;
        $this->kursService = $kursService;
        $this->hsCodeService = $hsCodeService;
    }

    /**
     * Endpoint H2H: Submit Dokumen Impor BC 2.0 (PIB)
     */
    public function submitBC20(Request $request): JsonResponse
    {
        if ((bool) config('ceisa.api.read_only', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Mode Tarik Data Saja (Read-Only) aktif: upload BC 2.0 ke CEISA diblokir.',
            ], 403);
        }

        $validated = $request->validate([
            'kode_kantor' => 'nullable|string',
            'ndbm_kurs' => 'nullable|numeric',
            'kode_valuta' => 'nullable|string',
            'fob' => 'required|numeric',
            'freight' => 'required|numeric',
            'asuransi' => 'required|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'supplier_nama' => 'required|string',
            'supplier_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC20Service::generateNomorAju($validated['kode_kantor'] ?? null);
        $validated['nomor_aju'] = $nomorAju;
        $payload = DocumentBC20Service::buildPayload($validated);

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '20',
            'kode_kantor' => $payload['header']['kodeKantor'],
            'nama_kantor' => config('ceisa.kantor_pabean.' . $payload['header']['kodeKantor'], 'Kantor Bea Cukai'),
            'status' => 'DRAFT',
            'status_description' => 'Disiapkan via REST API H2H',
            'kode_valuta' => $payload['header']['kodeValuta'],
            'ndbm_kurs' => $payload['header']['ndbm'],
            'total_nilai' => $payload['header']['cif'],
            'total_nilai_idr' => $payload['header']['cifRupiah'],
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 100,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['supplier_nama'],
            'negara_mitra_luar_negeri' => $validated['supplier_negara'],
            'raw_payload' => $payload,
        ]);

        $submitResult = $this->documentService->submit($doc);

        return response()->json([
            'success' => true,
            'nomor_aju' => $nomorAju,
            'document_id' => $doc->id,
            'status' => $doc->fresh()->status,
            'ceisa_response' => $submitResult['data'] ?? [],
        ]);
    }

    /**
     * Endpoint H2H: Submit Dokumen Ekspor BC 3.0 (PEB)
     */
    public function submitBC30(Request $request): JsonResponse
    {
        if ((bool) config('ceisa.api.read_only', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Mode Tarik Data Saja (Read-Only) aktif: upload BC 3.0 ke CEISA diblokir.',
            ], 403);
        }

        $validated = $request->validate([
            'kode_kantor' => 'nullable|string',
            'ndbm_kurs' => 'nullable|numeric',
            'kode_valuta' => 'nullable|string',
            'fob' => 'required|numeric',
            'freight' => 'nullable|numeric',
            'asuransi' => 'nullable|numeric',
            'bruto' => 'required|numeric',
            'netto' => 'required|numeric',
            'buyer_nama' => 'required|string',
            'buyer_negara' => 'required|string',
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'nama_sarana_pengangkut' => 'required|string',
            'pelabuhan_muat' => 'required|string',
            'pelabuhan_bongkar' => 'required|string',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC30Service::generateNomorAju($validated['kode_kantor'] ?? null);
        $validated['nomor_aju'] = $nomorAju;
        $payload = DocumentBC30Service::buildPayload($validated);

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '30',
            'kode_kantor' => $payload['header']['kodeKantor'],
            'nama_kantor' => config('ceisa.kantor_pabean.' . $payload['header']['kodeKantor'], 'Kantor Bea Cukai'),
            'status' => 'DRAFT',
            'status_description' => 'Disiapkan via REST API H2H',
            'kode_valuta' => $payload['header']['kodeValuta'],
            'ndbm_kurs' => $payload['header']['ndbm'],
            'total_nilai' => $payload['header']['fob'],
            'total_nilai_idr' => $payload['header']['fobRupiah'],
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 50,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['buyer_nama'],
            'negara_mitra_luar_negeri' => $validated['buyer_negara'],
            'raw_payload' => $payload,
        ]);

        $submitResult = $this->documentService->submit($doc);

        return response()->json([
            'success' => true,
            'nomor_aju' => $nomorAju,
            'document_id' => $doc->id,
            'status' => $doc->fresh()->status,
            'ceisa_response' => $submitResult['data'] ?? [],
        ]);
    }

    /**
     * Endpoint H2H: Cek Status & Respon Dokumen by Nomor Aju
     */
    public function getStatus(string $nomorAju): JsonResponse
    {
        $doc = CeisaDocument::where('nomor_aju', $nomorAju)->first();

        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan'], 404);
        }

        $this->documentService->checkStatus($doc);
        $this->documentService->fetchResponse($doc);

        return response()->json([
            'success' => true,
            'data' => $doc->fresh(),
        ]);
    }

    /**
     * Endpoint: Dapatkan Kurs NDPBM Kepabeanan Aktif
     */
    public function getKurs(string $valuta, Request $request): JsonResponse
    {
        $date = $request->query('tanggal');
        $kurs = $this->kursService->getActiveKurs($valuta, $date);

        return response()->json($kurs);
    }

    /**
     * Endpoint: Sinkronisasi Seluruh Kurs Utama dari CEISA 4.0
     */
    public function syncKurs(): JsonResponse
    {
        $rates = $this->kursService->syncMajorRates();

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi kurs pabean berhasil dilakukan.',
            'data' => $rates,
        ]);
    }

    /**
     * Endpoint: Dapatkan Pos Tarif HS Code, Komponen Bea/Pajak & Regulasi Lartas
     */
    public function getTarifHs(string $hsCode): JsonResponse
    {
        $result = $this->hsCodeService->getTariffAndLartas($hsCode);

        return response()->json($result);
    }

    /**
     * Endpoint API: Rekomendasi Cerdas HS Code BTKI 2022 (Single / Batch Item)
     */
    public function recommendHs(Request $request): JsonResponse
    {
        $direction = $request->input('direction', 'IMPORT');
        $limit = (int) $request->input('limit', 5);

        // Jika request batch items
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            $recommendations = $this->hsCodeService->recommendForMultipleItems($items, $direction);

            return response()->json([
                'success' => true,
                'mode' => 'batch',
                'count' => count($recommendations),
                'data' => $recommendations,
            ]);
        }

        // Single query
        $query = $request->input('keyword') ?? $request->input('uraian') ?? $request->input('text') ?? $request->input('q') ?? '';
        if (empty(trim($query))) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter kata kunci/uraian barang tidak boleh kosong.',
                'candidates' => [],
            ], 422);
        }

        $candidates = $this->hsCodeService->recommendHsCandidates($query, $direction, $limit);

        return response()->json([
            'success' => true,
            'mode' => 'single',
            'query' => $query,
            'count' => count($candidates),
            'top_recommendation' => $candidates[0] ?? null,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Endpoint Autocomplete / Live Search Database BTKI 2022
     */
    public function searchHs(Request $request): JsonResponse
    {
        $q = trim((string) ($request->input('q') ?? $request->input('keyword') ?? ''));
        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'count' => 0, 'data' => []]);
        }

        $cleanQ = str_replace(['.', ' ', '-'], '', $q);

        try {
            $results = \App\Models\HsCode::query()
                ->where('code', 'like', "%{$q}%")
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(code, '.', ''), ' ', ''), '-', '') LIKE ?", ["%{$cleanQ}%"])
                ->orWhere('description', 'like', "%{$q}%")
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    return [
                        'code' => $item->code,
                        'clean_code' => str_replace(['.', ' ', '-'], '', $item->code),
                        'description' => $item->description,
                        'duty_bm' => (float) ($item->duty_bm ?? 0),
                        'duty_ppn' => (float) ($item->duty_ppn ?? 11),
                        'duty_pph' => (float) ($item->duty_pph ?? 2.5),
                        'duty_bk' => (float) ($item->duty_bk ?? 0),
                        'satuan' => $item->satuan ?? 'PCE',
                        'has_lartas' => (bool) $item->has_lartas,
                        'lartas_desc' => $item->lartas_desc ?? ($item->has_lartas ? 'LARTAS' : 'NON LARTAS'),
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari pos tarif: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Endpoint: Sinkronisasi Master Pos Tarif BTKI 2022 dari Bea Cukai ke Database
     */
    public function syncHsCodes(\App\Services\Ceisa\CeisaBtkiTariffSyncService $syncService): JsonResponse
    {
        $result = $syncService->syncFromBtkiRegistry(true);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => 'Sinkronisasi master data HS Code BTKI 2022 selesai.',
            'data' => $result,
        ]);
    }

    /**
     * Endpoint Webhook / Callback Resmi dari CEISA 4.0 DJBC
     * Bea Cukai mengirimkan callback HTTP POST secara real-time saat status pabean,
     * penjaluran (Jalur Merah/Kuning/Hijau), kode billing MPN G3, SPPB, atau NPE terbit.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $payload = $request->all();

        // Verifikasi optional webhook secret / token jika dikonfigurasi di Setting Genie
        $configuredSecret = (string) config('ceisa.api.webhook_secret', '');
        if ($configuredSecret !== '') {
            $providedSecret = (string) (
                $request->header('X-CEISA-Webhook-Secret')
                ?? $request->header('X-Webhook-Token')
                ?? $request->query('token')
                ?? ''
            );

            if (!hash_equals($configuredSecret, $providedSecret)) {
                \App\Models\CeisaApiLog::create([
                    'nomor_aju' => $request->input('nomorAju') ?? $request->input('nomor_aju'),
                    'endpoint' => '/api/v1/ceisa/webhook',
                    'method' => 'POST',
                    'status_code' => 401,
                    'request_header' => $request->headers->all(),
                    'request_body' => $payload,
                    'response_body' => ['status' => 'error', 'message' => 'Token callback webhook CEISA tidak valid.'],
                    'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
                    'is_success' => false,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Token callback webhook CEISA tidak sah.',
                ], 401);
            }
        }

        $nomorAju = $request->input('nomorAju') 
            ?? $request->input('nomor_aju') 
            ?? $request->input('dataStatus.0.nomorAju')
            ?? $request->input('dataRespon.0.nomorAju');

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if (!$nomorAju) {
            \App\Models\CeisaApiLog::create([
                'nomor_aju' => null,
                'endpoint' => '/api/v1/ceisa/webhook',
                'method' => 'POST',
                'status_code' => 400,
                'request_header' => $request->headers->all(),
                'request_body' => $payload,
                'response_body' => ['status' => 'error', 'message' => 'Nomor Aju tidak ditemukan dalam payload webhook.'],
                'duration_ms' => $duration,
                'is_success' => false,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Parameter nomorAju wajib ada dalam payload.',
            ], 400);
        }

        // Cari dokumen pabean dengan format standar atau format tanda hubung
        $cleanAju = str_replace('-', '', $nomorAju);
        $doc = CeisaDocument::where('nomor_aju', $nomorAju)
            ->orWhereRaw("REPLACE(nomor_aju, '-', '') = ?", [$cleanAju])
            ->first();

        $updates = [
            'last_response' => $payload,
        ];

        // 1. Tangani Data Status & Penjaluran
        if (!empty($payload['dataStatus']) && is_array($payload['dataStatus'])) {
            $statusItem = end($payload['dataStatus']);
            if (!empty($statusItem['nomorDaftar'])) {
                $updates['nomor_daftar'] = $statusItem['nomorDaftar'];
            }
            if (!empty($statusItem['tanggalDaftar'])) {
                $updates['tanggal_daftar'] = $statusItem['tanggalDaftar'];
            }
            if (!empty($statusItem['keterangan'])) {
                $updates['status_description'] = $statusItem['keterangan'];
            }
        }

        if (!empty($payload['nomorDaftar'])) {
            $updates['nomor_daftar'] = $payload['nomorDaftar'];
        }
        if (!empty($payload['tanggalDaftar'])) {
            $updates['tanggal_daftar'] = $payload['tanggalDaftar'];
        }
        if (!empty($payload['jalur'])) {
            $updates['jalur'] = strtoupper($payload['jalur']);
        }
        if (!empty($payload['status'])) {
            $updates['status'] = $payload['status'];
        }
        if (!empty($payload['keterangan'])) {
            $updates['status_description'] = $payload['keterangan'];
        }

        // 2. Tangani Respon Resmi (SPPB / NPE / Reject)
        if (!empty($payload['dataRespon']) && is_array($payload['dataRespon'])) {
            $responItem = end($payload['dataRespon']);
            $kodeRespon = strtoupper($responItem['kodeRespon'] ?? '');

            if ($kodeRespon === 'SPPB') {
                $updates['nomor_sppb_npe'] = $responItem['nomorRespon'] ?? null;
                $updates['tanggal_sppb_npe'] = $responItem['tanggalRespon'] ?? now();
                $updates['status'] = 'SPPB';
            } elseif ($kodeRespon === 'NPE') {
                $updates['nomor_sppb_npe'] = $responItem['nomorRespon'] ?? null;
                $updates['tanggal_sppb_npe'] = $responItem['tanggalRespon'] ?? now();
                $updates['status'] = 'NPE';
            } elseif (str_contains($kodeRespon, 'REJECT') || str_contains($kodeRespon, 'TOLAK')) {
                $updates['status'] = 'REJECT';
            }

            if (!empty($responItem['keterangan'])) {
                $updates['status_description'] = $responItem['keterangan'];
            }
        }

        // 3. Tangani Kode Billing MPN G3
        if (!empty($payload['billing'])) {
            $updates['kode_billing'] = $payload['billing']['kodeBilling'] ?? null;
            $updates['total_billing'] = $payload['billing']['totalNominal'] ?? null;
            $updates['tgl_jatuh_tempo_billing'] = $payload['billing']['tglKadaluarsa'] ?? null;
            $updates['status_billing'] = 'UNPAID';
        }

        // 4. Fallback root SPPB / NPE
        if (!empty($payload['sppb'])) {
            $updates['nomor_sppb_npe'] = $payload['sppb']['nomorSppb'] ?? null;
            $updates['tanggal_sppb_npe'] = $payload['sppb']['tanggalSppb'] ?? null;
            $updates['status'] = 'SPPB';
        }
        if (!empty($payload['npe'])) {
            $updates['nomor_sppb_npe'] = $payload['npe']['nomorNpe'] ?? null;
            $updates['tanggal_sppb_npe'] = $payload['npe']['tanggalNpe'] ?? null;
            $updates['status'] = 'NPE';
        }

        if ($doc) {
            $doc->update($updates);
            $doc->refresh();

            // Update status shipment sesuai tahapan di Genie jika terhubung
            if ($doc->shipment) {
                $shipUpdates = [
                    'status_tahap' => \App\Services\Ceisa\CeisaDocumentSyncService::resolveShipmentStage(
                        $doc->status,
                        $doc->jalur,
                        $doc->kode_billing,
                        $doc->shipment->status_tahap
                    ),
                ];
                if (!empty($doc->jalur)) {
                    $shipUpdates['customs_channel'] = strtoupper($doc->jalur);
                }
                $doc->shipment->update($shipUpdates);
            }
        }

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        \App\Models\CeisaApiLog::create([
            'nomor_aju' => $nomorAju,
            'endpoint' => '/api/v1/ceisa/webhook',
            'method' => 'POST',
            'status_code' => 200,
            'request_header' => $request->headers->all(),
            'request_body' => $payload,
            'response_body' => [
                'status' => 'success',
                'message' => 'Webhook berhasil diproses',
                'document_found' => (bool) $doc,
                'nomor_aju' => $nomorAju,
            ],
            'duration_ms' => $duration,
            'is_success' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Callback webhook CEISA 4.0 berhasil diterima dan diproses.',
            'nomor_aju' => $nomorAju,
            'document_updated' => (bool) $doc,
        ], 200);
    }
}
