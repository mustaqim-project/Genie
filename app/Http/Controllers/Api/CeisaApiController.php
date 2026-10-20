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
}
