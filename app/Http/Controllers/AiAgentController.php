<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\CeisaDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AiAgentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ai.view', only: ['index']),
            new Middleware('permission:ai.approve', except: ['index']),
        ];
    }
    public function index()
    {
        $pendingApprovals = CeisaDocument::whereIn('status', ['DRAFT', 'SUBMITTED'])->count();
        $recentAiActions = CeisaApiLog::latest()->take(10)->get();
        $activeShipments = \App\Models\Shipment::with('items')->latest()->take(15)->get();

        return view('ai.index', compact('pendingApprovals', 'recentAiActions', 'activeShipments'));
    }
}
