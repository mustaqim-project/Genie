<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\McpCustomsAgentController;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class McpIntegrationController extends Controller
{
    protected McpCustomsAgentController $agentController;

    public function __construct(McpCustomsAgentController $agentController)
    {
        $this->agentController = $agentController;
    }

    /**
     * Halaman Hub Integrasi MCP Server & AI Agent Kepabeanan
     */
    public function index(Request $request)
    {
        // Cek otorisasi atau tampilkan untuk admin/staff operasional
        if (auth()->check() && Gate::has('system.settings')) {
            Gate::authorize('system.settings');
        }

        $toolsResponse = $this->agentController->tools()->getData(true);
        $tools = $toolsResponse['tools'] ?? [];
        $serverInfo = $toolsResponse['server'] ?? [
            'name' => 'GENIE Customs AI & BTKI MCP Server',
            'version' => '2.5.0',
            'ceisa_openapi_version' => 'v2.0',
            'status' => 'ONLINE',
        ];

        $sseEndpoint = url('/api/mcp/sse');
        $executeEndpoint = url('/api/mcp/execute');
        $toolsEndpoint = url('/api/mcp/tools');
        $apiBaseUrl = url('/api/mcp');

        // Contoh teks Packing List otentik untuk 1-click testing di UI
        $samplePackingList = "INVOICE NO: HZ20240519 DATE: 19-05-2024
COMMODITY & PACKING LIST:
1. HYDRAULIC BREAKER DYB800 STRAIGHT TYPE 4 SET 8392 KGS CNY 229640.71
2. HYDRAULIC BREAKER THB140 1 SET 1998 KGS CNY 57410.18
3. HYDRAULIC BREAKER SPAREPART : CHISEL SB40 PLUS TYPE 5 PCE 80 KGS CNY 775.53
4. HYDRAULIC BREAKER SPAREPART : PIN ROD SB40 10 PCE 22 KGS CNY 186.13
5. HYDRAULIC BREAKER SPAREPART : SIDE BOLT SB40 10 PCE 13.32 KGS CNY 186.13
6. SBS WATERPROOFING MEMBRANE PE 10M*1M*3MM 80 ROLL 3200 KGS CNY 12408.54
7. PNEUMATIC PICK G10 5 UNT 44.5 KGS CNY 1137.45
8. PNEUMATIC PICK SPAREPART : CHISEL TYPE PLUS TPB60 20 PCE 59 KGS CNY 661.79
9. HAND PALLET (NYLON CASTER) 3T 1200*685 12 PALLET 869 KGS CNY 12932.92
10. HAND PALLET SPAREPART : OIL CYLINDER 2T 5 PCE 39.9 KGS CNY 853.09
11. BENDING MACHINE GW-50 2 PKG 626 KGS CNY 6100.87
12. STEEL BAR CUTTING MACHINE GQ50 2 PKG 976 KGS CNY 8272.36
13. ELECTRIC CONCRETE MIXER 180 2 CTN 100 KGS CNY 1902.64
14. FIBERGLASS MESH WATERPROOFING 120GR 1*50M 30 ROLL 180 KGS CNY 1473.51
15. PARANET 75% BLACK 100M*3M 300 KGM CNY 3412.35
16. TAMPING RAMMER GASOLINE TYPE 125 5 UNT 370 KGS CNY 10904.00
17. WEBBING SLING BELT YELLOW 3T*10M 65.7 KGM CNY 1290.80";

        $recentShipments = Shipment::latest()->take(8)->get();

        return view('settings.integrations.mcp', compact(
            'tools',
            'serverInfo',
            'sseEndpoint',
            'executeEndpoint',
            'toolsEndpoint',
            'apiBaseUrl',
            'samplePackingList',
            'recentShipments'
        ));
    }
}
