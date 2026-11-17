<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientHsCode;
use App\Models\HsCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ClientHsCodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:core.view', only: ['index', 'suggest']),
            new Middleware('permission:core.manage', except: ['index', 'suggest']),
        ];
    }

    /**
     * Tampilan Kamus HS Code per Klien (Sesuai MoM Poin 12)
     */
    public function index(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $query = ClientHsCode::with(['client', 'hsCode', 'creator'])->latest();

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('uraian_barang_client', 'like', "%{$s}%")
                    ->orWhere('catatan_lartas', 'like', "%{$s}%")
                    ->orWhereHas('hsCode', function ($hq) use ($s) {
                        $hq->where('code', 'like', "%{$s}%")
                           ->orWhere('description', 'like', "%{$s}%");
                    });
            });
        }

        $records = $query->paginate(15)->withQueryString();
        $masterHsCodes = HsCode::orderBy('code')->take(50)->get();

        return view('core.client_hs_codes', compact('clients', 'records', 'masterHsCodes'));
    }

    /**
     * Simpan referensi HS Code ke kamus klien
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'hs_code_id' => 'nullable|exists:hs_codes,id',
            'hs_code_manual' => 'nullable|string|max:20',
            'uraian_barang_client' => 'required|string|max:500',
            'spesifikasi_teknis' => 'nullable|string|max:500',
            'negara_asal_default' => 'nullable|string|max:10',
            'tarif_bm_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_ppn_persen' => 'nullable|numeric|min:0|max:100',
            'tarif_pph_persen' => 'nullable|numeric|min:0|max:100',
            'catatan_lartas' => 'nullable|string|max:500',
        ]);

        $hsCodeId = $validated['hs_code_id'] ?? null;

        // Jika user mengetik nomor HS manual tanpa memilih ID
        if (!$hsCodeId && !empty($validated['hs_code_manual'])) {
            $cleanCode = preg_replace('/[^0-9]/', '', $validated['hs_code_manual']);
            $hs = HsCode::where('code', 'like', "%{$cleanCode}%")->first();
            if ($hs) {
                $hsCodeId = $hs->id;
            } else {
                // Buat entitas HS code baru
                $newHs = HsCode::create([
                    'code' => $validated['hs_code_manual'],
                    'description' => $validated['uraian_barang_client'],
                    'duty_bm' => $validated['tarif_bm_persen'] ?? 0,
                    'duty_ppn' => $validated['tarif_ppn_persen'] ?? 11,
                    'duty_pph' => $validated['tarif_pph_persen'] ?? 2.5,
                    'source' => 'CLIENT_CUSTOM_MOM',
                ]);
                $hsCodeId = $newHs->id;
            }
        }

        ClientHsCode::create([
            'client_id' => $validated['client_id'],
            'hs_code_id' => $hsCodeId,
            'uraian_barang_client' => $validated['uraian_barang_client'],
            'spesifikasi_teknis' => $validated['spesifikasi_teknis'] ?? null,
            'negara_asal_default' => $validated['negara_asal_default'] ?? 'CN',
            'tarif_bm_persen' => $validated['tarif_bm_persen'] ?? 0,
            'tarif_ppn_persen' => $validated['tarif_ppn_persen'] ?? 11,
            'tarif_pph_persen' => $validated['tarif_pph_persen'] ?? 2.5,
            'catatan_lartas' => $validated['catatan_lartas'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()->route('core.client-hs-codes.index')
            ->with('success', 'Referensi HS Code berhasil ditambahkan ke kamus klien!');
    }

    /**
     * Hapus referensi dari kamus klien
     */
    public function destroy(string $id)
    {
        $record = ClientHsCode::findOrFail($id);
        $record->delete();

        return redirect()->route('core.client-hs-codes.index')
            ->with('success', 'Referensi HS Code klien berhasil dihapus.');
    }

    /**
     * JSON Suggestion API untuk auto-complete di formulir PIB / PEB
     */
    public function suggest(Request $request)
    {
        $clientId = $request->query('client_id');
        $keyword = $request->query('q');

        if (!$clientId) {
            return response()->json([]);
        }

        $query = ClientHsCode::with('hsCode')
            ->where('client_id', $clientId);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('uraian_barang_client', 'like', "%{$keyword}%")
                  ->orWhereHas('hsCode', function ($hq) use ($keyword) {
                      $hq->where('code', 'like', "%{$keyword}%");
                  });
            });
        }

        $results = $query->take(10)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'hs_code' => $item->hsCode ? $item->hsCode->code : '-',
                'uraian_barang' => $item->uraian_barang_client,
                'spesifikasi' => $item->spesifikasi_teknis,
                'negara_asal' => $item->negara_asal_default,
                'bm' => (float) ($item->tarif_bm_persen ?? ($item->hsCode->duty_bm ?? 0)),
                'ppn' => (float) ($item->tarif_ppn_persen ?? ($item->hsCode->duty_ppn ?? 11)),
                'pph' => (float) ($item->tarif_pph_persen ?? ($item->hsCode->duty_pph ?? 2.5)),
                'lartas' => $item->catatan_lartas,
            ];
        });

        return response()->json($results);
    }
}