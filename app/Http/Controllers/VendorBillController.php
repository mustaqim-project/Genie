<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\Shipment;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorBillController extends Controller
{
    /**
     * Ambang Batas Otorisasi Pimpinan (limitAP prototipe: Rp 10.000.000)
     */
    public const APPROVAL_THRESHOLD = 10000000.00;

    /**
     * Daftar Hutang Vendor (AP), Rekapitulasi & Aging Schedule
     * Hak Akses: finance.view
     */
    public function index(Request $request)
    {
        Gate::authorize('finance.view');

        $query = VendorBill::with(['shipment', 'approvedBy', 'payments.account']);

        // Filter Status
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        // Filter Tipe Vendor
        if ($request->filled('vendor_type') && $request->vendor_type !== 'ALL') {
            $query->where('vendor_type', $request->vendor_type);
        }

        // Pencarian Ref / No Shipment
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ref_no', 'like', "%{$s}%")
                  ->orWhereHas('shipment', function ($sq) use ($s) {
                      $sq->where('project_no', 'like', "%{$s}%")
                         ->orWhere('nomor_bl_awb', 'like', "%{$s}%");
                  });
            });
        }

        $bills = $query->latest('due_date')->paginate(10);

        // Perhitungan KPI Bento
        $allBills = VendorBill::all();
        $totalOpenAp = $allBills->where('status', '!=', 'PAID')->sum('amount');
        $dueIn7Days = $allBills->where('status', '!=', 'PAID')->filter(function ($b) {
            return $b->due_date && Carbon::parse($b->due_date)->diffInDays(now(), false) <= 0 
                && Carbon::parse($b->due_date)->diffInDays(now(), false) >= -7;
        })->count();
        $pendingApprovalCount = $allBills->where('status', 'PENDING')->count();
        $totalPaidThisMonth = VendorPayment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Perhitungan AP Aging Schedule (Umur Hutang per Tipe Vendor)
        $agingSchedule = [];
        $unpaidBills = VendorBill::where('status', '!=', 'PAID')->get();
        $types = ['SHIPPING_LINE', 'TRUCKING', 'TPS', 'CUSTOMS'];

        foreach ($types as $type) {
            $typeBills = $unpaidBills->where('vendor_type', $type);
            $c0_30 = 0;
            $c31_60 = 0;
            $c61_90 = 0;
            $cOver90 = 0;

            foreach ($typeBills as $tb) {
                $days = $tb->due_date ? Carbon::parse($tb->due_date)->diffInDays(now(), false) : 0;
                // Jika due_date lewat (hari overdue positif)
                $overdueDays = max(0, $days);
                
                if ($overdueDays <= 30) {
                    $c0_30 += (float) $tb->amount;
                } elseif ($overdueDays <= 60) {
                    $c31_60 += (float) $tb->amount;
                } elseif ($overdueDays <= 90) {
                    $c61_90 += (float) $tb->amount;
                } else {
                    $cOver90 += (float) $tb->amount;
                }
            }

            $total = $c0_30 + $c31_60 + $c61_90 + $cOver90;
            if ($total > 0) {
                $agingSchedule[$type] = [
                    'label' => match ($type) {
                        'SHIPPING_LINE' => 'Pelayaran (Shipping Line)',
                        'TRUCKING' => 'Subkon Trucking Inland',
                        'TPS' => 'Terminal Petikemas TPS',
                        'CUSTOMS' => 'Bea Cukai / Pabean',
                        default => $type,
                    },
                    'current' => $c0_30,
                    'days_31_60' => $c31_60,
                    'days_61_90' => $c61_90,
                    'over_90' => $cOver90,
                    'total' => $total,
                ];
            }
        }

        $activeShipments = Shipment::whereNull('closed_at')->latest()->get();
        $bankAccounts = CashBankAccount::all();

        return view('finance.bills_index', compact(
            'bills',
            'totalOpenAp',
            'dueIn7Days',
            'pendingApprovalCount',
            'totalPaidThisMonth',
            'agingSchedule',
            'activeShipments',
            'bankAccounts'
        ));
    }

    /**
     * Input & Catat Tagihan Vendor Baru
     * Hak Akses: finance.bill
     */
    public function store(Request $request)
    {
        Gate::authorize('finance.bill');

        $request->validate([
            'vendor_type' => 'required|in:SHIPPING_LINE,TRUCKING,TPS,CUSTOMS',
            'shipment_id' => 'nullable|exists:shipments,id',
            'ref_no' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;
        $needsApproval = $amount > self::APPROVAL_THRESHOLD;
        $status = $needsApproval ? 'PENDING' : 'APPROVED';
        $approvedById = $needsApproval ? null : auth()->id();

        $bill = VendorBill::create([
            'vendor_type' => $request->vendor_type,
            'shipment_id' => $request->shipment_id,
            'ref_no' => $request->ref_no,
            'amount' => $amount,
            'due_date' => $request->due_date,
            'status' => $status,
            'approved_by_id' => $approvedById,
        ]);

        if ($needsApproval) {
            Approval::create([
                'object_type' => VendorBill::class,
                'object_id' => $bill->id,
                'step' => 1,
                'approver_role' => 'R-OWN',
                'status' => 'PENDING',
                'reason' => 'Tagihan melebihi limit Rp 10.000.000 (limitAP prototipe)',
            ]);

            return back()->with('success', "Tagihan vendor {$bill->ref_no} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil diajukan dan membutuhkan persetujuan Pimpinan (R-OWN).");
        }

        return back()->with('success', "Tagihan vendor {$bill->ref_no} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat dan siap diproses pembayaran.");
    }

    /**
     * Otorisasi / Persetujuan Tagihan oleh Pimpinan (R-OWN)
     * Hak Akses: finance.ap_approve
     */
    public function approve(string $id, Request $request)
    {
        Gate::authorize('finance.ap_approve');

        $bill = VendorBill::findOrFail($id);

        if ($bill->status !== 'PENDING') {
            return back()->with('error', 'Tagihan ini tidak dalam status menunggu persetujuan.');
        }

        $bill->update([
            'status' => 'APPROVED',
            'approved_by_id' => auth()->id(),
        ]);

        Approval::where('object_type', VendorBill::class)
            ->where('object_id', $bill->id)
            ->update([
                'status' => 'APPROVED',
                'decided_by_id' => auth()->id(),
                'decided_at' => now(),
                'reason' => $request->reason ?? 'Disetujui oleh Pimpinan.',
            ]);

        return back()->with('success', "Tagihan {$bill->ref_no} sebesar Rp " . number_format($bill->amount, 0, ',', '.') . " telah disetujui Pimpinan dan siap dicairkan.");
    }

    /**
     * Pencairan Pembayaran Tagihan Vendor
     * Hak Akses: finance.pay
     */
    public function pay(string $id, Request $request)
    {
        Gate::authorize('finance.pay');

        $bill = VendorBill::findOrFail($id);

        if ($bill->status === 'PENDING') {
            return back()->with('error', 'Tagihan vendor ini melebihi limit dan belum disetujui Pimpinan. Pencairan dana ditolak.');
        }

        if ($bill->status === 'PAID') {
            return back()->with('error', 'Tagihan ini sudah pernah dilunasi.');
        }

        $request->validate([
            'account_id' => 'required|exists:cash_bank_accounts,id',
            'paid_at' => 'required|date',
            'payment_ref' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $account = CashBankAccount::findOrFail($request->account_id);
        $amount = (float) $bill->amount;

        // 1. Catat VendorPayment
        VendorPayment::create([
            'bill_id' => $bill->id,
            'account_id' => $account->id,
            'amount' => $amount,
            'paid_at' => $request->paid_at,
        ]);

        // 2. Potong Saldo Kas & Bank Otomatis (Mutasi Keluar)
        CashBankTransaction::create([
            'account_id' => $account->id,
            'trx_date' => $request->paid_at,
            'amount' => -1 * abs($amount),
            'description' => "Pembayaran {$bill->vendor_type_label} Ref: {$bill->ref_no}" . ($request->payment_ref ? " (Transfer: {$request->payment_ref})" : ''),
            'reconciled' => true,
            'ref_type' => 'VENDOR_BILL',
            'ref_id' => $bill->id,
        ]);

        // 3. Update Status Tagihan Menjadi PAID
        $bill->update([
            'status' => 'PAID',
        ]);

        return back()->with('success', "Pembayaran tagihan {$bill->ref_no} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicairkan dari rekening {$account->name}. Saldo kas/bank telah terpotong otomatis.");
    }
}
