<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AccountingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounting.view', only: ['index']),
            new Middleware('permission:accounting.manage', except: ['index']),
        ];
    }
    public function index()
    {
        $revenue = CeisaDocument::sum('total_nilai_idr');
        $expense = CeisaDocument::whereNotNull('total_billing')->sum('total_billing');
        $netProfit = $revenue - $expense;

        $journals = CeisaDocument::latest()->paginate(10);

        return view('accounting.index', compact('revenue', 'expense', 'netProfit', 'journals'));
    }
}
