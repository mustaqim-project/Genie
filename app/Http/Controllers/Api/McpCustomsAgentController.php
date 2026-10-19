<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\Ceisa\CeisaHsCodeService;
use App\Services\Shipment\DocumentExtractionService;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class McpCustomsAgentController extends Controller
{
    protected DocumentExtractionService $extractionService;
    protected CeisaHsCodeService $hsCodeService;
    protected ShipmentWorkflowService $workflowService;

    public function __construct(
        DocumentExtractionService $extractionService,
        CeisaHsCodeService $hsCodeService,
        ShipmentWorkflowService $workflowService
    ) {
        $this->extractionService = $extractionService;
        $this->hsCodeService = $hsCodeService;
        $this->workflowService = $workflowService;
    }

    /**
     * MCP Tools Definition Specification
     * Format Model Context Protocol (MCP) untuk AI Agent (Claude, Antigravity, AutoGen, dll)
     */
    public function tools(): JsonResponse
    {
        return response()->json([
            'tools' => [
                [
                    'name' => 'parse_packing_list',
                    'description' => 'Mengekstrak berkas Packing List (PDF, XLSX, CSV, TXT, OCR) atau teks mentah. Mendeteksi arah deklarasi pabean (PIB BC 2.0 vs PEB BC 3.0), mengklasifikasikan pos tarif HS Code BTKI 2022 tiap barang, dan memeriksa kepatuhan perizinan Lartas serta dokumen tambahan yang wajib dilampirkan.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'raw_text' => [
                                'type' => 'string',
                                'description' => 'Teks mentah hasil OCR atau isi dokumen packing list.',
                            ],
                            'direction' => [
                                'type' => 'string',
                                'enum' => ['IMPORT', 'EXPORT', 'IMPORT_BC20', 'EKSPORT_BC30'],
                                'description' => 'Arah pengapalan jika sudah diketahui sebelumnya (opsional, sistem dapat mendeteksi otomatis).',
                            ],
                            'file_base64' => [
                                'type' => 'string',
                                'description' => 'Konten berkas packing list yang di-encode dalam base64 (opsional jika raw_text tidak disediakan).',
                            ],
                            'file_name' => [
                                'type' => 'string',
                                'description' => 'Nama file dokumen asli (contoh: PACKING_LIST_PL09.pdf atau packing_list.xlsx).',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'check_hs_code',
                    'description' => 'Pengecekan spesifik pos tarif HS Code (8-digit) atau uraian komoditas barang terhadap database BTKI 2022, tarif pungutan (BM, PPN, PPh, BK), dan daftar izin Lartas / dokumen tambahan wajib Bea Cukai.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nomor HS Code 8-digit (misal 8504.40.90) atau nama komoditas (misal: Power Inverter, Laptop, CPO, Kayu, Mesin Jahit).',
                            ],
                            'direction' => [
                                'type' => 'string',
                                'enum' => ['IMPORT', 'EXPORT', 'IMPORT_BC20', 'EKSPORT_BC30'],
                                'description' => 'Arah impor atau ekspor.',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
                [
                    'name' => 'detect_shipment_customs',
                    'description' => 'Memindai packing list pada proyek Shipment GENIE ERP tertentu, mengisi rincian barang, dan secara otomatis menyinkronkan slot tracking izin dokumen tambahan Lartas per barang.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'shipment_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau nomor proyek pengapalan di GENIE ERP.',
                            ],
                            'raw_text' => [
                                'type' => 'string',
                                'description' => 'Teks mentah OCR packing list (opsional jika dokumen sudah terunggah di proyek).',
                            ],
                        ],
                        'required' => ['shipment_id'],
                    ],
                ],
                [
                    'name' => 'generate_customs_declaration',
                    'description' => 'Mendeteksi kebutuhan pengapalan dan menghasilkan draf deklarasi pabean resmi Bea Cukai (PIB BC 2.0 untuk impor atau PEB BC 3.0 untuk ekspor) lengkap dengan lampiran dokumen pelengkap pabean dan perizinan lartas.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'shipment_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau nomor proyek pengapalan di GENIE ERP.',
                            ],
                        ],
                        'required' => ['shipment_id'],
                    ],
                ],
            ],
            'server' => [
                'name' => 'GENIE Customs AI & BTKI MCP Server',
                'version' => '2.5.0',
                'ceisa_openapi_version' => 'v2.0',
                'status' => 'ONLINE',
            ],
        ]);
    }

    /**
     * Generic MCP JSON-RPC Protocol Router
     */
    public function execute(Request $request): JsonResponse
    {
        $method = $request->input('method');
        $params = $request->input('params', []);

        if ($method === 'tools/list') {
            return $this->tools();
        }

        if ($method === 'tools/call') {
            $toolName = $params['name'] ?? null;
            $args = $params['arguments'] ?? [];

            switch ($toolName) {
                case 'parse_packing_list':
                    $req = new Request($args);
                    return $this->parsePackingList($req);

                case 'check_hs_code':
                    $req = new Request($args);
                    return $this->checkHs($req);

                case 'detect_shipment_customs':
                    $shipmentId = $args['shipment_id'] ?? null;
                    if (!$shipmentId) {
                        return response()->json(['error' => 'Argument shipment_id is required'], 422);
                    }
                    $req = new Request($args);
                    return $this->detectShipmentCustoms($shipmentId, $req);

                case 'generate_customs_declaration':
                    $shipmentId = $args['shipment_id'] ?? null;
                    if (!$shipmentId) {
                        return response()->json(['error' => 'Argument shipment_id is required'], 422);
                    }
                    $req = new Request($args);
                    return $this->generateDeclaration($shipmentId, $req);

                default:
                    return response()->json([
                        'error' => "Unknown tool name '{$toolName}'",
                    ], 404);
            }
        }

        return response()->json([
            'error' => "Unsupported MCP method '{$method}'. Expected 'tools/list' or 'tools/call'.",
        ], 400);
    }

    /**
     * 1. Tool Endpoint: parse_packing_list
     */
    public function parsePackingList(Request $request): JsonResponse
    {
        $rawText = $request->input('raw_text');
        $direction = $request->input('direction');

        // Jika upload file via multipart
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $result = $this->extractionService->parsePackingListContent($file, $direction);
            return response()->json($result);
        }

        // Jika upload file via base64
        if ($request->filled('file_base64')) {
            $base64 = $request->input('file_base64');
            $fileName = $request->input('file_name', 'upload_' . time() . '.pdf');
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('mcp_pl_') . '_' . $fileName;
            file_put_contents($tmpPath, base64_decode($base64));

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tmpPath,
                $fileName,
                mime_content_type($tmpPath) ?: 'application/octet-stream',
                null,
                true
            );

            $result = $this->extractionService->parsePackingListContent($uploadedFile, $direction);
            @unlink($tmpPath);
            return response()->json($result);
        }

        // Jika teks mentah
        if (!empty($rawText)) {
            $result = $this->extractionService->parsePackingListContent($rawText, $direction);
            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'message' => 'Harap sertakan file, file_base64, atau raw_text packing list.',
        ], 422);
    }

    /**
     * 2. Tool Endpoint: check_hs_code
     */
    public function checkHs(Request $request): JsonResponse
    {
        $query = $request->input('query') ?? $request->input('hs_code') ?? $request->input('text');
        $direction = $request->input('direction');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter query atau hs_code wajib diisi.',
            ], 422);
        }

        $result = $this->hsCodeService->classifyCommodity($query, $direction);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 3. Tool Endpoint: detect_shipment_customs
     */
    public function detectShipmentCustoms(string $id, Request $request): JsonResponse
    {
        $shipment = Shipment::where('id', $id)
            ->orWhere('shipment_number', $id)
            ->orWhere('project_no', $id)
            ->firstOrFail();

        $rawText = $request->input('raw_text');
        $file = $request->hasFile('file') ? $request->file('file') : null;

        $items = $this->workflowService->detectHsCodeFromPackingList($shipment, $rawText, $file);
        $shipment->refresh();

        // Evaluasi kepatuhan dokumen
        $verification = $this->hsCodeService->verifyPackingList($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendeteksi pos tarif barang & menyinkronkan dokumen lartas pada shipment.',
            'shipment_id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number ?? $shipment->project_no,
            'detected_type' => $shipment->type,
            'declaration_recommendation' => $shipment->type === 'EXPORT' ? 'PEB (BC 3.0)' : 'PIB (BC 2.0)',
            'items_count' => count($items),
            'items' => $items,
            'lartas_compliance' => [
                'is_all_lartas_complete' => $shipment->is_all_lartas_documents_complete,
                'total_required_documents' => $shipment->itemCustomsDocuments->count(),
                'missing_documents_count' => $shipment->itemCustomsDocuments->where('status', 'MISSING')->count(),
                'required_documents' => $shipment->itemCustomsDocuments->map(fn($d) => [
                    'id' => $d->id,
                    'kode_dokumen' => $d->kode_dokumen,
                    'nama_dokumen' => $d->nama_dokumen,
                    'instansi' => $d->instansi_penerbit,
                    'item' => $d->item->uraian_barang ?? '-',
                    'hs_code' => $d->item->kode_hs ?? '-',
                    'status' => $d->status,
                    'is_mandatory' => $d->is_mandatory,
                ]),
            ],
            'discrepancies' => $verification['discrepancies'] ?? [],
        ]);
    }

    /**
     * 4. Tool Endpoint: generate_customs_declaration
     */
    public function generateDeclaration(string $id, Request $request): JsonResponse
    {
        $shipment = Shipment::with(['documents', 'items', 'client', 'vendor'])
            ->where('id', $id)
            ->orWhere('shipment_number', $id)
            ->orWhere('project_no', $id)
            ->firstOrFail();

        if ($shipment->items->isEmpty()) {
            $this->workflowService->detectHsCodeFromPackingList($shipment);
            $shipment->refresh();
        }

        $doc = $this->hsCodeService->generatePibOrPebFromPackingList($shipment);

        return response()->json([
            'success' => true,
            'message' => "Draft deklarasi pabean BC {$doc->jenis_dokumen} ({$doc->nomor_aju}) berhasil digenerate.",
            'declaration_type' => $doc->jenis_dokumen === '20' ? 'PIB (BC 2.0 IMPOR)' : 'PEB (BC 3.0 EKSPOR)',
            'nomor_aju' => $doc->nomor_aju,
            'document_id' => $doc->id,
            'total_billing' => $doc->total_billing,
            'header_dokumen_count' => count($doc->payload['dokumen'] ?? []),
            'items_count' => count($doc->payload['barang'] ?? []),
            'lartas_complete' => $shipment->is_all_lartas_documents_complete,
        ]);
    }
}
