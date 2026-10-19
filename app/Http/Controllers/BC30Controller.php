<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC30Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BC30Controller extends Controller implements HasMiddleware
{
    protected CeisaDocumentService $documentService;
    protected \App\Services\Ceisa\CeisaKursService $kursService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:document.view', only: ['index', 'show']),
            new Middleware('permission:document.create', only: ['create', 'store']),
            new Middleware('permission:ceisa.submit', only: ['submit', 'checkSchema', 'checkStatus', 'fetchResponse']),
        ];
    }

    public function __construct(
        CeisaDocumentService $documentService,
        \App\Services\Ceisa\CeisaKursService $kursService
    ) {
        $this->documentService = $documentService;
        $this->kursService = $kursService;
    }

    public function index(Request $request)
    {
        $query = CeisaDocument::where('jenis_dokumen', '30');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_aju', 'like', "%{$search}%")
                    ->orWhere('nomor_daftar', 'like', "%{$search}%")
                    ->orWhere('nama_mitra_luar_negeri', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(10)->withQueryString();

        return view('bc30.index', compact('documents'));
    }

    public function create(Request $request)
    {
        $defaultNomorAju = DocumentBC30Service::generateNomorAju();
        $kantorList = config('ceisa.kantor_pabean', []);

        $shipments = \App\Models\Shipment::with(['client', 'items'])
            ->where('jenis_layanan', 'EKSPORT_BC30')
            ->latest()
            ->take(20)
            ->get();

        $selectedShipment = null;
        if ($request->filled('shipment_id')) {
            $selectedShipment = \App\Models\Shipment::with(['client', 'items'])->find($request->shipment_id);
        }

        $activeValuta = $selectedShipment->currency ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);

        return view('bc30.create', compact('defaultNomorAju', 'kantorList', 'shipments', 'selectedShipment', 'activeKurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'kode_kantor' => 'required|string',
            'ndbm_kurs' => 'required|numeric',
            'kode_valuta' => 'required|string',
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
            'pic_pembuat' => 'nullable|string',
            'telepon_pembuat' => 'nullable|string',
            'lokasi_siap_periksa' => 'nullable|string',
            'tanggal_periksa' => 'nullable|date',
            'bank_kode' => 'nullable|string',
            'bank_nama' => 'nullable|string',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC30Service::generateNomorAju($validated['kode_kantor']);
        $validated['nomor_aju'] = $nomorAju;

        // Susun Payload CEISA
        $payload = DocumentBC30Service::buildPayload($validated);

        $fob = $payload['header']['fob'];
        $fobRupiah = $payload['header']['fobRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], 'KPU Tanjung Priok');

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '30',
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status' => 'DRAFT',
            'status_description' => 'Draft dokumen pabean BC 3.0 (PEB) siap diajukan ke CEISA 4.0 DJBC.',
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $fob,
            'total_nilai_idr' => $fobRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 50,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['buyer_nama'],
            'negara_mitra_luar_negeri' => $validated['buyer_negara'],
            'pic_pembuat' => $validated['pic_pembuat'] ?? null,
            'telepon_pembuat' => $validated['telepon_pembuat'] ?? null,
            'lokasi_siap_periksa' => $validated['lokasi_siap_periksa'] ?? null,
            'tanggal_periksa' => $validated['tanggal_periksa'] ?? null,
            'bank_kode' => $validated['bank_kode'] ?? null,
            'bank_nama' => $validated['bank_nama'] ?? null,
            'raw_payload' => $payload,
        ]);

        if (!empty($validated['shipment_id'])) {
            $shipment = \App\Models\Shipment::find($validated['shipment_id']);
            if ($shipment) {
                $shipment->update([
                    'ceisa_document_id' => $doc->id,
                    'status_tahap' => 'DRAFT_REVIEW',
                ]);
            }
        }

        return redirect()->route('bc30.show', $doc->id)
            ->with('success', "Dokumen BC 3.0 (PEB) berhasil dibuat dengan Nomor Aju: {$nomorAju}");
    }

    public function checkSchema(CeisaDocument $bc30)
    {
        try {
            $result = $this->documentService->checkSchema($bc30);
            $isValid = ($result['success'] ?? false) && (($result['data']['status'] ?? '') !== 'error');
            $msg = $result['data']['message'] ?? ($result['data']['error'] ?? ($isValid ? 'JSON Schema Valid dan sesuai spesifikasi CEISA 4.0 DJBC' : 'Validasi skema gagal'));

            $bc30->update([
                'is_schema_valid' => $isValid,
                'schema_validation_message' => $msg,
            ]);

            if ($isValid) {
                return back()->with('success', 'Pre-Check Berhasil: ' . $msg);
            } else {
                return back()->with('error', 'Pre-Check Gagal: ' . $msg);
            }
        } catch (\Throwable $e) {
            $bc30->update([
                'is_schema_valid' => false,
                'schema_validation_message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Pre-Check Gagal: ' . $e->getMessage());
        }
    }

    public function show(CeisaDocument $bc30)
    {
        $bc30->load('logs');
        return view('bc30.show', compact('bc30'));
    }

    public function submit(CeisaDocument $bc30)
    {
        try {
            $this->documentService->submit($bc30);
            return back()->with('success', 'Dokumen berhasil dikirim ke gateway CEISA 4.0 DJBC!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim ke CEISA: ' . $e->getMessage());
        }
    }

    public function checkStatus(CeisaDocument $bc30)
    {
        try {
            $this->documentService->checkStatus($bc30);
            return back()->with('success', 'Status dokumen berhasil diperbarui dari server CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal cek status: ' . $e->getMessage());
        }
    }

    public function fetchResponse(CeisaDocument $bc30)
    {
        try {
            $this->documentService->fetchResponse($bc30);
            return back()->with('success', 'Respon resmi (Nota Pelayanan Ekspor / NPE) berhasil diunduh dari CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal ambil respon: ' . $e->getMessage());
        }
    }
}
