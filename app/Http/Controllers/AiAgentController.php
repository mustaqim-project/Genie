<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use App\Models\Shipment;
use App\Services\Ai\OpenAiCustomsService;
use App\Services\Shipment\DocumentExtractionService;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AiAgentController extends Controller implements HasMiddleware
{
    protected OpenAiCustomsService $aiService;
    protected DocumentExtractionService $extractionService;
    protected ShipmentWorkflowService $workflowService;

    public function __construct(
        OpenAiCustomsService $aiService,
        DocumentExtractionService $extractionService,
        ShipmentWorkflowService $workflowService
    ) {
        $this->aiService = $aiService;
        $this->extractionService = $extractionService;
        $this->workflowService = $workflowService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:ai.view', only: ['index', 'matchPackingList', 'classifyHs', 'models']),
            new Middleware('permission:ai.approve', except: ['index', 'matchPackingList', 'classifyHs', 'models']),
        ];
    }

    /**
     * Dashboard Interaktif AI Agent & MCP Customs Workbench
     */
    public function index()
    {
        $pendingApprovals = CeisaDocument::whereIn('status', ['DRAFT', 'SUBMITTED'])->count();
        $recentAiActions = CeisaApiLog::latest()->take(10)->get();
        $activeShipments = Shipment::with('items')->latest()->take(20)->get();
        $availableModels = $this->aiService->getSupportedModels();
        $defaultModel = $this->aiService->getDefaultModel();
        $aiEndpoint = config('ai.endpoint');

        return view('ai.index', compact(
            'pendingApprovals',
            'recentAiActions',
            'activeShipments',
            'availableModels',
            'defaultModel',
            'aiEndpoint'
        ));
    }

    /**
     * Endpoint Pencocokan Dokumen Packing List dengan Pos Tarif HS Code BTKI 2022 via AI
     */
    public function matchPackingList(Request $request): JsonResponse
    {
        $model = $request->input('model') ?? $this->aiService->getDefaultModel();
        $direction = $request->input('direction', 'IMPORT_BC20');
        $shipmentId = $request->input('shipment_id');
        $rawText = $request->input('raw_text');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $result = $this->extractionService->parsePackingListContent($file, $direction, $model);
        } elseif (!empty($rawText)) {
            $result = $this->extractionService->parsePackingListText($rawText, $direction, $model);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Harap unggah berkas (PDF/Excel/Gambar/CSV) atau tempel teks dokumen packing list.',
            ], 422);
        }

        // Jika shipment_id disertakan dan user ingin langsung menyinkronkan item ke database shipment
        if ($shipmentId && !empty($result['items'])) {
            try {
                $shipment = Shipment::find($shipmentId);
                if ($shipment) {
                    $shipment->items()->delete();
                    foreach ($result['items'] as $it) {
                        $this->workflowService->addCargoItem($shipment, [
                            'uraian_barang' => $it['uraian_barang'],
                            'kode_hs' => $it['kode_hs'],
                            'jumlah_kemasan' => $it['jumlah_kemasan'],
                            'jenis_kemasan' => $it['jenis_kemasan'],
                            'berat_bruto' => $it['berat_bruto'],
                            'berat_netto' => $it['berat_netto'],
                            'nilai_fob' => $it['nilai_fob'],
                            'has_lartas' => $it['has_lartas'] ?? false,
                        ]);
                    }
                    $result['synced_to_shipment'] = true;
                    $result['shipment_number'] = $shipment->shipment_number ?? $shipment->project_no;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal auto-sync ke shipment {$shipmentId}: " . $e->getMessage());
                $result['synced_to_shipment'] = false;
                $result['sync_error'] = $e->getMessage();
            }
        }

        return response()->json($result);
    }

    /**
     * Endpoint Klasifikasi Komoditas Tunggal berbasis AI
     */
    public function classifyHs(Request $request): JsonResponse
    {
        $query = $request->input('query') ?? $request->input('hs_code') ?? $request->input('text');
        $model = $request->input('model') ?? $this->aiService->getDefaultModel();
        $direction = $request->input('direction');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter query atau deskripsi komoditas wajib diisi.',
            ], 422);
        }

        $result = $this->aiService->classifyCommodity($query, $model, $direction);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Endpoint Daftar Model AI yang Tersedia
     */
    public function models(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'default_model' => $this->aiService->getDefaultModel(),
            'models' => $this->aiService->getSupportedModels(),
        ]);
    }
}
