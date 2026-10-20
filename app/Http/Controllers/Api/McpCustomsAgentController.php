<?php

namespace App\Http\Controllers\Api;

use App\Domain\Mcp\Tools\McpToolRegistry;
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
    protected McpToolRegistry $toolRegistry;

    public function __construct(
        DocumentExtractionService $extractionService,
        CeisaHsCodeService $hsCodeService,
        ShipmentWorkflowService $workflowService,
        McpToolRegistry $toolRegistry
    ) {
        $this->extractionService = $extractionService;
        $this->hsCodeService = $hsCodeService;
        $this->workflowService = $workflowService;
        $this->toolRegistry = $toolRegistry;
    }

    /**
     * MCP Tools Definition Specification
     * Format Model Context Protocol (MCP) untuk AI Agent (Claude, Antigravity, AutoGen, Cursor, dll)
     */
    public function tools(): JsonResponse
    {
        return response()->json([
            'tools' => $this->toolRegistry->getFormattedTools(),
            'server' => [
                'name' => 'GENIE AI Agent & Enterprise ERP MCP Server',
                'version' => '3.0.0',
                'ceisa_openapi_version' => 'v2.0',
                'status' => 'ONLINE',
                'total_tools' => count($this->toolRegistry->getAllTools()),
            ],
        ]);
    }

    /**
     * Otentikasi Token Akses MCP dari Request
     * Mendukung: Header "Authorization: Bearer <token>", "?token=<token>", atau query/body "api_key".
     * Mengembalikan:
     * - McpCredential jika token valid
     * - false jika token dikirim tetapi invalid/kedaluwarsa/dicabut
     * - null jika tidak ada token yang dikirim (diizinkan untuk session internal dashboard GENIE)
     */
    protected function authenticateMcpToken(Request $request): false|null|\App\Models\McpCredential
    {
        $rawToken = $request->bearerToken()
            ?? $request->query('token')
            ?? $request->query('api_key')
            ?? $request->input('token')
            ?? $request->header('X-MCP-Token');

        if (!$rawToken) {
            return null; // Session internal atau unauthenticated call
        }

        $token = trim((string) $rawToken);
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        $hash = hash('sha256', $token);
        $credential = \App\Models\McpCredential::where('token_hash', $hash)->first();

        if ($credential && $credential->isValid()) {
            $credential->markAsUsed();
            return $credential;
        }

        return false; // Token diberikan tetapi tidak sah atau sudah dicabut
    }

    /**
     * MCP Server-Sent Events (SSE) Transport Endpoint
     * Standar resmi Anthropic MCP HTTP SSE Transport untuk Cursor, Claude Desktop Remote, dan Antigravity.
     */
    public function sse(Request $request)
    {
        $auth = $this->authenticateMcpToken($request);
        if ($auth === false) {
            return response()->json([
                'error' => 'Unauthorized: Token MCP tidak valid atau telah dicabut.',
                'status' => 401,
            ], 401);
        }

        $sessionId = (string) \Illuminate\Support\Str::uuid();
        $tokenParam = $request->query('token') ? '&token=' . urlencode($request->query('token')) : '';
        $postEndpoint = url("/api/mcp/execute?sessionId={$sessionId}{$tokenParam}");

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($postEndpoint) {
            echo "event: endpoint\n";
            echo "data: " . $postEndpoint . "\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Generic MCP JSON-RPC Protocol Router
     */
    public function execute(Request $request): JsonResponse
    {
        $auth = $this->authenticateMcpToken($request);
        if ($auth === false) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32001,
                    'message' => 'Unauthorized: Token MCP tidak valid atau telah dicabut.',
                ],
            ], 401);
        }

        $id = $request->input('id');
        $method = $request->input('method') ?? ($request->has('tool') ? 'tools/call' : null);
        $params = $request->input('params', []);

        // Handshake Inisialisasi MCP Standard
        if ($method === 'initialize') {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'protocolVersion' => '2024-11-05',
                    'serverInfo' => [
                        'name' => 'genie-customs-mcp',
                        'version' => '2.5.0',
                    ],
                    'capabilities' => [
                        'tools' => [
                            'listChanged' => false,
                        ],
                    ],
                    'instructions' => 'GENIE Customs AI Agent & BTKI MCP Server. Gunakan tool parse_packing_list untuk mencocokkan HS code otomatis dari dokumen, check_hs_code untuk validasi pos tarif & lartas, dan generate_customs_declaration untuk membuat draf PIB/PEB resmi CEISA 4.0.',
                ],
            ]);
        }

        if ($method === 'ping') {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => new \stdClass(),
            ]);
        }

        if ($method === 'tools/list') {
            $toolsData = $this->tools()->getData(true);
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'tools' => $toolsData['tools'] ?? [],
                ],
            ]);
        }

        if ($method === 'tools/call') {
            $toolName = $params['name'] ?? $request->input('tool') ?? null;
            $args = $params['arguments'] ?? $request->input('arguments') ?? $request->input('params', []);

            if (!$toolName || !$this->toolRegistry->hasTool($toolName)) {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'error' => [
                        'code' => -32601,
                        'message' => "Tool MCP '{$toolName}' tidak ditemukan dalam katalog sistem GENIE.",
                    ],
                ], 404);
            }

            try {
                $tool = $this->toolRegistry->getTool($toolName);
                $toolResult = $tool->execute(is_array($args) ? $args : []);
            } catch (\Throwable $e) {
                Log::error("MCP Tool Execution Error [{$toolName}]: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $toolResult = [
                    'success' => false,
                    'error' => "Eksekusi tool '{$toolName}' gagal: " . $e->getMessage(),
                ];
            }

            // Catat log audit pemanggilan tool ke database
            try {
                \App\Models\McpCallLog::create([
                    'credential_id' => ($auth instanceof \App\Models\McpCredential) ? $auth->id : null,
                    'tool' => (string) $toolName,
                    'args' => is_array($args) ? $args : [],
                    'result_status' => isset($toolResult['error']) ? 'ERROR' : 'SUCCESS',
                    'provider' => ($auth instanceof \App\Models\McpCredential) ? $auth->provider_hint : 'internal-web',
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal mencatat MCP call log: ' . $e->getMessage());
            }

            // Standar format respon MCP tool call
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => json_encode($toolResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                    'structured_data' => $toolResult,
                    'isError' => false,
                ],
            ]);
        }

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => -32600,
                'message' => "Metode '{$method}' tidak didukung. Harap gunakan 'initialize', 'tools/list', atau 'tools/call'.",
            ],
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
