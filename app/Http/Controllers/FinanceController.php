<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\CeisaDocument;
use App\Models\ClientInvoice;
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
        $invoices = ClientInvoice::with(['client', 'shipment'])->latest()->paginate(10);
        $totalAr = ClientInvoice::where('status', '!=', 'PAID')->sum('total_tagihan_idr');
        $totalBilling = CeisaDocument::whereNotNull('kode_billing')->sum('total_billing');
        $totalCashBank = CashBankAccount::all()->sum(fn($acc) => $acc->current_balance);

        return view('finance.index', compact('invoices', 'totalAr', 'totalBilling', 'totalCashBank'));
    }
}
