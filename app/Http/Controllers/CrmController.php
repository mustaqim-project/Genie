<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CrmController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:crm.view', only: ['index']),
            new Middleware('permission:crm.manage', except: ['index']),
        ];
    }
    public function index()
    {
        $clients = CeisaDocument::select('nama_mitra_luar_negeri', 'negara_mitra_luar_negeri', 'created_at')
            ->distinct()
            ->latest()
            ->paginate(10);

        return view('crm.index', compact('clients'));
    }
}
