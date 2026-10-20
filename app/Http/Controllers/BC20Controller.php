<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use App\Services\Ceisa\CeisaDocumentService;
use App\Services\Ceisa\DocumentBC20Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BC20Controller extends Controller implements HasMiddleware
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
        $query = CeisaDocument::where('jenis_dokumen', '20');

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

        $stats = [
            'total' => CeisaDocument::where('jenis_dokumen', '20')->count(),
            'draft' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'DRAFT')->count(),
            'billing' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'BILLING')->count(),
            'sppb' => CeisaDocument::where('jenis_dokumen', '20')->where('status', 'SPPB')->count(),
        ];

        return view('bc20.index', compact('documents', 'stats'));
    }

    public function create(Request $request)
    {
        $defaultNomorAju = DocumentBC20Service::generateNomorAju();
        $kantorList = config('ceisa.kantor_pabean', []);

        $shipments = \App\Models\Shipment::with(['client', 'items'])
            ->where('jenis_layanan', 'IMPORT_BC20')
            ->latest()
            ->take(20)
            ->get();

        $selectedShipment = null;
        if ($request->filled('shipment_id')) {
            $selectedShipment = \App\Models\Shipment::with(['client', 'items'])->find($request->shipment_id);
        }

        $activeValuta = $selectedShipment->currency ?? 'USD';
        $activeKurs = $this->kursService->getActiveKurs($activeValuta);

        return view('bc20.create', compact('defaultNomorAju', 'kantorList', 'shipments', 'selectedShipment', 'activeKurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'nullable|uuid|exists:shipments,id',
            'kode_kantor' => 'required|string',
            'ndbm_kurs' => 'required|numeric',
            'kode_valuta' => 'required|string',
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
            'nomor_bc11' => 'nullable|string',
            'tanggal_bc11' => 'nullable|date',
            'pos_bc11' => 'nullable|string',
            'subpos_bc11' => 'nullable|string',
            'kode_tps' => 'nullable|string',
            'tanggal_tiba' => 'nullable|date',
            'barang' => 'required|array|min:1',
        ]);

        $nomorAju = DocumentBC20Service::generateNomorAju($validated['kode_kantor']);
        $validated['nomor_aju'] = $nomorAju;

        // Susun Payload CEISA
        $payload = DocumentBC20Service::buildPayload($validated);

        $cif = $payload['header']['cif'];
        $cifRupiah = $payload['header']['cifRupiah'];
        $kantorName = config('ceisa.kantor_pabean.' . $validated['kode_kantor'], 'KPU Tanjung Priok');

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'jenis_dokumen' => '20',
            'kode_kantor' => $validated['kode_kantor'],
            'nama_kantor' => $kantorName,
            'status' => 'DRAFT',
            'status_description' => 'Draft dokumen pabean BC 2.0 (PIB) siap diajukan ke CEISA 4.0 DJBC.',
            'kode_valuta' => $validated['kode_valuta'],
            'ndbm_kurs' => $validated['ndbm_kurs'],
            'total_nilai' => $cif,
            'total_nilai_idr' => $cifRupiah,
            'total_bruto' => $validated['bruto'],
            'total_netto' => $validated['netto'],
            'total_kemasan' => $request->jumlah_kemasan ?? 100,
            'total_kontainer' => 1,
            'total_barang' => count($validated['barang']),
            'nama_perusahaan_lokal' => config('ceisa.company.name'),
            'npwp_perusahaan_lokal' => config('ceisa.company.npwp'),
            'nama_mitra_luar_negeri' => $validated['supplier_nama'],
            'negara_mitra_luar_negeri' => $validated['supplier_negara'],
            'nomor_bc11' => $validated['nomor_bc11'] ?? null,
            'tanggal_bc11' => $validated['tanggal_bc11'] ?? null,
            'pos_bc11' => $validated['pos_bc11'] ?? null,
            'subpos_bc11' => $validated['subpos_bc11'] ?? null,
            'kode_tps' => $validated['kode_tps'] ?? null,
            'tanggal_tiba' => $validated['tanggal_tiba'] ?? null,
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

        return redirect()->route('bc20.show', $doc->id)
            ->with('success', "Dokumen BC 2.0 (PIB) berhasil dibuat dengan Nomor Aju: {$nomorAju}");
    }

    public function checkSchema(CeisaDocument $bc20)
    {
        try {
            $result = $this->documentService->checkSchema($bc20);
            $isValid = ($result['success'] ?? false) && (($result['data']['status'] ?? '') !== 'error');
            $msg = $result['data']['message'] ?? ($result['data']['error'] ?? ($isValid ? 'JSON Schema Valid dan sesuai spesifikasi CEISA 4.0 DJBC' : 'Validasi skema gagal'));

            $bc20->update([
                'is_schema_valid' => $isValid,
                'schema_validation_message' => $msg,
            ]);

            if ($isValid) {
                return back()->with('success', 'Pre-Check Berhasil: ' . $msg);
            } else {
                return back()->with('error', 'Pre-Check Gagal: ' . $msg);
            }
        } catch (\Throwable $e) {
            $bc20->update([
                'is_schema_valid' => false,
                'schema_validation_message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Pre-Check Gagal: ' . $e->getMessage());
        }
    }

    public function show(CeisaDocument $bc20)
    {
        $bc20->load('logs');
        return view('bc20.show', compact('bc20'));
    }

    public function submit(CeisaDocument $bc20)
    {
        try {
            $this->documentService->submit($bc20);
            return back()->with('success', 'Dokumen berhasil dikirim ke gateway CEISA 4.0 DJBC!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim ke CEISA: ' . $e->getMessage());
        }
    }

    public function checkStatus(CeisaDocument $bc20)
    {
        try {
            $this->documentService->checkStatus($bc20);
            return back()->with('success', 'Status dokumen berhasil diperbarui dari server CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal cek status: ' . $e->getMessage());
        }
    }

    public function fetchResponse(CeisaDocument $bc20)
    {
        try {
            $this->documentService->fetchResponse($bc20);
            return back()->with('success', 'Respon resmi (Billing / SPPB) berhasil diunduh dari CEISA 4.0!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal ambil respon: ' . $e->getMessage());
        }
    }
}
