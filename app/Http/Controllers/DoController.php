<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:do.view', only: ['index', 'show']),
            new Middleware('permission:do.manage', except: ['index', 'show']),
        ];
    }

    public function index()
    {
        $shipments = CeisaDocument::where('jenis_dokumen', '20')->latest()->paginate(10);
        return view('core.do.index', compact('shipments'));
    }

    public function show($id)
    {
        $shipment = CeisaDocument::findOrFail($id);
        $shipments = CeisaDocument::where('id', $id)->paginate(1);
        return view('core.do.index', compact('shipment', 'shipments'));
    }
}
