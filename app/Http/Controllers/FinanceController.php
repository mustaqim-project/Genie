<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FinanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:finance.view', only: ['index']),
            new Middleware('permission:finance.manage', except: ['index']),
        ];
    }
    public function index()
    {
        $invoices = CeisaDocument::whereNotNull('total_nilai_idr')->latest()->paginate(10);
        $totalAr = CeisaDocument::sum('total_nilai_idr');
        $totalBilling = CeisaDocument::whereNotNull('kode_billing')->sum('total_billing');

        return view('finance.index', compact('invoices', 'totalAr', 'totalBilling'));
    }
}
