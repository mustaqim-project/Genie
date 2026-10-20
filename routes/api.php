<?php

use App\Http\Controllers\Api\CeisaApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/ceisa')->group(function () {
    Route::post('/bc-20/submit', [CeisaApiController::class, 'submitBC20']);
    Route::post('/bc-30/submit', [CeisaApiController::class, 'submitBC30']);
    Route::get('/dokumen/{nomorAju}/status', [CeisaApiController::class, 'getStatus']);
    Route::get('/kurs/{valuta}', [CeisaApiController::class, 'getKurs']);
    Route::post('/kurs/sync', [CeisaApiController::class, 'syncKurs']);
    Route::get('/tarif-hs/{hsCode}', [CeisaApiController::class, 'getTarifHs']);
    Route::post('/tarif-hs/sync', [CeisaApiController::class, 'syncHsCodes']);
});

// MCP (Model Context Protocol) Server & Customs AI Agent Endpoints
Route::prefix('mcp')->group(function () {
    Route::get('/sse', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'sse'])->name('api.mcp.sse');
    Route::get('/tools', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'tools'])->name('api.mcp.tools');
    Route::get('/manifest', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'tools']);
    Route::post('/execute', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'execute'])->name('api.mcp.execute');
    Route::post('/parse-packing-list', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'parsePackingList'])->name('api.mcp.parse-packing-list');
    Route::post('/check-hs', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'checkHs'])->name('api.mcp.check-hs');
    Route::post('/shipments/{id}/detect-hs', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'detectShipmentCustoms'])->name('api.mcp.shipments.detect-hs');
    Route::post('/shipments/{id}/generate-declaration', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'generateDeclaration'])->name('api.mcp.shipments.generate-declaration');
});
